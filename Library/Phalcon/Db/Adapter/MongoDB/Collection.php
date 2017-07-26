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

namespace Phalcon\Db\Adapter\MongoDB;

use Traversable;
use MongoDB\Driver\Cursor;
use MongoDB\Driver\Manager;
use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use Phalcon\Db\Adapter\MongoDB\Model;
use Phalcon\Db\Adapter\MongoDB\Operation;
use Phalcon\Db\Adapter\MongoDB\Exception\InvalidArgumentException;

/**
 * Phalcon\Db\Adapter\MongoDB\Collection
 *
 * @package Phalcon\Db\Adapter\MongoDB
 */
class Collection
{
    private static $defaultTypeMap = [
        'array'    => Model\BSONArray::class,
        'document' => Model\BSONDocument::class,
        'root'     => Model\BSONDocument::class,
    ];

    private static $wireVersionForFindAndModifyWriteConcern = 4;

    private $collectionName;
    private $databaseName;
    private $manager;
    private $readConcern;
    private $readPreference;
    private $typeMap;
    private $writeConcern;

    /**
     * Constructs new Collection instance.
     *
     * This class provides methods for collection-specific operations, such as
     * CRUD (i.e. create, read, update, and delete) and index management.
     *
     * Supported options:
     *
     *  * readConcern (MongoDB\Driver\ReadConcern): The default read concern to
     *    use for collection operations. Defaults to the Manager's read concern.
     *
     *  * readPreference (MongoDB\Driver\ReadPreference): The default read
     *    preference to use for collection operations. Defaults to the Manager's
     *    read preference.
     *
     *  * typeMap (array): Default type map for cursors and BSON documents.
     *
     *  * writeConcern (MongoDB\Driver\WriteConcern): The default write concern
     *    to use for collection operations. Defaults to the Manager's write
     *    concern.
     *
     * @param Manager $manager Manager instance from the driver
     * @param string  $databaseName Database name
     * @param string  $collectionName Collection name
     * @param array   $options Collection options
     *
     * @throws InvalidArgumentException
     */
    public function __construct(Manager $manager, $databaseName, $collectionName, array $options = [])
    {
        if (strlen($databaseName)<1) {
            throw new InvalidArgumentException('$databaseName is invalid: '.$databaseName);
        }

        if (strlen($collectionName)<1) {
            throw new InvalidArgumentException('$collectionName is invalid: '.$collectionName);
        }

        if (isset($options['readConcern'])&&!$options['readConcern'] instanceof ReadConcern) {
            throw InvalidArgumentException::invalidType(
                '"readConcern" option',
                $options['readConcern'],
                'MongoDB\Driver\ReadConcern'
            );
        }

        if (isset($options['readPreference'])&&!$options['readPreference'] instanceof ReadPreference) {
            throw InvalidArgumentException::invalidType(
                '"readPreference" option',
                $options['readPreference'],
                'MongoDB\Driver\ReadPreference'
            );
        }

        if (isset($options['typeMap'])&&!is_array($options['typeMap'])) {
            throw InvalidArgumentException::invalidType('"typeMap" option', $options['typeMap'], 'array');
        }

        if (isset($options['writeConcern'])&&!$options['writeConcern'] instanceof WriteConcern) {
            throw InvalidArgumentException::invalidType(
                '"writeConcern" option',
                $options['writeConcern'],
                'MongoDB\Driver\WriteConcern'
            );
        }

        $this->manager       =$manager;
        $this->databaseName  =(string)$databaseName;
        $this->collectionName=(string)$collectionName;
        $this->readConcern   =isset($options['readConcern'])?$options['readConcern']:$this->manager->getReadConcern();
        $this->readPreference=isset($options['readPreference'])
            ?$options['readPreference']
            :$this->manager->getReadPreference();
        $this->typeMap       =isset($options['typeMap'])?$options['typeMap']:self::$defaultTypeMap;
        $this->writeConcern  =isset($options['writeConcern'])
            ?$options['writeConcern']
            :$this->manager->getWriteConcern();
    }

