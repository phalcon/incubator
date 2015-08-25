<?php
namespace Phalcon\Logger\Adapter;

use Phalcon\Logger\Exception;

/**
 * Phalcon\Logger\Adapter\Database
 * Adapter to store logs in a database table
 */
class Database extends \Phalcon\Logger\Adapter implements \Phalcon\Logger\AdapterInterface
{
    /**
     * Name
     */
    protected $name;

    /**
     * Adapter options
     */
    protected $options;

    /**
     * Class constructor.
     *
     * @param  string                    $name
     * @param  array                     $options
     * @throws \Phalcon\Logger\Exception
     */
    public function __construct($name, $options = array())
    {
        if (!isset($options['db'])) {
            throw new Exception("Parameter 'db' is required");
        }

        if (!isset($options['table'])) {
            throw new Exception("Parameter 'table' is required");
        }

        $this->name = $name;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     *
     * @return \Phalcon\Logger\Formatter\Line
     */
    public function getFormatter()
    {
    }

    /**
     * Writes the log to the file itself
     *
     * @param string  $message
     * @param integer $type
     * @param integer $time
     * @param array   $context
     */
    public function logInternal($message, $type, $time, $context = array())
    {
        return $this->options['db']->execute(
            'INSERT INTO ' . $this->options['table'] . ' VALUES (null, ?, ?, ?, ?)',
            array($this->name, $type, $message, $time)
        );
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean
     */
    public function close()
    {
        $this->options['db']->close();
    }
}
