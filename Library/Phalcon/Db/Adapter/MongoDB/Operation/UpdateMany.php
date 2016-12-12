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
use Phalcon\Db\Adapter\MongoDB\UpdateResult;
use MongoDB\Driver\Server;
use Phalcon\Db\Adapter\MongoDB\Exception\InvalidArgumentException;

/**
 * Operation for updating multiple documents with the update command.
 *
 * @package Phalcon\Db\Adapter\MongoDB\Operation
 */
class UpdateMany implements Executable
{
    private $update;

    /**
     * Constructs an update command.
     *
     * Supported options:
     *
     *  * bypassDocumentValidation (boolean): If true, allows the write to opt
     *    out of document level validation.
     *
     *  * upsert (boolean): When true, a new document is created if no document
     *    matches the query. The default is false.
     *
     *  * writeConcern (MongoDB\Driver\WriteConcern): Write concern.
     *
     * @param string       $databaseName Database name
     * @param string       $collectionName Collection name
     * @param array|object $filter Query by which to filter documents
     * @param array|object $update Update to apply to the matched documents
     * @param array        $options Command options
     *
     * @throws InvalidArgumentException
     */
    public function __construct($databaseName, $collectionName, $filter, $update, array $options = [])
    {
        if (!is_array($update)&&!is_object($update)) {
            throw InvalidArgumentException::invalidType('$update', $update, 'array or object');
        }

        if (!Functions::isFirstKeyOperator($update)) {
            throw new InvalidArgumentException('First key in $update argument is not an update operator');
        }

        $this->update=new Update($databaseName, $collectionName, $filter, $update, ['multi'=>true]+$options);
    }

    /**
     * Execute the operation.
     *
     * @see Executable::execute()
     *
     * @param Server $server
     *
     * @return UpdateResult
     */
    public function execute(Server $server)
    {
        return $this->update->execute($server);
    }
}
