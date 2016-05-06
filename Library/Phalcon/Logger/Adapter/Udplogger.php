<?php
namespace Phalcon\Logger\Adapter;

use Phalcon\Logger\Exception;
use Phalcon\Logger\Formatter\Line as LineFormatter;
use Phalcon\Logger\Adapter as LoggerAdapter;
use Phalcon\Logger\AdapterInterface;

/**
 * Phalcon\Logger\Adapter\Udplogger
 * Sends messages using UDP protocol to external server
 *
 * @version 0.1
 * @author  Vitaliy Panait <panait.vi@gmail.com>
 * @license The BSD 3-Clause License {@link http://opensource.org/licenses/BSD-3-Clause}
 */
class Udplogger extends LoggerAdapter implements AdapterInterface
{
    /**
     * Name
     *
     * @var string
     */
    protected $name = 'phalcon';

    /**
     * Adapter options
     *
     * @var array
     */
    protected $options = [];

    /**
     * @var resource
     */
    protected $socket;

    /**
     * Storage for holding all messages until they are ready to be sent to server.
     *
     * @var array
     */
    protected $logs = [];

    /**
     * Flag for the transaction
     *
     * @var boolean
     */
    protected $isTransaction = false;

    /**
     * Class constructor.
     *
     * @param string $name
     * @param array  $options
     * @throws \Phalcon\Logger\Exception
     */
    public function __construct($name = 'phalcon', array $options = [])
    {
        if (!isset($options['url'])) {
            throw new Exception("Parameter 'url' is required");
        }

        if (!isset($options['port'])) {
            throw new Exception("Parameter 'port' is required");
        }

        if ($name) {
            $this->name = $name;
        }

        $this->options = $options;

        register_shutdown_function([$this, 'commit']);
        register_shutdown_function([$this, 'close']);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Phalcon\Logger\FormatterInterface
     */
    public function getFormatter()
    {
        if (!$this->_formatter) {
            $this->_formatter = new LineFormatter();
        }

        return $this->_formatter;
    }

    /**
     * Writes the log.
     *
     * @param string  $message
     * @param integer $type
     * @param integer $time
     * @param array   $context
     */
    public function logInternal($message, $type, $time, $context = [])
    {
        $this->logs[] = [
            'name'    => $this->name,
            'message' => $message,
            'type '   => $type,
            'time'    => $time
        ];

        if (!$this->isTransaction) {
            $this->send();
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean
     */
    public function close()
    {
        if ($this->socket !== null) {
            socket_close($this->socket);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function begin()
    {
        $this->commit();
        $this->isTransaction = true;
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        if (!$this->isTransaction || empty($this->logs)) {
            $this->isTransaction = false;

            return;
        }

        $this->send();
        $this->isTransaction = false;
    }

    /**
     * {@inheritdoc}
     */
    protected function send()
    {
        if (empty($this->logs)) {
            return;
        }

        $message = json_encode($this->logs);

        if ($this->socket === null) {
            $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        }

        socket_sendto($this->socket, $message, strlen($message), 0, $this->options['url'], $this->options['port']);

        $this->logs = [];
    }
}