    /**
     * Return internal properties for debugging purposes.
     *
     * @see http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.debuginfo
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'collectionName'=>$this->collectionName,
            'databaseName'  =>$this->databaseName,
            'manager'       =>$this->manager,
            'readConcern'   =>$this->readConcern,
            'readPreference'=>$this->readPreference,
            'typeMap'       =>$this->typeMap,
            'writeConcern'  =>$this->writeConcern,
        ];
    }

    /**
     * Return the collection namespace (e.g. "db.collection").
     *
     * @see https://docs.mongodb.org/manual/faq/developers/#faq-dev-namespace
     * @return string
     */
    public function __toString()
    {
        return $this->databaseName.'.'.$this->collectionName;
    }

    /**
     * Executes an aggregation framework pipeline on the collection.
     *
     * Note: this method's return value depends on the MongoDB server version
     * and the "useCursor" option. If "useCursor" is true, a Cursor will be
     * returned; otherwise, an ArrayIterator is returned, which wraps the
     * "result" array from the command response document.
     *
     * Note: BSON deserialization of inline aggregation results (i.e. not using
     * a command cursor) does not yet support a custom type map
     * (depends on: https://jira.mongodb.org/browse/PHPC-314).
     *
     * @param array $pipeline List of pipeline operations
     * @param array $options Command options
     *
     * @return Traversable
     */
    public function aggregate(array $pipeline, array $options = [])
    {
        $hasOutStage=Functions::isLastPipelineOperatorOut($pipeline);

        /**
         * A "majority" read concern is not compatible with the $out stage, so
         * avoid providing the Collection's read concern if it would conflict.
         */
        if (!isset($options['readConcern'])&&!($hasOutStage&&$this->readConcern->getLevel()===ReadConcern::MAJORITY)) {
            $options['readConcern']=$this->readConcern;
        }

        if (!isset($options['readPreference'])) {
            $options['readPreference']=$this->readPreference;
        }

        if ($hasOutStage) {
            $options['readPreference']=new ReadPreference(ReadPreference::RP_PRIMARY);
        }

        if (!isset($options['typeMap'])&&(!isset($options['useCursor'])||$options['useCursor'])) {
            $options['typeMap']=$this->typeMap;
        }

        $operation = new Operation\Aggregate($this->databaseName, $this->collectionName, $pipeline, $options);
        $server    = $this->manager->selectServer($options['readPreference']);

        return $operation->execute($server);
    }

