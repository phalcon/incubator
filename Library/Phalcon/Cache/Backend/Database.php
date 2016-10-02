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

namespace Phalcon\Cache\Backend;

use Phalcon\Cache\Exception;
use Phalcon\Cache\FrontendInterface;
use Phalcon\Db;
use Phalcon\Db\AdapterInterface as DbAdapterInterface;
use Phalcon\Cache\Backend;
use Phalcon\Cache\BackendInterface;

/**
 * Phalcon\Cache\Backend\Database
 *
 * This backend uses a database as cache backend
 *
 * @package Phalcon\Cache\Backend
 * @property \Phalcon\Cache\FrontendInterface _frontend
 */
class Database extends Backend implements BackendInterface
{
    use Prefixable;

    /**
     * @var DbAdapterInterface
     */
    protected $db = null;

    /**
     * @var string
     */
    protected $table = null;

    /**
     * {@inheritdoc}
     *
     * @param  FrontendInterface $frontend
     * @param  array             $options
     * @throws Exception
     */
    public function __construct(FrontendInterface $frontend, array $options)
    {
        if (!isset($options['db']) || !$options['db'] instanceof DbAdapterInterface) {
            throw new Exception(
                'Parameter "db" is required and it must be an instance of Phalcon\Acl\AdapterInterface'
            );
        }

        if (!isset($options['table']) || empty($options['table']) || !is_string($options['table'])) {
            throw new Exception("Parameter 'table' is required and it must be a non empty string");
        }

        $this->db    = $options['db'];
        $this->table = $this->db->escapeIdentifier($options['table']);

        unset($options['db'], $options['table']);

        parent::__construct($frontend, $options);
    }

    /**
     * {@inheritdoc}
     *
     * @param  string     $keyName
     * @param  integer    $lifetime
     * @return mixed|null
     */
    public function get($keyName, $lifetime = null)
    {
        $prefixedKey    = $this->getPrefixedIdentifier($keyName);
        $sql            = "SELECT data, lifetime FROM {$this->table} WHERE key_name = ?";
        $cache          = $this->db->fetchOne($sql, Db::FETCH_ASSOC, [$prefixedKey]);
        $this->_lastKey = $prefixedKey;

        if (!$cache) {
            return null;
        }

        /** @var \Phalcon\Cache\FrontendInterface $frontend */
        $frontend = $this->getFrontend();

        // Remove the cache if expired
        if ($cache['lifetime'] < time()) {
            $this->db->execute("DELETE FROM {$this->table} WHERE key_name = ?", [$prefixedKey]);

            return null;
        }

        return $frontend->afterRetrieve($cache['data']);
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $keyName
     * @param  string $content
     * @param  int    $lifetime
     * @param  bool   $stopBuffer
     * @return bool
     *
     * @throws Exception
     */
    public function save($keyName = null, $content = null, $lifetime = null, $stopBuffer = true)
    {
        if ($keyName === null) {
            $prefixedKey = $this->_lastKey;
        } else {
            $prefixedKey = $this->getPrefixedIdentifier($keyName);
        }

        if (!$prefixedKey) {
            throw new Exception('The cache must be started first');
        }

        /** @var \Phalcon\Cache\FrontendInterface $frontend */
        $frontend = $this->getFrontend();

        if ($content === null) {
            $cachedContent = $frontend->getContent();
        } else {
            $cachedContent = $content;
        }

        if (null === $lifetime) {
            $lifetime = $frontend->getLifetime();
        }

        $lifetime = time() + $lifetime;

        // Check if the cache already exist
        $sql   = "SELECT data, lifetime FROM {$this->table} WHERE key_name = ?";
        $cache = $this->db->fetchOne($sql, Db::FETCH_ASSOC, [$prefixedKey]);

        if (!$cache) {
            $status = $this->db->execute("INSERT INTO {$this->table} VALUES (?, ?, ?)", [
                $prefixedKey,
                $frontend->beforeStore($cachedContent),
                $lifetime
            ]);
        } else {
            $status = $this->db->execute(
                "UPDATE {$this->table} SET data = ?, lifetime = ? WHERE key_name = ?",
                [
                    $frontend->beforeStore($cachedContent),
                    $lifetime,
                    $prefixedKey
                ]
            );
        }

        if (!$status) {
            throw new Exception('Failed storing data in database');
        }

        if ($stopBuffer) {
            $frontend->stop();
        }

        if ($frontend->isBuffering()) {
            echo $content;
        }

        $this->_started = false;

        return $status;
    }

    /**
     * {@inheritdoc}
     *
     * @param  string  $keyName
     * @return bool
     */
    public function delete($keyName)
    {
        $prefixedKey = $this->getPrefixedIdentifier($keyName);
        $sql         = "SELECT COUNT(*) AS rowcount FROM {$this->table} WHERE key_name = ?";
        $row         = $this->db->fetchOne($sql, Db::FETCH_ASSOC, [$prefixedKey]);

        if (!$row['rowcount']) {
            return false;
        }

        return $this->db->execute("DELETE FROM {$this->table} WHERE key_name = ?", [$prefixedKey]);
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $prefix
     * @return array
     */
    public function queryKeys($prefix = null)
    {
        if (!$prefix) {
            $prefix = $this->_prefix;
        } else {
            $prefix = $this->getPrefixedIdentifier($prefix);
        }

        if (!empty($prefix)) {
            $sql = "SELECT key_name FROM {$this->table} WHERE key_name LIKE ? ORDER BY lifetime";
            $rs  = $this->db->query($sql, [$prefix . '%']);
        } else {
            $sql = "SELECT key_name FROM {$this->table} ORDER BY lifetime";
            $rs  = $this->db->query($sql);
        }

        $rs->setFetchMode(Db::FETCH_ASSOC);

        $keys = [];

        while ($row = $rs->fetch()) {
            $keys[] = !empty($prefix) ? str_replace($prefix, '', $row['key_name']) : $row['key_name'];
        }

        return $keys;
    }

    /**
     * {@inheritdoc}
     *
     * @param  string  $keyName
     * @param  string  $lifetime
     * @return bool
     */
    public function exists($keyName = null, $lifetime = null)
    {
        $prefixedKey = $this->getPrefixedIdentifier($keyName);
        $sql         = "SELECT lifetime FROM {$this->table} WHERE key_name = ?";
        $cache       = $this->db->fetchOne($sql, Db::FETCH_ASSOC, [$prefixedKey]);

        if (!$cache) {
            return false;
        }

        // Remove the cache if expired
        if ($cache['lifetime'] < time()) {
            $this->db->execute("DELETE FROM {$this->table} WHERE key_name = ?", [$prefixedKey]);

            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function flush()
    {
        $this->db->execute("DELETE FROM {$this->table}");

        return true;
    }
}
