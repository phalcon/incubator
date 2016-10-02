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
  | Authors: Richard Laffers <rlaffers@gmail.com>                          |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Logger\Adapter;

use Phalcon\Logger\Formatter\Firelogger as FireloggerFormatter;
use Phalcon\Logger\Adapter as LoggerAdapter;
use Phalcon\Logger\AdapterInterface;

/**
 * Phalcon\Logger\Adapter\Firelogger
 * Sends messages to the Firelogger extension in Firefox.
 *
 * @link http://firelogger.binaryage.com/
 */
class Firelogger extends LoggerAdapter implements AdapterInterface
{
    /**
     * Name
     *
     * @var string
     */
    protected $name = 'phalcon';

    /**
     * Adapter options
     * In addition to default options provided by Phalcon\Adapter, you may specify the following:
     * (string) password      Holds password which the client should send to turn Firelogger on.
     *                        Leave empty if no password authentication is needed.
     * (boolean) checkVersion Turn client version checks on / off.
     * (boolean) traceable    If TRUE, backtraces will be added to all logs.
     *
     * @var array
     */
    protected $options = [];

    /**
     * @var boolean
     */
    protected $enabled;

    /**
     * Holds current Firelogger server version.
     *
     * @var string
     */
    protected $serverVersion = '0.1';

    /**
     * Holds detected Firelogger client version.
     *
     * @var string
     */
    protected $clientVersion;

    /**
     * Recommended Firelogger client version.
     *
     * @var string
     */
    protected $recommendedClientVersion = '1.3';

    /**
     * Storage for holding all messages until they are ready to be shipped to client.
     *
     * @var array
     */
    protected $logs = [];

    /**
     * Denotes if there is a transaction started.
     *
     * @var boolean
     */
    protected $isTransaction = false;

    /**
     * Class constructor.
     *
     * @param string $name
     * @param array  $options
     */
    public function __construct($name = 'phalcon', array $options = [])
    {
        $defaults = [
            'password'     => null,
            'checkVersion' => true,
            'traceable'    => false,
            'triggerError' => true
        ];

        if ($name) {
            $this->name = $name;
        }

        $this->options = array_merge($defaults, $options);
        $this->enabled = $this->checkPassword() && $this->checkVersion();

        register_shutdown_function([$this, 'commit']);
    }

    /**
     * Setter for name
     *
     * @param  string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return \Phalcon\Logger\FormatterInterface
     */
    public function getFormatter()
    {
        if (!$this->_formatter) {
            $this->_formatter = new FireloggerFormatter($this->name);
        }

        return $this->_formatter;
    }

    /**
     * Writes the log to the headers.
     *
     * @param mixed $message Stuff to log. Can be of any type castable into a string (i.e. anything except for
     *                       objects without __toString() implementation).
     * @param integer $type
     * @param integer $time
     * @param array   $context
     */
    public function logInternal($message, $type, $time, $context = [])
    {
        if (!$this->enabled) {
            return;
        }
        $trace = null;
        if ($this->options['traceable']) {
            $trace = debug_backtrace();
        }
        $log = $this->getFormatter()->format($message, $type, $time, $context, $trace, count($this->logs));
        $this->logs[] = $log;

        // flush if this is not transaction
        if (!$this->isTransaction) {
            $this->flush();
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function begin()
    {
        // flush the previous transaction if there is any
        $this->commit();
        // start a new transaction
        $this->isTransaction = true;
    }

    /**
     * {@inheritdoc}
     * Encodes all collected messages into HTTP headers. This method is registered as a shutdown handler,
     * so transactions will get committed even if you forget to commit them yourself.
     */
    public function commit()
    {
        if (!$this->isTransaction || empty($this->logs)) {
            $this->isTransaction = false;

            return;
        }

        $this->flush();
        $this->isTransaction = false;
    }

    /**
     * {@inheritdoc}
     */
    protected function flush()
    {
        if (headers_sent($file, $line) && $this->options['triggerError']) {
            trigger_error(
                "Cannot send FireLogger headers after output has been sent" .
                ($file ? " (output started at $file:$line)." : "."),
                \E_USER_WARNING
            );

            return;
        }

        $logs = $this->logs;

        // final encoding
        $id = dechex(mt_rand(0, 0xFFFF)) . dechex(mt_rand(0, 0xFFFF)); // mt_rand is not working with 0xFFFFFFFF
        $json = json_encode(['logs' => $logs]);
        $res = str_split(base64_encode($json), 76); // RFC 2045

        foreach ($res as $k => $v) {
            header("FireLogger-$id-$k:$v");
        }

        $this->logs = [];
    }

    /**
     * Checks client provided password to see if we should disable/enable the firelogger.
     * Disables/enables the firelogger appropriately.
     *
     * @return boolean
     */
    protected function checkPassword()
    {
        if (!isset($this->options['password'])) {
            $this->enabled = true;

            return true;
        }

        if (isset($_SERVER['HTTP_X_FIRELOGGERAUTH'])) {
            $clientHash = $_SERVER['HTTP_X_FIRELOGGERAUTH'];
            $serverHash = md5("#FireLoggerPassword#" . $this->options['password'] . "#");
            if ($clientHash !== $serverHash) { // passwords do not match
                $this->enabled = false;

                if ($this->options['triggerError']) {
                    trigger_error(
                        "FireLogger passwords do not match. Have you specified correct password FireLogger extension?"
                    );
                }
            } else {
                $this->enabled = true;
            }
        } else {
            $this->enabled = false;
        }

        return $this->enabled;
    }

    /**
     * Checks client version vs recommended version and logs a message if there is a mismatch. Does not
     * disable firelogger even if there is version mismatch.
     *
     * @return boolean
     */
    private function checkVersion()
    {
        if (!$this->options['checkVersion']) {
            $this->enabled = true;
            return true;
        }

        if (!isset($_SERVER['HTTP_X_FIRELOGGER'])) {
            $this->enabled = false;
            return false;
        }

        $this->clientVersion = $_SERVER['HTTP_X_FIRELOGGER'];
        if ($this->clientVersion != $this->recommendedClientVersion) {
            error_log(
                'FireLogger for PHP (v' . $this->serverVersion .
                ') works best with FireLogger extension of version ' . $this->recommendedClientVersion .
                '. You are currently using extension v' . $this->clientVersion .
                '. Please install matching versions from http://firelogger.binaryage.com/ and ' .
                'https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Logger'
            );

            $this->enabled = false;
            return false;
        }

        $this->enabled = true;
        return true;
    }
}
