<?php
namespace Phalcon\Logger\Adapter;

use Phalcon\Logger\Exception;
use Phalcon\Logger\Formatter\Line as LineFormatter;
use Phalcon\Logger\Adapter as LoggerAdapter;
use Phalcon\Logger\AdapterInterface;

/**
 * Phalcon\Logger\Adapter\Database
 * Adapter to store logs in a database table
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

        if (!isset($options['table'])) {
            throw new Exception("Parameter 'table' is required");
        }

        if ($name) {
            $this->name = $name;
        }

        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     *
     * @return \Phalcon\Logger\FormatterInterface
     */
    public function getFormatter()
    {
        if (!is_object($this->_formatter)) {
            $this->_formatter = new LineFormatter();
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
     */
    public function logInternal($message, $type, $time, $context = [])
    {
        return $this->options['db']->execute(
            'INSERT INTO ' . $this->options['table'] . ' VALUES (null, ?, ?, ?, ?)',
            [$this->name, $type, $message, $time]
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

        return true;
    }
}
