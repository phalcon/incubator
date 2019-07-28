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
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\Server;
use Phalcon\Db\Adapter\MongoDB\Exception\InvalidArgumentException;

/**
 * Operation for executing a database command.
 *
 * @package Phalcon\Db\Adapter\MongoDB\Operation
 */
class DatabaseCommand implements Executable
{
    private $databaseName;
    private $command;
    private $options;

    /**
     * Constructs a command.
     *
     * Supported options:
     *
     *  * readPreference (MongoDB\Driver\ReadPreference): The read preference to
     *    use when executing the command. This may be used when issuing the
     *    command to a replica set or mongos node to ensure that the driver sets
     *    the wire protocol accordingly or adds the read preference to the
     *    command document, respectively.
     *
     *  * typeMap (array): Type map for BSON deserialization. This will be
     *    applied to the returned Cursor (it is not sent to the server).
     *
     * @param string       $databaseName Database name
     * @param array|object $command Command document
     * @param array        $options Options for command execution
     *
     * @throws InvalidArgumentException
     */
    public function __construct($databaseName, $command, array $options = [])
    {
        if (!is_array($command)&&!is_object($command)) {
            throw InvalidArgumentException::invalidType('$command', $command, 'array or object');
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

        $this->databaseName=(string)$databaseName;
        $this->command     =($command instanceof Command)?$command:new Command($command);
        $this->options     =$options;
    }

    /**
     * Execute the operation.
     *
     * @see Executable::execute()
     *
     * @param Server $server
     *
     * @return integer
     */
    public function execute(Server $server)
    {
        $readPreference=isset($this->options['readPreference'])?$this->options['readPreference']:null;

        $cursor=$server->executeCommand($this->databaseName, $this->command, $readPreference);

        if (isset($this->options['typeMap'])) {
            $cursor->setTypeMap($this->options['typeMap']);
        }

        return $cursor;
    }
}
