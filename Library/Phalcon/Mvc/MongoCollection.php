<?php

/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2016 Phalcon Team (https://www.phalconphp.com)      |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file LICENSE.txt.                             |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Ben Casey <bcasey@tigerstrikemedia.com>                       |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Mvc;

use Phalcon\Di;
use MongoDB\BSON\ObjectID;
use MongoDB\Driver\WriteConcern;
use MongoDB\BSON\Unserializable;
use Phalcon\Mvc\Collection\Document;
use Phalcon\Mvc\Collection\Exception;
use Phalcon\Mvc\Collection\ManagerInterface;
use Phalcon\Db\Adapter\MongoDB\InsertOneResult;
use Phalcon\Mvc\Collection as PhalconCollection;
use Phalcon\Db\Adapter\MongoDB\Collection as AdapterCollection;

/**
 * Class MongoCollection
 *
 * @property ManagerInterface _modelsManager
 * @package Phalcon\Mvc
 */
abstract class MongoCollection extends PhalconCollection implements Unserializable
{
    // @codingStandardsIgnoreStart
    static protected $_disableEvents;

    /**
     * @var bool
     */
    protected $_embedded = false;

    /**
     * @var array
     */
    protected $_embeddedFields = [];

    /**
     * @var array
     */
    protected $_embeddedArray = [];

    /**
     * @var array
     */
    protected $_securedFields = [];

    /**
     * @var \Phalcon\Mvc\MongoCollection|null
     */
    protected $_parent;
    // @codingStandardsIgnoreEnd

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getReservedAttributes()
    {
        $reserved = [
            '_data'           => true,
            '_embedded'       => true,
            '_embeddedFields' => true,
            '_embeddedArray'  => true,
            '_securedFields'   => true,
            '_parent'         => true,
        ];

        return array_merge($reserved, parent::getReservedAttributes());
    }

    /**
     * {@inheritdoc}
     *
     * @param bool $toString
     *
     * @return string
     */
    public function getId($toString = false)
    {
        return $toString ? (string) parent::getId() : parent::getId();
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $id
     */
    public function setId($id)
    {
        if (is_object($id)) {
            $this->_id = $id;
            return;
        }

        if ($this->_modelsManager->isUsingImplicitObjectIds($this)) {
            $this->_id = new ObjectID($id);

            return;
        }

        $this->_id = $id;
    }

    /**
     * Sets a parent object in embedded collection
     *
     * @param \Phalcon\Mvc\MongoCollection $object
     *
     * @return $this
     */
    public function setParent(MongoCollection &$object)
    {
        if ($this->isEmbedded()) {
            $this->_parent = $object;
        }

        return $this;
    }

    /**
     * Return parent object
     *
     * @return \Phalcon\Mvc\MongoCollection|null
     */
    public function getParent()
    {
        return $this->_parent;
    }


    /**
     *  Return whether this objects embedded
     *
     * @return bool
     */
    public function isEmbedded()
    {
        return $this->_embedded;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     *
     * @throws Exception
     */
    public function save()
    {
        if ($this->isEmbedded()) {
            return true;
        }

        $dependencyInjector = $this->_dependencyInjector;

        if (!is_object($dependencyInjector)) {
            throw new Exception(
                "A dependency injector container is required to obtain the services related to the ODM"
            );
        }

        $source = $this->getSource();

        if (empty($source)) {
            throw new Exception("Method getSource() returns empty string");
        }

        $connection = $this->getConnection();

        $collection = $connection->selectCollection($source);

        $exists = $this->_exists($collection);

        if (false === $exists) {
            $this->_operationMade = self::OP_CREATE;
        } else {
            $this->_operationMade = self::OP_UPDATE;
        }

        /**
         * The messages added to the validator are reset here
         */
        $this->_errorMessages = [];

        $disableEvents = self::$_disableEvents;

        /**
         * Execute the preSave hook
         */
        if (false === $this->_preSave($dependencyInjector, $disableEvents, $exists)) {
            return false;
        }

        $data = $this->toArray();

        /**
         * We always use safe stores to get the success state
         * Save the document
         */
        switch ($this->_operationMade) {
            case self::OP_CREATE:
                $status = $collection->insertOne($data);

                break;

            case self::OP_UPDATE:
                unset($data['_id']);

                $status = $collection->updateOne(
                    [
                        '_id' => $this->_id,
                    ],
                    [
                        '$set' => $data,
                    ]
                );

                break;

            default:
                throw new Exception(
                    'Invalid operation requested for ' . __METHOD__
                );
        }

        $success = false;

        if ($status->isAcknowledged()) {
            $success = true;

            if (false === $exists) {
                $this->_id = $status->getInsertedId();

                $this->_dirtyState = self::DIRTY_STATE_PERSISTENT;
            }
        }

        /**
         * Call the postSave hooks
         */
        return $this->_postSave($disableEvents, $success, $exists);
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $id
     *
     * @return array
     */
    public static function findById($id)
    {
        if (!is_object($id)) {
            $classname = get_called_class();

            $collection = new $classname();

            /** @var MongoCollection $collection */
            if ($collection->getCollectionManager()->isUsingImplicitObjectIds($collection)) {
                $mongoId = new ObjectID($id);
            } else {
                $mongoId = $id;
            }
        } else {
            $mongoId = $id;
        }

        return static::findFirst(
            [
                [
                    "_id" => $mongoId,
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     *
     * @param  array|null $parameters
     * @return array
     */
    public static function findFirst(array $parameters = null)
    {
        $className = get_called_class();

        /** @var MongoCollection $collection */
        $collection = new $className();

        $connection = $collection->getConnection();

        return static::_getResultset(
            $parameters,
            $collection,
            $connection,
            true
        );
    }

    /**
     * {@inheritdoc}
     *
     * @param array               $params
     * @param CollectionInterface $collection
     * @param \MongoDb            $connection
     * @param bool                $unique
     *
     * @return array
     * @throws Exception
     * @codingStandardsIgnoreStart
     */
    protected static function _getResultset($params, CollectionInterface $collection, $connection, $unique)
    {
        /**
         * @codingStandardsIgnoreEnd
         * Check if "class" clause was defined
         */
        if (isset($params['class'])) {
            $classname = $params['class'];

            $base = new $classname();

            if (!$base instanceof CollectionInterface || $base instanceof Document) {
                throw new Exception(
                    sprintf(
                        'Object of class "%s" must be an implementation of %s or an instance of %s',
                        get_class($base),
                        CollectionInterface::class,
                        Document::class
                    )
                );
            }
        } else {
            $base = $collection;
        }

        if ($base instanceof PhalconCollection) {
            $base->setDirtyState(
                PhalconCollection::DIRTY_STATE_PERSISTENT
            );
        }

        $source = $collection->getSource();

        if (empty($source)) {
            throw new Exception("Method getSource() returns empty string");
        }

        /**
         * @var \Phalcon\Db\Adapter\MongoDB\Collection $mongoCollection
         */
        $mongoCollection = $connection->selectCollection($source);

        if (!is_object($mongoCollection)) {
            throw new Exception("Couldn't select mongo collection");
        }

        $conditions = [];

        if (isset($params[0])||isset($params['conditions'])) {
            $conditions = (isset($params[0])) ? $params[0] : $params['conditions'];
        }

        /**
         * Convert the string to an array
         */
        if (!is_array($conditions)) {
            throw new Exception("Find parameters must be an array");
        }

        $options = [];

        /**
         * Check if a "limit" clause was defined
         */
        if (isset($params['limit'])) {
            $limit = $params['limit'];

            $options['limit'] = (int) $limit;

            if ($unique) {
                $options['limit'] = 1;
            }
        }

        /**
         * Check if a "sort" clause was defined
         */
        if (isset($params['sort'])) {
            $sort = $params["sort"];

            $options['sort'] = $sort;
        }

        /**
         * Check if a "skip" clause was defined
         */
        if (isset($params['skip'])) {
            $skip = $params["skip"];

            $options['skip'] = (int)$skip;
        }

        if (isset($params['fields']) && is_array($params['fields']) && !empty($params['fields'])) {
            $options['projection'] = [];

            foreach ($params['fields'] as $key => $show) {
                $options['projection'][$key] = $show;
            }
        }

        /**
         * Perform the find
         */
        $cursor = $mongoCollection->find($conditions, $options);

        $cursor->setTypeMap(
            [
                'root'     => get_class($base),
                'document' => 'array',
            ]
        );

        if (true === $unique) {
            /**
             * Looking for only the first result.
             */
            return current(
                $cursor->toArray()
            );
        }

        /**
         * Requesting a complete resultset
         */
        $collections = [];
        foreach ($cursor as $document) {
            /**
             * Assign the values to the base object
             */
            $collections[] = $document;
        }

        return $collections;
    }

    /**
     * {@inheritdoc}
     *
     * <code>
     *    $robot = Robots::findFirst();
     *    $robot->delete();
     *
     *    foreach (Robots::find() as $robot) {
     *        $robot->delete();
     *    }
     * </code>
     */
    public function delete()
    {
        if ($this->isEmbedded()) {
            return true;
        }

        if (!$id = $this->_id) {
            throw new Exception(
                "The document cannot be deleted because it doesn't exist"
            );
        }

        $disableEvents = self::$_disableEvents;

        if (!$disableEvents) {
            if (false === $this->fireEventCancel("beforeDelete")) {
                return false;
            }
        }

        if (true === $this->_skipped) {
            return true;
        }

        $connection = $this->getConnection();

        $source = $this->getSource();
        if (empty($source)) {
            throw new Exception("Method getSource() returns empty string");
        }

        /**
         * Get the Collection
         *
         * @var AdapterCollection $collection
         */
        $collection = $connection->selectCollection($source);

        if (is_object($id)) {
            $mongoId = $id;
        } else {
            if ($this->_modelsManager->isUsingImplicitObjectIds($this)) {
                $mongoId = new ObjectID($id);
            } else {
                $mongoId = $id;
            }
        }

        $success = false;

        /**
         * Remove the instance
         */
        $status = $collection->deleteOne(
            [
                '_id' => $mongoId,
            ],
            [
                'w' => true,
            ]
        );

        if ($status->isAcknowledged()) {
            $success = true;

            $this->fireEvent("afterDelete");

            $this->_dirtyState = self::DIRTY_STATE_DETACHED;
        }

        return $success;
    }

    /**
     * {@inheritdoc}
     *
     * @param  \MongoCollection $collection
     * @return boolean
     * @codingStandardsIgnoreStart
     */
    protected function _exists($collection)
    {
        // @codingStandardsIgnoreStart
        if (!$id = $this->_id) {
            return false;
        }

        if (!$this->_dirtyState) {
            return true;
        }

        if (is_object($id)) {
            $mongoId = $id;
        } else {
            /**
             * Check if the model use implicit ids
             */
            if ($this->_modelsManager->isUsingImplicitObjectIds($this)) {
                $mongoId = new ObjectID($id);
            } else {
                $mongoId = $id;
            }
        }

        /**
         * Perform the count using the function provided by the driver
         */
        $exists = $collection->count(["_id" => $mongoId]) > 0;

        if ($exists) {
            $this->_dirtyState = self::DIRTY_STATE_PERSISTENT;
        } else {
            $this->_dirtyState = self::DIRTY_STATE_TRANSIENT;
        }

        return $exists;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $eventName
     * @return bool
     */
    public function fireEventCancel($eventName)
    {
        /**
         * Check if there is a method with the same name of the event
         */
        if (method_exists($this, $eventName)) {
            if (false === $this->{$eventName}()) {
                return false;
            }
        }

        /**
         * Send a notification to the events manager
         */
        if (false === $this->_modelsManager->notifyEvent($eventName, $this)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @todo
     * @param string $field
     * @param null $conditions
     * @param null $finalize
     *
     * @throws Exception
     */
    public static function summatory($field, $conditions = null, $finalize = null)
    {
        throw new Exception(
            'The summatory() method is not implemented in the new Mvc MongoCollection'
        );
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function create()
    {
        if ($this->isEmbedded()) {
            return true;
        }

        /** @var \Phalcon\Db\Adapter\MongoDB\Collection $collection */
        $collection = $this->prepareCU();

        /**
         * Check the dirty state of the current operation to update the current operation
         */
        $this->_operationMade = self::OP_CREATE;

        /**
         * The messages added to the validator are reset here
         */
        $this->_errorMessages = [];

        /**
         * Execute the preSave hook
         */
        if ($this->_preSave($this->_dependencyInjector, self::$_disableEvents, false) === false) {
            return false;
        }

        $data = $this->toArray();
        $success = false;

        /**
         * We always use safe stores to get the success state
         * Save the document
         */
        $result = $collection->insert(
            $data,
            [
                'writeConcern' => new WriteConcern(1),
            ]
        );

        if ($result instanceof InsertOneResult && $result->getInsertedId()) {
            $success = true;

            $this->_dirtyState = self::DIRTY_STATE_PERSISTENT;
            $this->_id = $result->getInsertedId();
        }

        /**
         * Call the postSave hooks
         */
        return $this->_postSave(self::$_disableEvents, $success, false);
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function update()
    {
        if ($this->isEmbedded()) {
            return true;
        }

        return parent::update();
    }

    /**
     * {@inheritdoc}
     *
     * @param array $data
     */
    public function createIfNotExist(array $criteria)
    {
        if (empty($criteria)) {
            throw new Exception("Criteria parameter must be array with one or more attributes of the model");
        }
      
        /**
         * Choose a collection according to the collection name
         */
        $collection = $this->prepareCU();
      
        /**
         * Assume non-existence to fire beforeCreate events - no update does occur anyway
         */
        $exists = false;

        /**
         * Reset current operation
         */
        $this->_operationMade = self::OP_NONE;

        /**
         * The messages added to the validator are reset here
         */
        $this->_errorMessages = [];

        /**
         * Execute the preSave hook
         */
        if ($this->_preSave($this->_dependencyInjector, self::$_disableEvents, $exists) === false) {
            return false;
        }

        $keys = array_flip($criteria);
        $data = $this->toArray();

        if (array_diff_key($keys, $data)) {
            throw new \Exception("Criteria parameter must be array with one or more attributes of the model");
        }

        $query = array_intersect_key($data, $keys);

        $success = false;

        $status = $collection->findOneAndUpdate($query,
            ['$setOnInsert' => $data],
            ['new' => true, 'upsert' => true]);

        if ($status == null) {
            $doc = $collection->findOne($query);

            if (is_object($doc)) {
                $success = true;
                $this->_operationMade = self::OP_CREATE;
                $this->_id = $doc['_id'];
            }
        } else {
            $this->appendMessage(new Message("Document already exists"));
        }

        /**
         * Call the postSave hooks
         */
        return $this->_postSave(self::$_disableEvents, $success, $exists);
    }

    /**
     * {@inheritdoc}
     *
     * @param array $data
     */
    public function bsonUnserialize(array $data)
    {
        $di = Di::getDefault();

        $this->setDI($di);

        $this->_modelsManager = $di->getShared('collectionManager');

        foreach ($data as $key => $val) {
            $this->{$key} = $val;
        }

        if (method_exists($this, "afterFetch")) {
            $this->afterFetch();
        }
    }

    /**
     * Initialize embedded data
     */
    protected function initEmbedded()
    {
        $this->initEmbeddedFields();
        $this->initEmbeddedArray();
    }

    /**
     * Initialize embedded fields
     */
    protected function initEmbeddedFields()
    {
        foreach ($this->_embeddedFields as $field => $object) {
            if (empty($this->$field)) {
                $this->$field = [];
            }

            if ($this->$field instanceof MongoCollection) {
                continue;
            }

            $this->{$field} = $object::fromArray($this->{$field});
            $this->{$field}->setParent($this);
        }
    }

    /**
     * Initialize embedded field array
     */
    protected function initEmbeddedArray()
    {
        foreach ($this->_embeddedArray as $field => $object) {
            if (!is_array($this->$field) || empty($this->$field)) {
                continue;
            }

            $array = $this->$field;

            foreach ($array as $key => $value) {
                if ($value instanceof MongoCollection) {
                    continue;
                }

                $array[$key] = $object::fromArray($value);
                $array[$key]->setParent($this);
                $array[$key]->initEmbedded();
            }

            $this->$field = $array;
        }
    }

    /**
     * Create collection from array
     *
     * @param array $array
     *
     * @return mixed
     */
    public static function fromArray(array $array)
    {
        $className  = get_called_class();
        $collection = new $className();

        foreach ($array as $key => $value) {
            if (is_array($value) && key_exists('$oid', $value)) {
                $value = new ObjectId($value['$oid']);
            }

            if ($key === '_id' || (!$collection->isEmbedded() && $key === 'id')) {
                $key   = '_id';
                $value = new ObjectId($value);
            }

            $collection->{$key} = $value;
        }


        if (method_exists($collection, "afterFetch")) {
            $collection->afterFetch();
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     *
     * @param bool $secured
     * @return array
     */
    public function toArray($secured = false)
    {
        $data     = [];
        $reserved = $this->getReservedAttributes();

        foreach (get_object_vars($this) as $key => $value) {
            if ($secured && in_array($key, $this->getSecuredFields())) {
                continue;
            }

            if ($key === '_id' && $secured) {
                $key = 'id';
            }

            if (!isset($reserved[$key])) {
                if (isset($this->_embeddedFields[$key]) && $value instanceof MongoCollection) {
                    $data[$key] = $value->toArray();
                } elseif (isset($this->_embeddedArray[$key])) {
                    $data[$key] = [];

                    if (is_array($this->$key)) {
                        foreach ($this->$key as $k => $v) {
                            if ($data instanceof MongoCollection) {
                                $data[$key][$k] = $v->toArray();
                            } else {
                                $data[$key][$k] = $v;
                            }
                        }
                    }
                } elseif ($value instanceof ObjectId) {
                    $data[$key] = (string) $value;
                } else {
                    $data[$key] = $value;
                }
            }
        }

        if (!$secured) {
            $data = array_filter($data);
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function afterFetch()
    {
        $this->initEmbedded();
    }

    /**
     * {@inheritdoc}
     */
    public function onConstruct()
    {
        $this->initEmbedded();
    }

    /**
     * Returns an array with secured properties that cannot be send client
     *
     * @return array
     */
    protected function getSecuredFields()
    {
        return $this->_securedFields;
    }
}
