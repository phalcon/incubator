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
use Phalcon\Db\AdapterInterface as DbAdapter;
use Phalcon\Db\Column;

/**
 * Phalcon\Session\Adapter\Database
 * Database adapter for Phalcon\Session
 */
class Database extends Adapter implements AdapterInterface
{
    /**
     * @var DbAdapter
     */
    protected $connection;

    /**
     * {@inheritdoc}
     *
     * @param  array $options
     * @throws Exception
     */
    public function __construct($options = null)
    {
        if (!isset($options['db']) || !$options['db'] instanceof DbAdapter) {
            throw new Exception(
                'Parameter "db" is required and it must be an instance of Phalcon\Db\AdapterInterface'
            );
        }

        $this->connection = $options['db'];
        unset($options['db']);

        if (!isset($options['table']) || empty($options['table']) || !is_string($options['table'])) {
            throw new Exception(
                "Parameter 'table' is required and it must be a non empty string"
            );
        }

        $columns = ['session_id', 'data', 'created_at', 'modified_at'];
        foreach ($columns as $column) {
            $oColumn = "column_$column";
            if (!isset($options[$oColumn]) || !is_string($options[$oColumn]) || empty($options[$oColumn])) {
                $options[$oColumn] = $column;
            }
        }

        parent::__construct($options);

        session_set_save_handler(
            [$this, 'open'],
            [$this, 'close'],
            [$this, 'read'],
            [$this, 'write'],
            [$this, 'destroy'],
            [$this, 'gc']
        );
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean
     */
    public function open()
    {
        $this->_started = true;
        return $this->isStarted();
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean
     */
    public function close()
    {
        $this->_started = false;

        return $this->isStarted();
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $sessionId
     * @return string
     */
    public function read($sessionId)
    {
        $maxLifetime = (int) ini_get('session.gc_maxlifetime');
        
        if (!$this->isStarted()) {
            return false;
        }
        
        $options = $this->getOptions();
        $row = $this->connection->fetchOne(
            sprintf(
                'SELECT %s FROM %s WHERE %s = ? AND COALESCE(%s, %s) + %d >= ?',
                $this->connection->escapeIdentifier($options['column_data']),
                $this->connection->escapeIdentifier($options['table']),
                $this->connection->escapeIdentifier($options['column_session_id']),
                $this->connection->escapeIdentifier($options['column_modified_at']),
                $this->connection->escapeIdentifier($options['column_created_at']),
                $maxLifetime
            ),
            Db::FETCH_NUM,
            [$sessionId, time()],
            [Column::BIND_PARAM_STR, Column::BIND_PARAM_INT]
        );

        if (empty($row)) {
            return '';
        }

        return $row[0];
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
        $options = $this->getOptions();
        $row = $this->connection->fetchOne(
            sprintf(
                'SELECT COUNT(*) FROM %s WHERE %s = ?',
                $this->connection->escapeIdentifier($options['table']),
                $this->connection->escapeIdentifier($options['column_session_id'])
            ),
            Db::FETCH_NUM,
            [$sessionId]
        );
        
        if ($row[0] > 0) {
            return $this->connection->execute(
                sprintf(
                    'UPDATE %s SET %s = ?, %s = ? WHERE %s = ?',
                    $this->connection->escapeIdentifier($options['table']),
                    $this->connection->escapeIdentifier($options['column_data']),
                    $this->connection->escapeIdentifier($options['column_modified_at']),
                    $this->connection->escapeIdentifier($options['column_session_id'])
                ),
                [$data, time(), $sessionId]
            );
        }
        
        if (!$this->isStarted()) {
            return false;
        }
            
        return $this->connection->execute(
            sprintf(
                'INSERT INTO %s (%s, %s, %s, %s) VALUES (?, ?, ?, NULL)',
                $this->connection->escapeIdentifier($options['table']),
                $this->connection->escapeIdentifier($options['column_session_id']),
                $this->connection->escapeIdentifier($options['column_data']),
                $this->connection->escapeIdentifier($options['column_created_at']),
                $this->connection->escapeIdentifier($options['column_modified_at'])
            ),
            [$sessionId, $data, time()]
        );
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean
     */
    public function destroy($session_id = null)
    {
        if (!$this->isStarted()) {
            return true;
        }

        if (is_null($session_id)) {
            $session_id = $this->getId();
        }

        $this->_started = false;
        $options = $this->getOptions();
        $result = $this->connection->execute(
            sprintf(
                'DELETE FROM %s WHERE %s = ?',
                $this->connection->escapeIdentifier($options['table']),
                $this->connection->escapeIdentifier($options['column_session_id'])
            ),
            [$session_id]
        );

        return $result;
    }

    /**
     * {@inheritdoc}
     * @param  integer $maxlifetime
     *
     * @return boolean
     */
    public function gc($maxlifetime)
    {
        $options = $this->getOptions();

        return $this->connection->execute(
            sprintf(
                'DELETE FROM %s WHERE COALESCE(%s, %s) + %d < ?',
                $this->connection->escapeIdentifier($options['table']),
                $this->connection->escapeIdentifier($options['column_modified_at']),
                $this->connection->escapeIdentifier($options['column_created_at']),
                $maxlifetime
            ),
            [time()]
        );
    }
}
