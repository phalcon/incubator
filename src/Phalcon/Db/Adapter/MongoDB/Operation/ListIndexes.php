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
use MongoDB\Driver\Query;
use MongoDB\Driver\Server;
use MongoDB\Driver\Exception\RuntimeException;
use Phalcon\Db\Adapter\MongoDB\Exception\InvalidArgumentException;
use Phalcon\Db\Adapter\MongoDB\Functions;
use Phalcon\Db\Adapter\MongoDB\Model\IndexInfoIterator;
use Phalcon\Db\Adapter\MongoDB\Model\IndexInfoIteratorIterator;
use EmptyIterator;

/**
 * Operation for the listIndexes command.
 *
 * @package Phalcon\Db\Adapter\MongoDB\Operation
 */
class ListIndexes implements Executable
{
    private static $errorCodeDatabaseNotFound=60;
    private static $errorCodeNamespaceNotFound=26;
    private static $wireVersionForCommand=3;

    private $databaseName;
    private $collectionName;
    private $options;

    /**
     * Constructs a listIndexes command.
     *
     * Supported options:
     *
     *  * maxTimeMS (integer): The maximum amount of time to allow the query to
     *    run.
     *
     * @param string $databaseName Database name
     * @param string $collectionName Collection name
     * @param array  $options Command options
     *
     * @throws InvalidArgumentException
     */
    public function __construct($databaseName, $collectionName, array $options = [])
    {
        if (isset($options['maxTimeMS'])&&!is_integer($options['maxTimeMS'])) {
            throw InvalidArgumentException::invalidType('"maxTimeMS" option', $options['maxTimeMS'], 'integer');
        }

        $this->databaseName  =(string)$databaseName;
        $this->collectionName=(string)$collectionName;
        $this->options       =$options;
    }

    /**
     * Execute the operation.
     *
     * @see Executable::execute()
     *
     * @param Server $server
     *
     * @return IndexInfoIterator
     */
    public function execute(Server $server)
    {
        return Functions::serverSupportsFeature($server, self::$wireVersionForCommand)
            ?$this->executeCommand($server)
            :$this->executeLegacy($server);
    }

    /**
     * Returns information for all indexes for this collection using the
     * listIndexes command.
     *
     * @param Server $server
     *
     * @return IndexInfoIteratorIterator
     */
    private function executeCommand(Server $server)
    {
        $cmd=['listIndexes'=>$this->collectionName];

        if (isset($this->options['maxTimeMS'])) {
            $cmd['maxTimeMS']=$this->options['maxTimeMS'];
        }

        try {
            $cursor=$server->executeCommand($this->databaseName, new Command($cmd));
        } catch (RuntimeException $e) {
            /* The server may return an error if the collection does not exist.
             * Check for possible error codes (see: SERVER-20463) and return an
             * empty iterator instead of throwing.
             */
            if ($e->getCode()===self::$errorCodeNamespaceNotFound||$e->getCode()===self::$errorCodeDatabaseNotFound) {
                return new IndexInfoIteratorIterator(new EmptyIterator);
            }

            throw $e;
        }

        $cursor->setTypeMap(['root'=>'array','document'=>'array']);

        return new IndexInfoIteratorIterator($cursor);
    }

    /**
     * Returns information for all indexes for this collection by querying the
     * "system.indexes" collection (MongoDB <3.0).
     *
     * @param Server $server
     *
     * @return IndexInfoIteratorIterator
     */
    private function executeLegacy(Server $server)
    {
        $filter=['ns'=>$this->databaseName.'.'.$this->collectionName];

        $options=isset($this->options['maxTimeMS'])?['modifiers'=>['$maxTimeMS'=>$this->options['maxTimeMS']]]:[];

        $cursor=$server->executeQuery($this->databaseName.'.system.indexes', new Query($filter, $options));
        $cursor->setTypeMap(['root'=>'array','document'=>'array']);

        return new IndexInfoIteratorIterator($cursor);
    }
}
