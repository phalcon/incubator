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

use Phalcon\Db\Adapter\MongoDB\DeleteResult;
use MongoDB\Driver\Server;
use Phalcon\Db\Adapter\MongoDB\Exception\InvalidArgumentException;

/**
 * Operation for deleting a single document with the delete command.
 *
 * @package Phalcon\Db\Adapter\MongoDB\Operation
 */
class DeleteOne implements Executable
{
    private $delete;

    /**
     * Constructs a delete command.
     *
     * Supported options:
     *
     *  * writeConcern (MongoDB\Driver\WriteConcern): Write concern.
     *
     * @param string       $databaseName Database name
     * @param string       $collectionName Collection name
     * @param array|object $filter Query by which to delete documents
     * @param array        $options Command options
     *
     * @throws InvalidArgumentException
     */
    public function __construct($databaseName, $collectionName, $filter, array $options = [])
    {
        $this->delete=new Delete($databaseName, $collectionName, $filter, 1, $options);
    }

    /**
     * Execute the operation.
     *
     * @see Executable::execute()
     *
     * @param Server $server
     *
     * @return DeleteResult
     */
    public function execute(Server $server)
    {
        return $this->delete->execute($server);
    }
}
