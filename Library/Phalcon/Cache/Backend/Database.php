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

namespace Phalcon\Cache\Backend;

use Phalcon\Cache\Backend;
use Phalcon\Cache\BackendInterface;
use Phalcon\Cache\Exception;
use Phalcon\Db;

/**
 * Phalcon\Cache\Backend\Database
 * This backend uses a database as cache backend
 */
class Database extends Backend implements BackendInterface
{

	/**
	 * Phalcon\Cache\Backend\Database constructor
	 *
	 * @param Phalcon\Cache\FrontendInterface $frontend
	 * @param array                           $options
	 */
	public function __construct($frontend, $options = array())
	{
		if (!isset($options['db'])) {
			throw new Exception("Parameter 'db' is required");
		}

		if (!isset($options['table'])) {
			throw new Exception("Parameter 'table' is required");
		}

		parent::__construct($frontend, $options);
	}

	/**
	 * Get a cached content from the
	 *
	 * @param string $keyName
	 * @param int    $lifetime
	 */
	public function get($keyName, $lifetime = null)
	{

		$options = $this->getOptions();

		$sql = "SELECT data, lifetime FROM " . $options['table'] . " WHERE key_name = ?";
		$cache = $options['db']->fetchOne($sql, Db::FETCH_ASSOC, array($keyName));
		if (!$cache) {
			return null;
		}

		$frontend = $this->getFrontend();

		if ($lifetime === null) {
			$lifetime = $frontend->getLifetime();
		}

		//Remove the cache if expired
		if ($cache['lifetime'] < (time() - $lifetime)) {
			$options['db']->execute("DELETE FROM " . $options['table'] . " WHERE key_name = ?", array($keyName));
			return null;
		}

		$this->setLastKey($keyName);

		return $frontend->afterRetrieve($cache['data']);
	}

	/**
	 * Stores cached content into the APC backend and stops the frontend
	 *
	 * @param string  $keyName
	 * @param string  $content
	 * @param long    $lifetime
	 * @param boolean $stopBuffer
	 */
	public function save($keyName = null, $content = null, $lifetime = null, $stopBuffer = true)
	{

		if ($keyName === null) {
			$lastKey = $this->_lastKey;
		} else {
			$lastKey = $keyName;
		}

		if (!$lastKey) {
			throw new Exception('The cache must be started first');
		}

		$options = $this->getOptions();
		$frontend = $this->getFrontend();

		if ($content === null) {
			$content = $frontend->getContent();
		}

		//Get the lifetime from the frontend
		if ($lifetime === null) {
			$lifetime = $frontend->getLifetime();
		}

		//Check if the cache already exist
		$sql = "SELECT data, lifetime FROM " . $options['table'] . " WHERE key_name = ?";
		$cache = $options['db']->fetchOne($sql, Db::FETCH_ASSOC, array($keyName));
		if (!$cache) {
			$options['db']->execute("INSERT INTO " . $options['table'] . " VALUES (?, ?, ?)", array(
					$keyName,
					$frontend->beforeStore($content),
					time()
				));
		} else {
			$options['db']->execute("UPDATE " . $options['table'] . " SET data = ?, lifetime = ? WHERE key_name = ?", array(
					$frontend->beforeStore($content),
					time(),
					$keyName
				));
		}

		//Stop the buffer, this only applies for Phalcon\Cache\Frontend\Output
		if ($stopBuffer) {
			$frontend->stop();
		}

		//Print the buffer, this only applies for Phalcon\Cache\Frontend\Output
		if ($frontend->isBuffering()) {
			echo $content;
		}

		$this->_started = false;

	}

	/**
	 * Deletes a value from the cache by its key
	 *
	 * @param string $keyName
	 * @return boolean
	 */
	public function delete($keyName)
	{

		$options = $this->getOptions();

		$sql = "SELECT COUNT(*) AS rowcount FROM " . $options['table'] . " WHERE key_name = ?";
		$row = $options['db']->fetchOne($sql, Db::FETCH_ASSOC, array($keyName));
		if (!$row['rowcount']) {
			return false;
		}

		return $options['db']->execute("DELETE FROM " . $options['table'] . " WHERE key_name = ?", array($keyName));
	}

	/**
	 * Query the existing cached keys
	 *
	 * @param string $prefix
	 * @return array
	 */
	public function queryKeys($prefix = null)
	{

		$options = $this->getOptions();

		if ($prefix != null) {
			$sql = "SELECT key_name FROM " . $options['table'] . " WHERE key_name LIKE ? ORDER BY lifetime";
			$caches = $options['db']->query($sql, array($prefix));
		} else {
			$sql = "SELECT key_name FROM " . $options['table'] . " ORDER BY lifetime";
			$caches = $options['db']->query($sql);
		}

		$caches->setFetchMode(Db::FETCH_ASSOC);

		$keys = array();
		while ($row = $caches->fetch()) {
			$keys[] = $row['key_name'];
		}

		return $keys;
	}

	/**
	 * Checks if cache exists.
	 *
	 * @param string $keyName
	 * @param string $lifetime
	 * @return boolean
	 */
	public function exists($keyName = null, $lifetime = null)
	{

		$options = $this->getOptions();

		$sql = "SELECT lifetime FROM " . $options['table'] . " WHERE key_name = ?";
		$cache = $options['db']->fetchOne($sql, Db::FETCH_ASSOC, array($keyName));
		if (!$cache) {
			return false;
		}

		if ($lifetime === null) {
			$lifetime = $this->getFrontend()->getLifetime();
		}

		//Remove the cache if expired
		if ($cache['lifetime'] < (time() - $lifetime)) {
			$options['db']->execute("DELETE FROM " . $options['table'] . " WHERE key_name = ?", array($keyName));
			return false;
		}

		return true;
	}

}