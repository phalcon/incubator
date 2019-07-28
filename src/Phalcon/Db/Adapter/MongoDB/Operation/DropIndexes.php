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

use MongoDB\Driver\Command;
use MongoDB\Driver\Server;
use Phalcon\Db\Adapter\MongoDB\Exception\InvalidArgumentException;

/**
 * Operation for the dropIndexes command.
 *
 * @package Phalcon\Db\Adapter\MongoDB\Operation
 */
class DropIndexes implements Executable
{
    private $databaseName;
    private $collectionName;
    private $indexName;
    private $options;

    /**
     * Constructs a dropIndexes command.
     *
     * Supported options:
     *
     *  * typeMap (array): Type map for BSON deserialization. This will be used
     *    for the returned command result document.
     *
     * @param string $databaseName Database name
     * @param string $collectionName Collection name
     * @param string $indexName Index name (use "*" to drop all indexes)
     * @param array  $options Command options
     *
     * @throws InvalidArgumentException
     */
    public function __construct($databaseName, $collectionName, $indexName, array $options = [])
    {
        $indexName=(string)$indexName;

        if ($indexName==='') {
            throw new InvalidArgumentException('$indexName cannot be empty');
        }

        if (isset($options['typeMap'])&&!is_array($options['typeMap'])) {
            throw InvalidArgumentException::invalidType('"typeMap" option', $options['typeMap'], 'array');
        }

        $this->databaseName  =(string)$databaseName;
        $this->collectionName=(string)$collectionName;
        $this->indexName     =$indexName;
        $this->options       =$options;
    }

    /**
     * Execute the operation.
     *
     * @see Executable::execute()
     *
     * @param Server $server
     *
     * @return array|object Command result document
     */
    public function execute(Server $server)
    {
        $cmd=[
            'dropIndexes'=>$this->collectionName,
            'index'      =>$this->indexName,
        ];

        $cursor=$server->executeCommand($this->databaseName, new Command($cmd));

        if (isset($this->options['typeMap'])) {
            $cursor->setTypeMap($this->options['typeMap']);
        }

        return current($cursor->toArray());
    }
}
