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
  | Authors: Andres Gutierrez <andres@phalconphp.com>                      |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Logger\Adapter;

use Phalcon\Db\Column;
use Phalcon\Logger\Exception;
use Phalcon\Logger\Formatter\Line as LineFormatter;
use Phalcon\Logger\Adapter as LoggerAdapter;
use Phalcon\Logger\AdapterInterface;
use Phalcon\Db\AdapterInterface as DbAdapterInterface;

/**
 * Database Logger
 *
 * Adapter to store logs in a database table
 *
 * @package Phalcon\Logger\Adapter
 */
class Database extends LoggerAdapter implements AdapterInterface
{
    /**
     * Name
     * @var string
     */
    protected $name = 'phalcon';

    /**
     * Adapter options
     * @var array
     */
    protected $options = [];

    /**
     * @var \Phalcon\Db\AdapterInterface
     */
    protected $db;

    /**
     * Class constructor.
     *
     * @param  string $name
     * @param  array  $options
     * @throws \Phalcon\Logger\Exception
     */
    public function __construct($name = 'phalcon', array $options = [])
    {
        if (!isset($options['db'])) {
            throw new Exception("Parameter 'db' is required");
        }

        if (!$options['db'] instanceof DbAdapterInterface) {
            throw new Exception(
                "Parameter 'db' must be object and implement AdapterInterface"
            );
        }

        if (!isset($options['table'])) {
            throw new Exception("Parameter 'table' is required");
        }

        $this->db = $options['db'];

        if ($name) {
            $this->name = $name;
        }

        $this->options = $options;
    }

    /**
     * Sets database connection
     *
     * @param AdapterInterface $db
     * @return $this
     */
    public function setDb(AdapterInterface $db)
    {
        $this->db = $db;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return \Phalcon\Logger\FormatterInterface
     */
    public function getFormatter()
    {
        if (!is_object($this->_formatter)) {
            $this->_formatter = new LineFormatter('%message%');
        }

        return $this->_formatter;
    }

    /**
     * Writes the log to the file itself
     *
     * @param string  $message
     * @param integer $type
     * @param integer $time
     * @param array   $context
     * @return bool
     */
    public function logInternal($message, $type, $time, $context = [])
    {
        return $this->db->execute(
            'INSERT INTO ' . $this->options['table'] . ' VALUES (null, ?, ?, ?, ?)',
            [
                $this->name,
                $type,
                $this->getFormatter()->format($message, $type, $time, $context),
                $time,
            ],
            [
                Column::BIND_PARAM_STR,
                Column::BIND_PARAM_INT,
                Column::BIND_PARAM_STR,
                Column::BIND_PARAM_INT,
            ]
        );
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean
     */
    public function close()
    {
        if ($this->db->isUnderTransaction()) {
            $this->db->commit();
        }

        $this->db->close();

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function begin()
    {
        $this->db->begin();

        return $this;
    }

    /**
     * Commit transaction
     *
     * @return $this
     */
    public function commit()
    {
        $this->db->commit();

        return $this;
    }

    /**
     * Rollback transaction
     * (happens automatically if commit never reached)
     *
     * @return $this
     */
    public function rollback()
    {
        $this->db->rollback();

        return $this;
    }
}
