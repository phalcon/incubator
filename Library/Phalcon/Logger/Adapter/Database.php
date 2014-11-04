<?php
/**
 * Database adapter for the Phalcon\Logger functionality
 *
 */

namespace Phalcon\Logger\Adapter;


use Phalcon\Db\Adapter\Pdo;
use Phalcon\Db\Column;
use Phalcon\DI;
use Phalcon\Logger;
use Phalcon\Logger\Adapter;
use Phalcon\Logger\AdapterInterface;

class Database extends Adapter implements AdapterInterface
{
    /**
     * Database connection
     *
     * @var \Phalcon\Db\Adapter
     */
    protected $db;

    /**
     * Database table to which log records are stored
     *
     * @var string
     */
    protected $table;

    public function __construct($table, \Phalcon\Db\Adapter $db = null)
    {
        if (!$table) {
            throw new \InvalidArgumentException('You must supply a table name to which the log records will be written');
        }

        $this->table = $table;

        $this->db = $db;
    }

    /**
     * Inserts a log record into the database table
     *
     * @param string $message
     * @param int $type
     * @param int $time
     * @param null|array $context
     * @return bool
     */
    public function logInternal($message, $type, $time, $context = null)
    {
        $context = $this->getContextAsString($context);

        return $this->db->execute('INSERT INTO ' . $this->table . ' VALUES(NULL, ?, ?, ?, ?, ?)',
            array($this->getTypeName($type),
                  $type,
                  $message,
                  $time,
                  $context),
            array(Column::BIND_PARAM_STR,
                  Column::BIND_PARAM_INT,
                  Column::BIND_PARAM_STR,
                  Column::BIND_PARAM_INT,
                  is_null($context) ? Column::BIND_PARAM_NULL : Column::BIND_PARAM_STR)
        );
    }

    /**
     * Returns context array as a string
     *
     * Delimiter ";" between context messages
     *
     * @param null|array $context
     * @return string
     */
    protected function getContextAsString($context = null)
    {
        if (null !== $context) {
            $info = '';

            foreach ($context as $name => $moreInfo) {
                $info .= $name . ': ' . $moreInfo . '; ';
            }

            $context = rtrim($info);
        }

        return $context;
    }

    /**
     * Returns string representation of a type ('error', 'notice', etc.)
     *
     * @param int $type
     * @return string
     */
    protected function getTypeName($type)
    {
        // Get the constants which are defined in the Logger class
        $oClass = new \ReflectionClass('Phalcon\Logger');
        $constants = $oClass->getConstants();

        // Remove EMERGENCE as it is poor English
        if (array_key_exists('EMERGENCE', $constants)) {
            unset($constants['EMERGENCE']);
        }

        // Use CUSTOM constant as SUCCESS
        if (array_key_exists('CUSTOM', $constants)) {
            $constants['SUCCESS'] = $constants['CUSTOM'];
            unset($constants['CUSTOM']);
        }

        // Flip the array to get keys as (type) numbers
        $constants = array_flip($constants);

        // return type as lowercase string (error, notice, etc.)
        return strtolower($constants[$type]);
    }

    public function getFormatter()
    {

    }

    /**
     * Closes database connection
     *
     * @return bool|void
     */
    public function close()
    {
        $this->db->close();
    }

    /**
     * Sets database connection
     *
     * @param \Phalcon\Db\Adapter $db
     * @return $this
     */
    public function setDb(\Phalcon\Db\Adapter $db)
    {
        $this->db = $db;

        return $this;
    }

    /**
     * Shortcut to an Adapter::log function, type SUCCESS.
     * Gives success type (using Logger::CUSTOM)
     *
     * @param string $message
     * @param null|array $context
     * @return Adapter
     */
    public function success($message, $context = null)
    {
        return $this->log(Logger::CUSTOM, $message, $context);
    }
} 
