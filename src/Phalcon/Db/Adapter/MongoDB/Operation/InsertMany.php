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

namespace Phalcon\Db\Adapter\MongoDB\Operation;

use Phalcon\Db\Adapter\MongoDB\Functions;
use Phalcon\Db\Adapter\MongoDB\InsertManyResult;
use MongoDB\Driver\BulkWrite as Bulk;
use MongoDB\Driver\Server;
use MongoDB\Driver\WriteConcern;
use Phalcon\Db\Adapter\MongoDB\Exception\InvalidArgumentException;

/**
 * Operation for inserting multiple documents with the insert command.
 *
 * @package Phalcon\Db\Adapter\MongoDB\Operation
 */
class InsertMany implements Executable
{
    private static $wireVersionForDocumentLevelValidation=4;

    private $databaseName;
    private $collectionName;
    private $documents;
    private $options;

    /**
     * Constructs an insert command.
     *
     * Supported options:
     *
     *  * bypassDocumentValidation (boolean): If true, allows the write to opt
     *    out of document level validation.
     *
     *  * ordered (boolean): If true, when an insert fails, return without
     *    performing the remaining writes. If false, when a write fails,
     *    continue with the remaining writes, if any. The default is true.
     *
     *  * writeConcern (MongoDB\Driver\WriteConcern): Write concern.
     *
     * @param string           $databaseName Database name
     * @param string           $collectionName Collection name
     * @param array[]|object[] $documents List of documents to insert
     * @param array            $options Command options
     *
     * @throws InvalidArgumentException
     */
    public function __construct($databaseName, $collectionName, array $documents, array $options = [])
    {
        if (empty($documents)) {
            throw new InvalidArgumentException('$documents is empty');
        }

        $expectedIndex=0;

        foreach ($documents as $i => $document) {
            if ($i!==$expectedIndex) {
                throw new InvalidArgumentException(sprintf('$documents is not a list (unexpected index: "%s")', $i));
            }

            if (!is_array($document)&&!is_object($document)) {
                throw InvalidArgumentException::invalidType(
                    sprintf('$documents[%d]', $i),
                    $document,
                    'array or object'
                );
            }

            $expectedIndex+=1;
        }

        $options+=['ordered'=>true];

        if (isset($options['bypassDocumentValidation'])&&!is_bool($options['bypassDocumentValidation'])) {
            throw InvalidArgumentException::invalidType(
                '"bypassDocumentValidation" option',
                $options['bypassDocumentValidation'],
                'boolean'
            );
        }

        if (!is_bool($options['ordered'])) {
            throw InvalidArgumentException::invalidType('"ordered" option', $options['ordered'], 'boolean');
        }

        if (isset($options['writeConcern'])&&!$options['writeConcern'] instanceof WriteConcern) {
            throw InvalidArgumentException::invalidType(
                '"writeConcern" option',
                $options['writeConcern'],
                'MongoDB\Driver\WriteConcern'
            );
        }

        $this->databaseName  =(string)$databaseName;
        $this->collectionName=(string)$collectionName;
        $this->documents     =$documents;
        $this->options       =$options;
    }

    /**
     * Execute the operation.
     *
     * @see Executable::execute()
     *
     * @param Server $server
     *
     * @return InsertManyResult
     */
    public function execute(Server $server)
    {
        $options=['ordered'=>$this->options['ordered']];

        if (isset($this->options['bypassDocumentValidation'])&&Functions::serverSupportsFeature(
            $server,
            self::$wireVersionForDocumentLevelValidation
        )
        ) {
            $options['bypassDocumentValidation']=$this->options['bypassDocumentValidation'];
        }

        $bulk       =new Bulk($options);
        $insertedIds=[];

        foreach ($this->documents as $i => $document) {
            $insertedId=$bulk->insert($document);

            if ($insertedId!==null) {
                $insertedIds[ $i ]=$insertedId;
            } else {
                $insertedIds[ $i ]=Functions::extractIdFromInsertedDocument($document);
            }
        }

        $writeConcern=isset($this->options['writeConcern'])?$this->options['writeConcern']:null;
        $writeResult =$server->executeBulkWrite($this->databaseName.'.'.$this->collectionName, $bulk, $writeConcern);

        return new InsertManyResult($writeResult, $insertedIds);
    }
}
