<?php

/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2012 Phalcon Team (http://www.phalconphp.com)       |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file docs/LICENSE.txt.                        |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Andres Gutierrez <andres@phalconphp.com>                      |
  |          Eduar Carvajal <eduar@phalconphp.com>                         |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Db\Result;

/**
 * Phalcon\Adapter\Result\Serializable
 * Fetches all the data in a result providing a serializable resultset
 */
class Serializable
{

	protected $_data = array();

	protected $_position = 0;

	/**
	 * The resultset is completely fetched

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