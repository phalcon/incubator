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
use Phalcon\Db\Adapter\MongoDB\Exception\InvalidArgumentException;
use Phalcon\Db\Adapter\MongoDB\Functions;
use Phalcon\Db\Adapter\MongoDB\Model\CollectionInfoCommandIterator;
use Phalcon\Db\Adapter\MongoDB\Model\CollectionInfoIterator;
use Phalcon\Db\Adapter\MongoDB\Model\CollectionInfoLegacyIterator;

/**
 * Operation for the listCollections command.
 *
 * @package Phalcon\Db\Adapter\MongoDB\Operation
 */
class ListCollections implements Executable
{
    private static $wireVersionForCommand=3;

    private $databaseName;
    private $options;

    /**
     * Constructs a listCollections command.
     *
     * Supported options:
     *
     *  * filter (document): Query by which to filter collections.
     *
     *  * maxTimeMS (integer): The maximum amount of time to allow the query to
     *    run.
     *
     * @param string $databaseName Database name
     * @param array  $options Command options
     *
     * @throws InvalidArgumentException
     */
    public function __construct($databaseName, array $options = [])
    {
        if (isset($options['filter'])&&!is_array($options['filter'])&&!is_object($options['filter'])) {
            throw InvalidArgumentException::invalidType('"filter" option', $options['filter'], 'array or object');
        }

        if (isset($options['maxTimeMS'])&&!is_integer($options['maxTimeMS'])) {
            throw InvalidArgumentException::invalidType('"maxTimeMS" option', $options['maxTimeMS'], 'integer');
        }

        $this->databaseName=(string)$databaseName;
        $this->options     =$options;
    }

    /**
     * Execute the operation.
     *
     * @see Executable::execute()
     *
     * @param Server $server
     *
     * @return CollectionInfoIterator
     */
    public function execute(Server $server)
    {
        return Functions::serverSupportsFeature(
            $server,
            self::$wireVersionForCommand
        )?$this->executeCommand($server):$this->executeLegacy($server);
    }

    /**
     * Returns information for all collections in this database using the
     * listCollections command.
     *
     * @param Server $server
     *
     * @return CollectionInfoCommandIterator
     */
    private function executeCommand(Server $server)
    {
        $cmd=['listCollections'=>1];

        if (!empty($this->options['filter'])) {
            $cmd['filter']=(object)$this->options['filter'];
        }

        if (isset($this->options['maxTimeMS'])) {
            $cmd['maxTimeMS']=$this->options['maxTimeMS'];
        }

        $cursor=$server->executeCommand($this->databaseName, new Command($cmd));
        $cursor->setTypeMap(['root'=>'array','document'=>'array']);

        return new CollectionInfoCommandIterator($cursor);
    }

    /**
     * Returns information for all collections in this database by querying the
     * "system.namespaces" collection (MongoDB <3.0).
     *
     * @param Server $server
     *
     * @return CollectionInfoLegacyIterator
     * @throws InvalidArgumentException if filter.name is not a string.
     */
    private function executeLegacy(Server $server)
    {
        $filter=empty($this->options['filter'])?[]:(array)$this->options['filter'];

        if (array_key_exists('name', $filter)) {
            if (!is_string($filter['name'])) {
                throw InvalidArgumentException::invalidType('filter name for MongoDB <3.0', $filter['name'], 'string');
            }

            $filter['name']=$this->databaseName.'.'.$filter['name'];
        }

        $options=isset($this->options['maxTimeMS'])?['modifiers'=>['$maxTimeMS'=>$this->options['maxTimeMS']]]:[];

        $cursor=$server->executeQuery($this->databaseName.'.system.namespaces', new Query($filter, $options));
        $cursor->setTypeMap(['root'=>'array','document'=>'array']);

        return new CollectionInfoLegacyIterator($cursor);
    }
}
