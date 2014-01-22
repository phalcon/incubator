<?php

namespace Phalcon\Logger\Adapter;

use Phalcon\Logger\Exception;
use Phalcon\Logger\Formatter\Firelogger as FireloggerFormatter;

/**
 * Phalcon\Logger\Adapter\Firelogger
 * Sends messages to the Firelogger extension in Firefox.
 *
 * @link    http://firelogger.binaryage.com/
 * @version 0.1
 * @author  Richard Laffers <rlaffers@gmail.com>
 * @license The BSD 3-Clause License {@link http://opensource.org/licenses/BSD-3-Clause}
 */
class Firelogger extends \Phalcon\Logger\Adapter implements \Phalcon\Logger\AdapterInterface
{

	/**
	 * Name
	 */
	protected $_name;

	/**
	 * Adapter options
	 * In addition to default options provided by Phalcon\Adapter, you may specify the following:
	 * (string)     password    Holds password which the client should send to turn Firelogger on. Leave empty if no password
	 *                          authentication is needed.
	 * (boolean)    checkVersion Turn client version checks on / off.
	 * (boolean)    traceable   If TRUE, backtraces will be added to all logs.
	 */
	protected $_options;

	/**
	 * _enabled
	 *
	 * @var bool
	 * @access protected
	 */
	protected $_enabled;

	/**
	 * _serverVersion
	 * Holds current Firelogger server version.
	 *
	 * @var string
	 * @access protected
	 */
	protected $_serverVersion = '0.1';

	/**
	 * _clientVersion
	 * Holds detected Firelogger client version.
	 *
	 * @var string
	 * @access protected
	 */
	protected $_clientVersion;

	/**
	 * _recommendedClientVersion
	 * Recommended Firelogger client version.
	 *
	 * @var mixed
	 * @access protected
	 */
	protected $_recommendedClientVersion = '1.3';

	/**
	 * _logs
	 * Storage for holding all messages until they are ready to be shipped to client.
	 *
	 * @var array
	 * @access protected
	 */
	protected $_logs = array();

	/**
	 * _isTransaction
	 * Denotes if there is a transaction started.
	 *
	 * @var bool
	 * @access protected
	 */
	protected $_isTransaction = false;


	/**
	 * Phalcon\Logger\Adapter\Firelogger constructor
	 *
	 * @param string $name
	 * @param array  $options
	 */
	public function __construct($name = 'phalcon', $options = array())
	{
		$defaults = array(
			'password'     => null,
			'checkVersion' => true,
			'traceable'    => false,
		);
		$this->_name = $name;
		$this->_options = array_merge($defaults, $options);
		$this->_enabled = $this->checkPassword();
		$this->checkVersion();
		register_shutdown_function(array($this, 'commit'));
	}

	/**
	 * Setter for _name
	 *
	 * @return $this
	 */
	public function setName($name)
	{
		$this->_name = $name;
		return $this;
	}


	/**
	 * Returns the internal formatter
	 *
	 * @return Phalcon\Logger\Formatter\Line
	 */
	public function getFormatter()
	{
		if (!$this->_formatter) {
			$this->_formatter = new FireloggerFormatter($this->_name);
		}
		return $this->_formatter;
	}

	/**
	 * Writes the log to the headers.
	 *
	 * @param mixed $message Stuff to log. Can be of any type castable into a string (i.e. anything except for objects without __toString() implementation).
	 * @param int   $type
	 * @param int   $time
	 * @param array $context
	 */
	protected function logInternal($message, $type, $time, $context)
	{
		if (!$this->_enabled) {
			return;
		}
		$trace = null;
		if ($this->_options['traceable']) {
			$trace = debug_backtrace();
		}
		$log = $this->getFormatter()->format($message, $type, $time, $context, $trace, count($this->_logs));
		$this->_logs[] = $log;

		// flush if this is not transaction
		if (!$this->_isTransaction) {
			$this->flush();
		}
	}

	/**
	 * Closes the logger
	 *
	 * @return boolean
	 */
	public function close()
	{
	}

	/**
	 * begin
	 *
	 * @see    Phalcon\Logger\Adapter::begin()
	 * @access public
	 * @return void
	 */
	public function begin()
	{
		// flush the previous transaction if there is any
		$this->commit();
		// start a new transaction
		$this->_isTransaction = true;
	}

	/**
	 * flush
	 *
	 * @return void
	 **/
	private function flush()
	{
		if (headers_sent($file, $line)) {
			trigger_error("Cannot send FireLogger headers after output has been sent" . ($file ? " (output started at {$file}:{$line})." : "."), \E_USER_WARNING);
			return;
		}
		$logs = $this->_logs;

		// final encoding
		$id = dechex(mt_rand(0, 0xFFFF)) . dechex(mt_rand(0, 0xFFFF)); // mt_rand is not working with 0xFFFFFFFF
		$json = json_encode(array('logs' => $logs));
		$res = str_split(base64_encode($json), 76); // RFC 2045

		foreach ($res as $k => $v) {
			header("FireLogger-$id-$k:$v");
		}

		$this->_logs = array();
	}

	/**
	 * commit
	 * Encodes all collected messages into HTTP headers. This method is registered as a shutdown handler,
	 * so transactions will get committed even if you forget to commit them yourself.
	 *
	 * @see    Phalcon\Logger\Adapter::commit()
	 * @access public
	 * @return void
	 */
	public function commit()
	{
		if (!$this->_isTransaction || empty($this->_logs)) {
			$this->_isTransaction = false;
			return;
		}
		$this->flush();
		$this->_isTransaction = false;
	}

	/**
	 * checkPassword
	 * Checks client provided password to see if we should disable/enable the firelogger.
	 * Disables/enables the firelogger appropriately.
	 *
	 * @access private
	 * @return bool
	 */
	private function checkPassword()
	{
		if (!isset($this->_options['password'])) {
			$this->_enabled = true;
			return true;
		}
		if (isset($_SERVER['HTTP_X_FIRELOGGERAUTH'])) {
			$clientHash = $_SERVER['HTTP_X_FIRELOGGERAUTH'];
			$serverHash = md5("#FireLoggerPassword#" . $this->_options['password'] . "#");
			if ($clientHash !== $serverHash) { // passwords do not match
				$this->_enabled = false;
				trigger_error("FireLogger passwords do not match. Have you specified correct password FireLogger extension?");
			} else {
				$this->_enabled = true;
			}
		} else {
			$this->_enabled = false;
		}
		return $this->_enabled;
	}


	/**
	 * checkVersion
	 * Checks client version vs recommended version and logs a message if there is a mismatch. Does not
	 * disable firelogger even if there is version mismatch.
	 *
	 * @return bool
	 **/
	private function checkVersion()
	{
		if (!$this->_options['checkVersion']) {
			return true;
		}
		if (!isset($_SERVER['HTTP_X_FIRELOGGER'])) {
			return false;
		} else {
			$this->_clientVersion = $_SERVER['HTTP_X_FIRELOGGER'];
			if ($this->_clientVersion != $this->_recommendedClientVersion) {
				error_log("FireLogger for PHP (v" . $this->_serverVersion . ") works best with FireLogger extension of version " . $this->_recommendedClientVersion . ". You are currently using extension v" . $this->_clientVersion . ". Please install matching versions from http://firelogger.binaryage.com/ and https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Logger");
				return false;
			}
			return true;
		}


	}


}
