<?php

namespace Phalcon\Db\Result;

/**
 * Phalcon\Adapter\Result\Serializable
 *
 * Fetches all the data in a result providing a serializable resultset
 */
class Serializable
{

	protected $_data = array();

	protected $_position = 0;

	/**
	 * The resultset is completely fetched
	 *
	 */
	public function __construct($result)
	{
		$this->_data = $result->fetchAll();
	}

	/**
	 * Returns the number of rows in the internal array
	 *
	 * @return int
	 */
	public function numRows()
	{
		return count($this->_data);
	}

	/**
	 * Fetches a row in the resultset
	 *
	 * @return array|boolean
	 */
	public function fetch()
	{
		if (isset($this->_data[$this->_position])) {
			return $this->_data[$this->_position++];
		}
		return false;
	}

	/**
	 * Changes the fetch mode, this is not implemented yet
	 *
	 * @param int $fetchMode
	 */
	public function setFetchMode($fetchMode)
	{

	}

	/**
	 * Returns the full data in the resultset
	 *
	 * @return array
	 */
	public function fetchAll()
	{
		return $this->_data;
	}

	/**
	 * Resets the internal pointer
	 */
	public function __wakeup()
	{
		$this->_position = 0;
	}

}