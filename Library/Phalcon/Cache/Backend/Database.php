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
namespace Phalcon\Cache\Backend;

use Phalcon\Cache\Exception;
use Phalcon\Cache\FrontendInterface;
use Phalcon\Db;
use Phalcon\Db\AdapterInterface as DbAdapterInterface;

/**
 * Phalcon\Cache\Backend\Database
 * This backend uses a database as cache backend
 */
class Database extends Prefixable
{
    /**
     * @var \Phalcon\Db\AdapterInterface
     */
    protected $db = null;

    /**
     * @var string
     */
    protected $table = null;

    /**
     * Class constructor.
     *
     * @param  \Phalcon\Cache\FrontendInterface $frontend
     * @param  array                            $options
     * @throws \Phalcon\Cache\Exception
     */
    public function __construct(FrontendInterface $frontend, $options = array())
    {
        if (!isset($options['db'])) {
            throw new Exception("Parameter 'db' is required");
        }

        if (!($options['db'] instanceof DbAdapterInterface)) {
            throw new Exception("Parameter 'db' must implement Phalcon\\Db\\AdapterInterface");
        }

        if (!isset($options['table'])) {
            throw new Exception("Parameter 'table' is required");
        }

        $this->db    = $options['db'];
        $this->table = $options['table'];

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
        $sql            = "SELECT data, lifetime FROM " . $this->table . " WHERE key_name = ?";
        $cache          = $this->db->fetchOne($sql, Db::FETCH_ASSOC, array($prefixedKey));
        $this->_lastKey = $prefixedKey;

        if (!$cache) {
            return null;
        }

        /** @var \Phalcon\Cache\FrontendInterface $frontend */
        $frontend = $this->getFrontend();

        // Remove the cache if expired
        if ($cache['lifetime'] < time()) {
            $this->db->execute("DELETE FROM " . $this->table . " WHERE key_name = ?", array($prefixedKey));

            return null;
        }

        return $frontend->afterRetrieve($cache['data']);
    }

    /**
     * {@inheritdoc}
     *
     * @param  string                   $keyName
     * @param  string                   $content
     * @param  integer                  $lifetime
     * @param  boolean                  $stopBuffer
     * @throws \Phalcon\Cache\Exception
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
        $sql   = "SELECT data, lifetime FROM " . $this->table . " WHERE key_name = ?";
        $cache = $this->db->fetchOne($sql, Db::FETCH_ASSOC, array($prefixedKey));

        if (!$cache) {
            $this->db->execute("INSERT INTO " . $this->table . " VALUES (?, ?, ?)", array(
                $prefixedKey,
                $frontend->beforeStore($cachedContent),
                $lifetime
            ));
        } else {
            $this->db->execute(
                "UPDATE " . $this->table . " SET data = ?, lifetime = ? WHERE key_name = ?",
                array(
                    $frontend->beforeStore($cachedContent),
                    $lifetime,
                    $prefixedKey
                )
            );
        }

        if ($stopBuffer) {
            $frontend->stop();
        }

        if ($frontend->isBuffering()) {
            echo $content;
        }

        $this->_started = false;
    }

    /**
     * {@inheritdoc}
     *
     * @param  string  $keyName
     * @return boolean
     */
    public function delete($keyName)
    {
        $prefixedKey = $this->getPrefixedIdentifier($keyName);
        $sql         = "SELECT COUNT(*) AS rowcount FROM " . $this->table . " WHERE key_name = ?";
        $row         = $this->db->fetchOne($sql, Db::FETCH_ASSOC, array($prefixedKey));

        if (!$row['rowcount']) {
            return false;
        }

        return $this->db->execute("DELETE FROM " . $this->table . " WHERE key_name = ?", array($prefixedKey));
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
            $sql = "SELECT key_name FROM " . $this->table . " WHERE key_name LIKE ? ORDER BY lifetime";
            $rs  = $this->db->query($sql, array($prefix . '%'));
        } else {
            $sql = "SELECT key_name FROM " . $this->table . " ORDER BY lifetime";
            $rs  = $this->db->query($sql);
        }

        $rs->setFetchMode(Db::FETCH_ASSOC);

        $keys = array();

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
     * @return boolean
     */
    public function exists($keyName = null, $lifetime = null)
    {
        $prefixedKey = $this->getPrefixedIdentifier($keyName);
        $sql         = "SELECT lifetime FROM " . $this->table . " WHERE key_name = ?";
        $cache       = $this->db->fetchOne($sql, Db::FETCH_ASSOC, array($prefixedKey));

        if (!$cache) {
            return false;
        }

        // Remove the cache if expired
        if ($cache['lifetime'] < time()) {
            $this->db->execute("DELETE FROM " . $this->table . " WHERE key_name = ?", array($prefixedKey));

            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean
     */
    public function flush()
    {
        $this->db->execute('DELETE FROM ' . $this->table);

        return true;
    }
}
