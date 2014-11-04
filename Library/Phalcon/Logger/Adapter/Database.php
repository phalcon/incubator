<?php
/**
 * Database adapter for the Phalcon\Logger functionality
 *
 */

namespace Phalcon\Logger\Adapter;


use Phalcon\Db\Adapter\Pdo;
use Phalcon\Db\Column;
use Phalcon\Logger\Adapter;
use Phalcon\Logger\AdapterInterface;
use Phalcon\Logger\Exception as LoggerException;
use Phalcon\Logger;

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

    /**
     * Constructor
     *
     * @param string $table
     * @param \Phalcon\Db\Adapter $db
     * @throws \Phalcon\Logger\Exception
     */
    public function __construct($table, \Phalcon\Db\Adapter $db = null)
    {
        if (!$table) {
            throw new LoggerException('You must supply a table name to which the log records will be written');
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
            array($this->getTypeString($type),
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
    protected function getTypeString($type)
    {
        switch ($type) {
            case Logger::EMERGENCE:
            case Logger::EMERGENCY:
                return 'emergency';
            case Logger::CRITICAL:
                return 'critical';
            case Logger::ALERT:
                return 'alert';
            case Logger::ERROR:
                return 'error';
            case Logger::WARNING:
                return 'warning';
            case Logger::NOTICE:
                return 'notice';
            case Logger::INFO:
                return 'info';
            case Logger::CUSTOM:
                // use CUSTOM for success status
                return 'success';
            case Logger::DEBUG:
            case Logger::SPECIAL:
            default:
                return 'debug';
        }
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
     * @return \Phalcon\Logger\Adapter
     */
    public function success($message, $context = null)
    {
        return $this->log(Logger::CUSTOM, $message, $context);
    }

    /**
     * Begin transaction
     *
     * @return Adapter|void
     */
    public function begin()
    {
        $this->db->begin();
    }

    /**
     * Commit transaction
     *
     * @return Adapter|void
     */
    public function commit()
    {
        $this->db->commit();
    }

    /**
     * Rollback transaction
     * (happens automatically if commit never reached)
     *
     * @return Adapter|void
     */
    public function rollback()
    {
        $this->db->rollback();
    }
}
