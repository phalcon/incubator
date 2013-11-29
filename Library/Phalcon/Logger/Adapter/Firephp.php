<?php

namespace Phalcon\Logger\Adapter;

use Phalcon\Logger\Exception,
	Phalcon\Logger\Formatter\Firephp as FirephpFormatter;

/**
 * Phalcon\Logger\Adapter\Firephp
 *
 * Adapter to send logs to Firebug using Firephp
 */
class Firephp extends \Phalcon\Logger\Adapter implements \Phalcon\Logger\AdapterInterface
{

	/**
	 * Name
	 */
	protected $_name;

	/**
	 * Adapter options
	 */
	protected $_options;

	/**
	 * Check if the initialization headers have been sent
	 */
	protected $_initialized;

	/**
	 * Current index in the main structure
	 */
	protected $_index = 1;

	/**
	 * Phalcon\Logger\Adapter\Database constructor
	 *
	 * @param string $name
	 * @param array $options
	 */
	public function __construct($name, $options = array())
	{
		$this->_name = $name;
		$this->_options = $options;
	}

	/**
	 * Returns the internal formatter
	 *
	 * @return Phalcon\Logger\Formatter\Line
	 */
	public function getFormatter()
	{
		if (!$this->_formatter) {
			$this->_formatter = new FirephpFormatter();
		}

		return $this->_formatter;
	}

	/**
	 * Writes the log to the file itself
	 *
	 * @param string $message
	 * @param int $type
	 * @param int $time
	 */
	public function logInternal($message, $type, $time)
	{

		if (!$this->_initialized) {
			header('X-Wf-Protocol-1: http://meta.wildfirehq.org/Protocol/JsonStream/0.2');
			header('X-Wf-1-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3');
			header('X-Wf-1-Structure-1: http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1');
			$this->_initialized = true;
		}

		$log = $this->getFormatter()->format($message, $type, $time);

		header('X-Wf-1-1-1-' . $this->_index . ': ' . $log);
		$this->_index++;
	}

	/**
	 * Closes the logger
	 *
	 * @return boolean
	 */
	public function close()
	{
	}

}
