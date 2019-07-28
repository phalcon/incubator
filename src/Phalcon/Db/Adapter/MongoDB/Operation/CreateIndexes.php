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
use MongoDB\Driver\BulkWrite as Bulk;
use MongoDB\Driver\WriteConcern;
use Phalcon\Db\Adapter\MongoDB\Exception\InvalidArgumentException;
use Phalcon\Db\Adapter\MongoDB\Model\IndexInput;
use Phalcon\Db\Adapter\MongoDB\Functions;

/**
 * Operation for the createIndexes command.
 *
 * @package Phalcon\Db\Adapter\MongoDB\Operation
 */
class CreateIndexes implements Executable
{
    private static $wireVersionForCommand=2;

    private $databaseName;
    private $collectionName;
    private $indexes=[];

    /**
     * Constructs a createIndexes command.
     *
     * @param string  $databaseName Database name
     * @param string  $collectionName Collection name
     * @param array[] $indexes List of index specifications
     *
     * @throws InvalidArgumentException
     */
    public function __construct($databaseName, $collectionName, array $indexes)
    {
        if (empty($indexes)) {
            throw new InvalidArgumentException('$indexes is empty');
        }

        $expectedIndex=0;

        foreach ($indexes as $i => $index) {
            if ($i!==$expectedIndex) {
                throw new InvalidArgumentException(sprintf('$indexes is not a list (unexpected index: "%s")', $i));
            }

            if (!is_array($index)) {
                throw InvalidArgumentException::invalidType(sprintf('$index[%d]', $i), $index, 'array');
            }

            if (!isset($index['ns'])) {
                $index['ns']=$databaseName.'.'.$collectionName;
            }

            $this->indexes[]=new IndexInput($index);

            $expectedIndex+=1;
        }

        $this->databaseName  =(string)$databaseName;
        $this->collectionName=(string)$collectionName;
    }

    /**
     * Execute the operation.
     *
     * For servers < 2.6, this will actually perform an insert operation on the
     * database's "system.indexes" collection.
     *
     * @see Executable::execute()
     *
     * @param Server $server
     *
     * @return string[] The names of the created indexes
     */
    public function execute(Server $server)
    {
        if (Functions::serverSupportsFeature($server, self::$wireVersionForCommand)) {
            $this->executeCommand($server);
        } else {
            $this->executeLegacy($server);
        }

        return array_map(function (IndexInput $index) {
            return (string)$index;
        }, $this->indexes);
    }

    /**
     * Create one or more indexes for the collection using the createIndexes
     * command.
     *
     * @param Server $server
     */
    private function executeCommand(Server $server)
    {
        $command=new Command([
            'createIndexes'=>$this->collectionName,
            'indexes'      =>$this->indexes,
        ]);

        $server->executeCommand($this->databaseName, $command);
    }

    /**
     * Create one or more indexes for the collection by inserting into the
     * "system.indexes" collection (MongoDB <2.6).
     *
     * @param Server $server
     */
    private function executeLegacy(Server $server)
    {
        $bulk=new Bulk(['ordered'=>true]);

        foreach ($this->indexes as $index) {
            $bulk->insert($index);
        }

        $server->executeBulkWrite($this->databaseName.'.system.indexes', $bulk, new WriteConcern(1));
    }
}