    /**
     * Executes multiple write operations.
     *
     * @param array[] $operations List of write operations
     * @param array   $options Command options
     *
     * @return BulkWriteResult
     */
    public function bulkWrite(array $operations, array $options = [])
    {
        if (!isset($options['writeConcern'])) {
            $options['writeConcern']=$this->writeConcern;
        }

        $operation=new Operation\BulkWrite($this->databaseName, $this->collectionName, $operations, $options);
        $server   =$this->manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));

        return $operation->execute($server);
    }

    /**
     * Gets the number of documents matching the filter.
     *
     * @param array|object $filter Query by which to filter documents
     * @param array        $options Command options
     *
     * @return integer
     */
    public function count($filter = [], array $options = [])
    {
        if (!isset($options['readConcern'])) {
            $options['readConcern']=$this->readConcern;
        }

        if (!isset($options['readPreference'])) {
            $options['readPreference']=$this->readPreference;
        }

        $operation=new Operation\Count($this->databaseName, $this->collectionName, $filter, $options);
        $server   =$this->manager->selectServer($options['readPreference']);

        return $operation->execute($server);
    }

    /**
     * Create a single index for the collection.
     *
     * @param array|object $key Document containing fields mapped to values,
     *                              which denote order or an index type
     * @param array        $options Index options
     *
     * @return string The name of the created index
     */
    public function createIndex($key, array $options = [])
    {
        return current($this->createIndexes([['key'=>$key]+$options]));
    }

    /**
     * Create one or more indexes for the collection.
     *
     * Each element in the $indexes array must have a "key" document, which
     * contains fields mapped to an order or type. Other options may follow.
     * For example:
     *
     *     $indexes = [
     *         // Create a unique index on the "username" field
     *         [ 'key' => [ 'username' => 1 ], 'unique' => true ],
     *         // Create a 2dsphere index on the "loc" field with a custom name
     *         [ 'key' => [ 'loc' => '2dsphere' ], 'name' => 'geo' ],
     *     ];
     *
     * If the "name" option is unspecified, a name will be generated from the
     * "key" document.
     *
     * @param array[] $indexes List of index specifications
     *
     * @return string[] The names of the created indexes
     * @throws InvalidArgumentException if an index specification is invalid
     */
    public function createIndexes(array $indexes)
    {
        $operation=new Operation\CreateIndexes($this->databaseName, $this->collectionName, $indexes);
        $server   =$this->manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));

        return $operation->execute($server);
    }

    /**
     * Deletes all documents matching the filter.
     *
     * @param array|object $filter Query by which to delete documents
     * @param array        $options Command options
     *
     * @return DeleteResult
     */
    public function deleteMany($filter, array $options = [])
    {
        if (!isset($options['writeConcern'])) {
            $options['writeConcern']=$this->writeConcern;
        }

        $operation=new Operation\DeleteMany($this->databaseName, $this->collectionName, $filter, $options);
        $server   =$this->manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));

        return $operation->execute($server);
    }

    /**
     * Deletes at most one document matching the filter.
     *
     * @param array|object $filter Query by which to delete documents
     * @param array        $options Command options
     *
     * @return DeleteResult
     */
    public function deleteOne($filter, array $options = [])
    {
        if (!isset($options['writeConcern'])) {
            $options['writeConcern']=$this->writeConcern;
        }

        $operation=new Operation\DeleteOne($this->databaseName, $this->collectionName, $filter, $options);
        $server   =$this->manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));

        return $operation->execute($server);
    }

    /**
     * Finds the distinct values for a specified field across the collection.
     *
     * @param string       $fieldName Field for which to return distinct values
     * @param array|object $filter Query by which to filter documents
     * @param array        $options Command options
     *
     * @return mixed[]
     */
    public function distinct($fieldName, $filter = [], array $options = [])
    {
        if (!isset($options['readConcern'])) {
            $options['readConcern']=$this->readConcern;
        }

        if (!isset($options['readPreference'])) {
            $options['readPreference']=$this->readPreference;
        }

        $operation=new Operation\Distinct($this->databaseName, $this->collectionName, $fieldName, $filter, $options);
        $server   =$this->manager->selectServer($options['readPreference']);

        return $operation->execute($server);
    }

    /**
     * Drop this collection.
     *
     * @param array $options Additional options
     *
     * @return array|object Command result document
     */
    public function drop(array $options = [])
    {
        if (!isset($options['typeMap'])) {
            $options['typeMap']=$this->typeMap;
        }

        $operation=new Operation\DropCollection($this->databaseName, $this->collectionName, $options);
        $server   =$this->manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));

        return $operation->execute($server);
    }

    /**
     * Drop a single index in the collection.
     *
     * @param string $indexName Index name
     * @param array  $options Additional options
     *
     * @return array|object Command result document
     * @throws InvalidArgumentException if $indexName is an empty string or "*"
     */
    public function dropIndex($indexName, array $options = [])
    {
        $indexName=(string)$indexName;

        if ($indexName==='*') {
            throw new InvalidArgumentException('dropIndexes() must be used to drop multiple indexes');
        }

        if (!isset($options['typeMap'])) {
            $options['typeMap']=$this->typeMap;
        }

        $operation=new Operation\DropIndexes($this->databaseName, $this->collectionName, $indexName, $options);
        $server   =$this->manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));

        return $operation->execute($server);
    }

    /**
     * Drop all indexes in the collection.
     *
     * @param array $options Additional options
     *
     * @return array|object Command result document
     */
    public function dropIndexes(array $options = [])
    {
        if (!isset($options['typeMap'])) {
            $options['typeMap']=$this->typeMap;
        }

        $operation=new Operation\DropIndexes($this->databaseName, $this->collectionName, '*', $options);
        $server   =$this->manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));

        return $operation->execute($server);
    }

    /**
     * Finds documents matching the query.
     *
     * @param array|object $filter Query by which to filter documents
     * @param array        $options Additional options
     *
     * @return Cursor
     */
    public function find($filter = [], array $options = [])
    {
        if (!isset($options['readConcern'])) {
            $options['readConcern']=$this->readConcern;
        }

        if (!isset($options['readPreference'])) {
            $options['readPreference']=$this->readPreference;
        }

        if (!isset($options['typeMap'])) {
            $options['typeMap']=$this->typeMap;
        }

        $operation=new Operation\Find($this->databaseName, $this->collectionName, $filter, $options);
        $server   =$this->manager->selectServer($options['readPreference']);

        return $operation->execute($server);
    }

    /**
     * Finds a single document matching the query.
     *
     * @param array|object $filter Query by which to filter documents
     * @param array        $options Additional options
     *
     * @return array|object|null
     */
    public function findOne($filter = [], array $options = [])
    {
        if (!isset($options['readConcern'])) {
            $options['readConcern']=$this->readConcern;
        }

        if (!isset($options['readPreference'])) {
            $options['readPreference']=$this->readPreference;
        }

        if (!isset($options['typeMap'])) {
            $options['typeMap']=$this->typeMap;
        }

        $operation=new Operation\FindOne($this->databaseName, $this->collectionName, $filter, $options);
        $server   =$this->manager->selectServer($options['readPreference']);

        return $operation->execute($server);
    }

    /**
     * Finds a single document and deletes it, returning the original.
     *
     * The document to return may be null if no document matched the filter.
     *
     * Note: BSON deserialization of the returned document does not yet support
     * a custom type map (depends on: https://jira.mongodb.org/browse/PHPC-314).
     *
     * @param  array|object $filter  Query by which to filter documents
     * @param  array        $options Command options [Optional]
     * @return object|null
     */
    public function findOneAndDelete($filter, array $options = [])
    {
        $server = $this->manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));

        if (!isset($options['writeConcern']) && Functions::serverSupportsFeature(
            $server,
            self::$wireVersionForFindAndModifyWriteConcern
        )
        ) {
            $options['writeConcern']=$this->writeConcern;
        }

        $operation = new Operation\FindOneAndDelete($this->databaseName, $this->collectionName, $filter, $options);

        return $operation->execute($server);
    }

    /**
     * Finds a single document and replaces it, returning either the original or
     * the replaced document.
     *
     * The document to return may be null if no document matched the filter. By
     * default, the original document is returned. Specify
     * FindOneAndReplace::RETURN_DOCUMENT_AFTER for the "returnDocument" option
     * to return the updated document.
     *
     * Note: BSON deserialization of the returned document does not yet support
     * a custom type map (depends on: https://jira.mongodb.org/browse/PHPC-314).
     *
     * @param  array|object $filter      Query by which to filter documents
     * @param  array|object $replacement Replacement document
     * @param  array        $options     Command options [Optional]
     * @return object|null
     */
    public function findOneAndReplace($filter, $replacement, array $options = [])
    {
        $server = $this->manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));

        if (!isset($options['writeConcern']) && Functions::serverSupportsFeature(
            $server,
            self::$wireVersionForFindAndModifyWriteConcern
        )
        ) {
            $options['writeConcern'] = $this->writeConcern;
        }

        $operation = new Operation\FindOneAndReplace(
            $this->databaseName,
            $this->collectionName,
            $filter,
            $replacement,
            $options
        );

        return $operation->execute($server);
    }

    /**
     * Finds a single document and updates it, returning either the original or
     * the updated document.
     *
     * The document to return may be null if no document matched the filter. By
     * default, the original document is returned. Specify
     * FindOneAndUpdate::RETURN_DOCUMENT_AFTER for the "returnDocument" option
     * to return the updated document.
     *
     * Note: BSON deserialization of the returned document does not yet support
     * a custom type map (depends on: https://jira.mongodb.org/browse/PHPC-314).
     *
     * @param  array|object $filter  Query by which to filter documents
     * @param  array|object $update  Update to apply to the matched document
     * @param  array        $options Command options [Optional]
     * @return object|null
     */
    public function findOneAndUpdate($filter, $update, array $options = [])
    {
        $server = $this->manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));

        if (!isset($options['writeConcern']) && Functions::serverSupportsFeature(
            $server,
            self::$wireVersionForFindAndModifyWriteConcern
        )
        ) {
            $options['writeConcern'] = $this->writeConcern;
        }

        $operation = new Operation\FindOneAndUpdate(
            $this->databaseName,
            $this->collectionName,
            $filter,
            $update,
            $options
        );

        return $operation->execute($server);
    }

    /**
     * Return the collection name.
     *
     * @return string
     */
    public function getCollectionName()
    {
        return $this->collectionName;
    }

    /**
     * Return the database name.
     *
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    /**
     * Return the collection namespace.
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->databaseName . '.' . $this->collectionName;
    }

    /**
     * Inserts multiple documents.
     *
     * @param  array[]|object[] $documents The documents to insert
     * @param  array            $options   Command options [Optional]
     * @return InsertManyResult
     */
    public function insertMany(array $documents, array $options = [])
    {
        if (!isset($options['writeConcern'])) {
            $options['writeConcern'] = $this->writeConcern;
        }

        $operation = new Operation\InsertMany($this->databaseName, $this->collectionName, $documents, $options);
        $server    = $this->manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));

        return $operation->execute($server);
    }

    /**
     * Inserts one document.
     *
     * @param array|object $document The document to insert
     * @param array        $options  Command options [Optional]
     * @return InsertOneResult
     */
    public function insertOne($document, array $options = [])
    {
        if (!isset($options['writeConcern'])) {
            $options['writeConcern'] = $this->writeConcern;
        }

        $operation = new Operation\InsertOne($this->databaseName, $this->collectionName, $document, $options);
        $server    = $this->manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));

        return $operation->execute($server);
    }

    /**
     * Inserts the document.
     *
     * @param  array|object $document The document to insert
     * @param  array        $options  Command options [Optional]
     * @return mixed
     */
    public function insert($document, array $options = [])
    {
        return $this->insertOne($document, $options);
    }

    /**
     * Returns information for all indexes for the collection.
     *
     * @param  array $options Command options [Optional]
     * @return Model\IndexInfoIterator
     */
    public function listIndexes(array $options = [])
    {
        $operation = new Operation\ListIndexes($this->databaseName, $this->collectionName, $options);
        $server    = $this->manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));

        return $operation->execute($server);
    }

    /**
     * Replaces at most one document matching the filter.
     *
     * @param  array|object $filter      Query by which to filter documents
     * @param  array|object $replacement Replacement document
     * @param  array        $options     Command options [Optional]
     * @return UpdateResult
     */
    public function replaceOne($filter, $replacement, array $options = [])
    {
        if (!isset($options['writeConcern'])) {
            $options['writeConcern'] = $this->writeConcern;
        }

        $operation = new Operation\ReplaceOne(
            $this->databaseName,
            $this->collectionName,
            $filter,
            $replacement,
            $options
        );

        $server = $this->manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));

        return $operation->execute($server);
    }

    /**
     * Updates all documents matching the filter.
     *
     * @param  array|object $filter  Query by which to filter documents
     * @param  array|object $update  Update to apply to the matched documents
     * @param  array        $options Command options [Optional]
     * @return UpdateResult
     */
    public function updateMany($filter, $update, array $options = [])
    {
        if (!isset($options['writeConcern'])) {
            $options['writeConcern'] = $this->writeConcern;
        }

        $operation = new Operation\UpdateMany($this->databaseName, $this->collectionName, $filter, $update, $options);
        $server    = $this->manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));

        return $operation->execute($server);
    }

    /**
     * Updates at most one document matching the filter.
     *
     * @param  array|object $filter  Query by which to filter documents
     * @param  array|object $update  Update to apply to the matched document
     * @param  array        $options Command options [Optional]
     * @return UpdateResult
     */
    public function updateOne($filter, $update, array $options = [])
    {
        if (!isset($options['writeConcern'])) {
            $options['writeConcern'] = $this->writeConcern;
        }

        $operation = new Operation\UpdateOne($this->databaseName, $this->collectionName, $filter, $update, $options);
        $server    = $this->manager->selectServer(new ReadPreference(ReadPreference::RP_PRIMARY));

        return $operation->execute($server);
    }

    /**
     * Get a clone of this collection with different options.
     *
     * @param  array $options Collection constructor options [Optional]
     * @return Collection
     */
    public function withOptions(array $options = [])
    {
        $options += [
            'readConcern'    => $this->readConcern,
            'readPreference' => $this->readPreference,
            'typeMap'        => $this->typeMap,
            'writeConcern'   => $this->writeConcern,
        ];

        return new Collection($this->manager, $this->databaseName, $this->collectionName, $options);
    }
}
