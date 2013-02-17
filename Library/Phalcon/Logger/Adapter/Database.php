<?php

namespace Phalcon\Logger\Adapter;

use \Phalcon\Logger\Exception;

/**
 * Phalcon\Logger\Adapter\Database
 *
 * Adapter to store logs in a database table
 */
class Database extends \Phalcon\Logger\Adapter implements \Phalcon\Logger\AdapterInterface {

	/**
	 * Name
	 */
	protected $_name;

	/**
	 * Adapter options
	 */
	protected $_options;

	/**
	 * Phalcon\Logger\Adapter\Database constructor
	 *
	 * @param string $name
	 * @param array $options
	 */
	public function __construct($name, $options=array()){

		if (!isset($options['db'])) {
			throw new Exception("Parameter 'db' is required");
		}

		if (!isset($options['table'])) {
			throw new Exception("Parameter 'table' is required");
		}

		$this->_name = $name;
		$this->_options = $options;
	}

	/**
	 * Returns the internal formatter
	 *
	 * @return Phalcon\Logger\Formatter\Line
	 */
	public function getFormatter(){

	}

	/**
	 * Writes the log to the file itself
	 *
	 * @param string $message
	 * @param int $type
	 * @param int $time
	 */
	public function logInternal($message, $type, $time){
		return $this->_options['db']->execute("INSERT INTO " . $this->_options['table'] . " VALUES (null, ?, ?, ?, ?)", array(
			$this->_name,
			$type,
			$message,
			$time
		));
	}

	/**
 	 * Closes the logger
 	 *
 	 * @return boolean
 	 */
	public function close(){
		$this->_options['db']->close();
	}

}
