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

use Phalcon\Logger\Exception;
use Phalcon\Logger\Formatter\FormatterInterface;
use Phalcon\Logger\Item;
use Phalcon\Db\Adapter\AdapterInterface as DbAdapterInterface;
use Phalcon\Logger\Formatter\Line as LineFormatter;
use Phalcon\Db\Column;

/**
 * Phalcon\Logger\Adapter\Database
 *
 * Adapter to store logs in a database table
 */
class Database extends AbstractAdapter
{
    /**
     * Database connection
     *
     * @var DbAdapterInterface
     */
    protected $db;

    /**
     * Table name
     *
     * @var string
     */
    protected $table = "log";

    /**
     * Name
     * @var string
     */
    protected $name = 'phalcon';

    /**
     * @var \Phalcon\Logger\Formatter\AbstractFormatter
     */
    protected $_formatter;

    /**
     * Adapter options
     * @var array
     */
    protected $options = [];

    /**
     * Constructor. Accepts the name and some options
     */
    public function __construct(string $name = 'phalcon', array $options = [])
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
        $this->table = $options['table'];

        if ($name) {
            $this->name = $name;
        }

        $this->options = $options;
    }

    /**
     * Sets database connection
     *
     * @param DbAdapterInterface $db
     * @return $this
     */
    public function setDb(DbAdapterInterface $db)
    {
        $this->db = $db;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean
     */
    public function close(): bool
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
    public function begin(): AdapterInterface
    {
        $this->db->begin();

        return $this;
    }

    /**
     * Commit transaction
     *
     * @return $this
     */
    public function commit(): AdapterInterface
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
    public function rollback(): AdapterInterface
    {
        $this->db->rollback();

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return FormatterInterface
     */
    public function getFormatter(): FormatterInterface
    {
        if (!is_object($this->_formatter)) {
            $this->_formatter = new LineFormatter('%message%');
        }

        return $this->_formatter;
    }

    /**
     * Processes the message i.e. writes it to the file
     */
    public function process(Item $item)
    {
        return $this->db->execute(
            'INSERT INTO ' . $this->table . ' VALUES (null, ?, ?, ?, ?)',
            [
                $this->name,
                $item->getType(),
                $this->getFormatter()->format($item),
                $item->getTime(),
            ],
            [
                Column::BIND_PARAM_STR,
                Column::BIND_PARAM_INT,
                Column::BIND_PARAM_STR,
                Column::BIND_PARAM_INT,
            ]
        );
    }
}