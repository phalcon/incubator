<?php

/*
 +------------------------------------------------------------------------+
 | Phalcon Framework                                                      |
 +------------------------------------------------------------------------+
 | Copyright (c) 2011-2016 Phalcon Team (http://www.phalconphp.com)       |
 +------------------------------------------------------------------------+
 | This source file is subject to the New BSD License that is bundled     |
 | with this package in the file docs/LICENSE.txt.                        |
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
use MongoDB\BSON\Unserializable;
use Phalcon\Mvc\Collection\Document;
use Phalcon\Mvc\Collection\Exception;
use Phalcon\Mvc\Collection as PhalconCollection;
use Phalcon\Db\Adapter\MongoDB\Collection as AdapterCollection;

/**
 * Class MongoCollection
 *
 * @property  \Phalcon\Mvc\Collection\ManagerInterface _modelsManager
 * @package Phalcon\Mvc
 */
abstract class MongoCollection extends PhalconCollection implements Unserializable
{
    // @codingStandardsIgnoreStart
    static protected $_disableEvents;
    // @codingStandardsIgnoreEnd

    /**
     * Sets a value for the _id property, creates a MongoId object if needed
     *
     * @param mixed $id
     */
    public function setId($id)
    {
        $mongoId = null;

        if (is_object($id)) {
            $mongoId = $id;
        } else {
            if ($this->_modelsManager->isUsingImplicitObjectIds($this)) {
                $mongoId = new ObjectID($id);
            } else {
                $mongoId = $id;
            }
        }

        $this->_id = $mongoId;
    }

    /**
     * Creates/Updates a collection based on the values in the attributes
     */
    public function save()
    {
        $dependencyInjector = $this->_dependencyInjector;

        if (!is_object($dependencyInjector)) {
            throw new Exception(
                "A dependency injector container is required to obtain the services related to the ORM"
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
                $status = $collection->updateOne(['_id' => $this->_id], ['$set' => $this->toArray()]);
                break;

            default:
                throw new Exception('Invalid operation requested for MongoCollection->save()');
        }

        $success = false;

        if ($status->isAcknowledged()) {
            $success = true;

            if (false === $exists) {
                $this->_id = $status->getInsertedId();
            }
        }

        /**
         * Call the postSave hooks
         */
        return $this->_postSave($disableEvents, $success, $exists);
    }

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

        return static::findFirst([["_id" => $mongoId]]);
    }

    public static function findFirst(array $parameters = null)
    {
        $className = get_called_class();

        /** @var MongoCollection $collection */
        $collection = new $className();

        $connection = $collection->getConnection();

        return static::_getResultset($parameters, $collection, $connection, true);
    }

    /**
     * @param array               $params
     * @param CollectionInterface $collection
     * @param \MongoDb            $connection
     * @param bool                $unique
     *
     * @return array
     * @throws Exception
     */
    // @codingStandardsIgnoreStart
    protected static function _getResultset($params, CollectionInterface $collection, $connection, $unique)
    {
        // @codingStandardsIgnoreEnd

        /**
         * Check if "class" clause was defined
         */
        if (isset($params['class'])) {
            $classname = $params['class'];

            $base = new $classname();

            if (!$base instanceof CollectionInterface || $base instanceof Document) {
                throw new Exception(
                    "Object of class '".$classname."' must be an implementation of 
                    Phalcon\\Mvc\\CollectionInterface or an instance of Phalcon\\Mvc\\Collection\\Document"
                );
            }
        } else {
            $base = $collection;
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
            $conditions = (isset($params[0]))?$params[0]:$params['conditions'];
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

            $options['limit'] = (int)$limit;

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

        $cursor->setTypeMap(['root'=>get_called_class(),'document'=>'object']);

        if (true === $unique) {
            /**
             * Looking for only the first result.
             */
            return current($cursor->toArray());
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
     * Deletes a model instance. Returning true on success or false otherwise.
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
        if (!$id = $this->_id) {
            throw new Exception("The document cannot be deleted because it doesn't exist");
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
        $status = $collection->deleteOne(['_id' => $mongoId], ['w' => true]);

        if ($status->isAcknowledged()) {
            $success = true;

            $this->fireEvent("afterDelete");
        }

        return $success;
    }

    /**
     * Checks if the document exists in the collection
     *
     * @param \MongoCollection collection
     *
     * @return boolean
     */
    // @codingStandardsIgnoreStart
    protected function _exists($collection)
    {
        // @codingStandardsIgnoreStart

        if (!$id = $this->_id) {
            return false;
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
        return $collection->count(["_id"=>$mongoId])>0;
    }

    /**
     * Fires an internal event that cancels the operation
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

    public static function summatory($field, $conditions = null, $finalize = null)
    {
        throw new Exception('The summatory() method is not implemented in the new Mvc MongoCollection');
    }

    /**
     * Pass the values from the BSON document back to the object.
     *
     * @param array $data
     */
    public function bsonUnserialize(array $data)
    {
        $this->setDI(Di::getDefault());
        $this->_modelsManager = Di::getDefault()->getShared('collectionManager');

        foreach ($data as $key => $val) {
            $this->{$key} = $val;
        }

        if (method_exists($this, "afterFetch")) {
            $this->afterFetch();
        }
    }
}
