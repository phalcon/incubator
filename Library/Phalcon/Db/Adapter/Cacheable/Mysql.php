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

namespace Phalcon\Adapter\Cacheable;

use Phalcon\Db\Result\Serializable;

/**
 * Phalcon\Adapter\Cacheable\Mysql
 *
 * Every query executed via this adapter is automatically cached
 */
class Mysql extends \Phalcon\Db\Adapter\Pdo\Mysql
{

	/**
	 * The constructor avoids the automatic connection
	 *
	 * @param array $descriptor
	 */
	public function __construct($descriptor)
	{
		$this->_descriptor = $descriptor;
		$this->_dialect = new \Phalcon\Db\Dialect\Mysql();
	}

	/**
	 * Sets a handler to cache the data
	 *
	 * @param \Phalcon\Cache\BackendInterface $cache
	 */
	public function setCache($cache)
	{
		$this->_cache = $cache;
	}

	/**
	 * Checks if exist an active connection, if not, makes a connection
	 */
	protected function _connect()
	{
		if (!$this->_pdo) {
			$this->connect();
		}
	}

	/**
	 * The queries executed are stored in the cache
	 *
	 * @param string $sqlStatement
	 * @param array $bindParams
	 * @param array $bindTypes
	 * @return \Phalcon\Db\Result\Serializable
	 */
	public function query($sqlStatement, $bindParams=null, $bindTypes=null)
	{

		/**
		 * The key is the full sql statement + its parameters
		 */
		if (is_array($bindParams)) {
			$key = \Phalcon\Kernel::preComputeHashKey($sqlStatement.'//'.join('|', $bindParams));
		} else {
			$key = \Phalcon\Kernel::preComputeHashKey($sqlStatement);
		}

		/**
		 * Check if the result is already cached
		 */
		if ($this->_cache->exists($key)) {
			$value = $this->_cache->get($key);
			if (!is_null($value)) {
				return $value;
			}
		}

		$this->_connect();

		/**
		 * Executes the queries
		 */
		$data = parent::query($sqlStatement, $bindParams, $bindTypes);

		if (is_object($data)) {
			$result = new Serializable($data);
			$this->_cache->save($key, $result);
			return $result;
		}

		$this->_cache->save($key, $data);
		return false;
	}

	/**
	 * Executes the SQL statement without caching
	 *
	 * @param string $sqlStatement
	 * @param array $bindParams
	 * @param array $bindTypes
	 * @return boolean
	 */
	public function execute($sqlStatement, $bindParams=null, $bindTypes=null)
	{
		$this->_connect();
		return parent::execute($sqlStatement, $bindParams, $bindTypes);
	}

	/**
	 * Checks if a table exists
	 *
	 * @param string $tableName
	 * @param string $schemaName
	 * @return boolean
	 */
	public function tableExists($tableName, $schemaName=null)
	{
		$this->_connect();
		return parent::tableExists($tableName, $schemaName);
	}

}