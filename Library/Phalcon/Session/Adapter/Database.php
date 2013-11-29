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
  |          Nikita Vershinin <endeveit@gmail.com>                         |
  +------------------------------------------------------------------------+
*/
namespace Phalcon\Session\Adapter;

use Phalcon\Db;
use Phalcon\Session\Adapter;
use Phalcon\Session\AdapterInterface;
use Phalcon\Session\Exception;

/**
 * Phalcon\Session\Adapter\Database
 *
 * Database adapter for Phalcon\Session
 */
class Database extends Adapter implements AdapterInterface
{

	/**
	 * Flag to check if session is destroyed.
	 *
	 * @var boolean
	 */
	protected $isDestroyed = false;

	/**
	 * {@inheritdoc}
	 *
	 * @param  array $options
	 * @throws \Phalcon\Session\Exception
	 */
	public function __construct($options = null)
	{
		if (!isset($options['db'])) {
			throw new Exception("The parameter 'db' is required");
		}

		if (!isset($options['table'])) {
			throw new Exception("The parameter 'table' is required");
		}

		parent::__construct($options);

		session_set_save_handler(
			array($this, 'open'),
			array($this, 'close'),
			array($this, 'read'),
			array($this, 'write'),
			array($this, 'destroy'),
			array($this, 'gc')
		);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return boolean
	 */
	public function open()
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return boolean
	 */
	public function close()
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param  string $sessionId
	 * @return string
	 */
	public function read($sessionId)
	{
		$options = $this->getOptions();
		$row = $options['db']->fetchOne(
			sprintf(
				'SELECT %s FROM %s WHERE %s = ?',
				$options['db']->escapeIdentifier('data'),
				$options['db']->escapeIdentifier($options['table']),
				$options['db']->escapeIdentifier('session_id')
			),
			Db::FETCH_NUM,
			array($sessionId)
		);

		if (!empty($row)) {
			return $row[0];
		}

		return '';
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param  string $sessionId
	 * @param  string $data
	 * @return boolean
	 */
	public function write($sessionId, $data)
	{
		if ($this->isDestroyed || empty($data)) {
			return false;
		}

		$options = $this->getOptions();
		$row = $options['db']->fetchOne(
			sprintf(
				'SELECT COUNT(*) FROM %s WHERE %s = ?',
				$options['db']->escapeIdentifier($options['table']),
				$options['db']->escapeIdentifier('session_id')
			),
			Db::FETCH_NUM,
			array($sessionId)
		);

		if (!empty($row) && intval($row[0]) > 0) {
			return $options['db']->execute(
				sprintf(
					'UPDATE %s SET %s = ?, %s = ? WHERE %s = ?',
					$options['db']->escapeIdentifier($options['table']),
					$options['db']->escapeIdentifier('data'),
					$options['db']->escapeIdentifier('modified_at'),
					$options['db']->escapeIdentifier('session_id')
				),
				array($data, time(), $sessionId)
			);
		} else {
			return $options['db']->execute(
				sprintf('INSERT INTO %s VALUES (?, ?, ?, 0)', $options['db']->escapeIdentifier($options['table'])),
				array($sessionId, $data, time())
			);
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return boolean
	 */
	public function destroy()
	{
		if (!$this->isStarted() || $this->isDestroyed) {
			return true;
		}

		$this->isDestroyed = true;
		$options = $this->getOptions();
		$result = $options['db']->execute(
			sprintf(
				'DELETE FROM %s WHERE %s = ?',
				$options['db']->escapeIdentifier($options['table']),
				$options['db']->escapeIdentifier('session_id')
			),
			array($this->getId())
		);

		session_regenerate_id();

		return $result;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param  integer $maxlifetime
	 * @return boolean
	 */
	public function gc($maxlifetime)
	{
		$options = $this->getOptions();

		return $options['db']->execute(
			sprintf(
				'DELETE FROM %s WHERE %s + %d < ?',
				$options['db']->escapeIdentifier($options['table']),
				$options['db']->escapeIdentifier('modified_at'),
				$maxlifetime
			),
			array(time())
		);
	}

}
