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

namespace Phalcon\Session\Adapter;

use \Phalcon\Session\Adapter,
	\Phalcon\Session\AdapterInterface;

/**
 * Phalcon\Session\Adapter\Database
 *
 * Database adapter for Phalcon\Session
 */
class Database extends Adapter implements AdapterInterface
{

	/**
	 * Phalcon\Session\Adapter\Database constructor
	 *
	 * @param array $options
	 */
	public function __construct($options=null)
	{

		if(!isset($options['db'])){
			throw new Exception("The parameter 'db' is required");
		}

		if(!isset($options['table'])){
			throw new Exception("The parameter 'table' is required");
		}

		session_set_save_handler(
			array($this, 'open'),
			array($this, 'close'),
			array($this, 'read'),
			array($this, 'write'),
			array($this, 'destroy'),
			array($this, 'gc')
		);

		parent::__construct($options);
	}


	public function open()
	{
		return true;
	}

	public function close()
	{
		return false;
	}

	/**
	 * Reads the data from the table
	 *
	 * @param string $sessionId
	 * @return array
	 */
	public function read($sessionId)
	{
		$options = $this->getOptions();
		$sessionData = $options['db']->fetchOne("SELECT * FROM ".$options['table']." WHERE session_id = '".$sessionId."'");
		if ($sessionData) {
			return $sessionData['data'];
		}
	}

	public function write($sessionId, $data)
	{
		$options = $this->getOptions();
		$exists = $options['db']->fetchOne("SELECT COUNT(*) FROM ".$options['table']." WHERE session_id = '".$sessionId."'");
		if ($exists[0]) {
			$options['db']->execute("UPDATE ".$options['table']." SET data = '".$data."', modified_at = ".time()." WHERE session_id = '".$sessionId."'");
		} else {
			$options['db']->execute("INSERT INTO ".$options['table']." VALUES ('".$sessionId."', '".$data."', ".time().", 0)");
		}
	}

	public function destroy()
	{
		$options = $this->getOptions();
		$options['db']->execute("DELETE FROM ".$options['table']." WHERE session_id = '".$sessionId."'");
	}

	public function gc()
	{

	}

}