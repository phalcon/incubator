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
use Phalcon\Db\Adapter\MongoDB\InsertOneResult;
use MongoDB\Driver\BulkWrite as Bulk;
use MongoDB\Driver\Server;
use MongoDB\Driver\WriteConcern;
use Phalcon\Db\Adapter\MongoDB\Exception\InvalidArgumentException;

/**
 * Operation for inserting a single document with the insert command.
 *
 * @package Phalcon\Db\Adapter\MongoDB\Operation
 */
class InsertOne implements Executable
{
    private static $wireVersionForDocumentLevelValidation=4;

    private $databaseName;
    private $collectionName;
    private $document;
    private $options;

    /**
     * Constructs an insert command.
     *
     * Supported options:
     *
     *  * bypassDocumentValidation (boolean): If true, allows the write to opt
     *    out of document level validation.
     *
     *  * writeConcern (MongoDB\Driver\WriteConcern): Write concern.
     *
     * @param string       $databaseName Database name
     * @param string       $collectionName Collection name
     * @param array|object $document Document to insert
     * @param array        $options Command options
     *
     * @throws InvalidArgumentException
     */
    public function __construct($databaseName, $collectionName, $document, array $options = [])
    {
        if (!is_array($document)&&!is_object($document)) {
            throw InvalidArgumentException::invalidType('$document', $document, 'array or object');
        }

        if (isset($options['bypassDocumentValidation'])&&!is_bool($options['bypassDocumentValidation'])) {
            throw InvalidArgumentException::invalidType(
                '"bypassDocumentValidation" option',
                $options['bypassDocumentValidation'],
                'boolean'
            );
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
        $this->document      =$document;
        $this->options       =$options;
    }

    /**
     * Execute the operation.
     *
     * @see Executable::execute()
     *
     * @param Server $server
     *
     * @return InsertOneResult
     */
    public function execute(Server $server)
    {
        $options=[];

        if (isset($this->options['bypassDocumentValidation'])&&Functions::serverSupportsFeature(
            $server,
            self::$wireVersionForDocumentLevelValidation
        )
        ) {
            $options['bypassDocumentValidation']=$this->options['bypassDocumentValidation'];
        }

        $bulk      =new Bulk($options);
        $insertedId=$bulk->insert($this->document);

        if ($insertedId===null) {
            $insertedId=Functions::extractIdFromInsertedDocument($this->document);
        }

        $writeConcern=isset($this->options['writeConcern'])?$this->options['writeConcern']:null;
        $writeResult =$server->executeBulkWrite($this->databaseName.'.'.$this->collectionName, $bulk, $writeConcern);

        return new InsertOneResult($writeResult, $insertedId);
    }
}
