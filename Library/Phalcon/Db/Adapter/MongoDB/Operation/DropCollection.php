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
use MongoDB\Driver\Exception\RuntimeException;
use Phalcon\Db\Adapter\MongoDB\Exception\InvalidArgumentException;

/**
 * Operation for the drop command.
 *
 * @package Phalcon\Db\Adapter\MongoDB\Operation
 */
class DropCollection implements Executable
{
    private static $errorMessageNamespaceNotFound='ns not found';

    private $databaseName;
    private $collectionName;
    private $options;

    /**
     * Constructs a drop command.
     *
     * Supported options:
     *
     *  * typeMap (array): Type map for BSON deserialization. This will be used
     *    for the returned command result document.
     *
     * @param string $databaseName Database name
     * @param string $collectionName Collection name
     * @param array  $options Command options
     */
    public function __construct($databaseName, $collectionName, array $options = [])
    {
        if (isset($options['typeMap'])&&!is_array($options['typeMap'])) {
            throw InvalidArgumentException::invalidType('"typeMap" option', $options['typeMap'], 'array');
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
     * @return array|object Command result document
     */
    public function execute(Server $server)
    {
        try {
            $cursor=$server->executeCommand($this->databaseName, new Command(['drop'=>$this->collectionName]));
        } catch (RuntimeException $e) {
            /* The server may return an error if the collection does not exist.
             * Check for an error message (unfortunately, there isn't a code)
             * and NOP instead of throwing.
             */
            if ($e->getMessage()===self::$errorMessageNamespaceNotFound) {
                return (object)['ok'=>0,'errmsg'=>self::$errorMessageNamespaceNotFound];
            }

            throw $e;
        }

        if (isset($this->options['typeMap'])) {
            $cursor->setTypeMap($this->options['typeMap']);
        }

        return current($cursor->toArray());
    }
}
