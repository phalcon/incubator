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
use Phalcon\Db;

/**
 * Phalcon\Cache\Backend\Database
 * This backend uses a database as cache backend
 */
class Database extends Prefixable
{

    /**
     * Class constructor.
     *
     * @param  \Phalcon\Cache\FrontendInterface $frontend
     * @param  array                            $options
     * @throws \Phalcon\Cache\Exception
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
     * {@inheritdoc}
     *
     * @param  string     $keyName
     * @param  integer    $lifetime
     * @return mixed|null
     */
    public function get($keyName, $lifetime = null)
    {
        $prefixedKey = $this->getPrefixedIdentifier($keyName);
        $options     = $this->getOptions();
        $sql         = "SELECT data, lifetime FROM " . $options['table'] . " WHERE key_name = ?";
        $cache       = $options['db']->fetchOne($sql, Db::FETCH_ASSOC, array($prefixedKey));

        if (!$cache) {
            return null;
        }

        $frontend = $this->getFrontend();

        if ($lifetime === null) {
            $lifetime = $frontend->getLifetime();
        }

        //Remove the cache if expired
        if ($cache['lifetime'] < (time() - $lifetime)) {
            $options['db']->execute("DELETE FROM " . $options['table'] . " WHERE key_name = ?", array($prefixedKey));

            return null;
        }

        $this->setLastKey($keyName);

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

        // Check if the cache already exist
        $prefixedKey = $this->getPrefixedIdentifier($keyName);
        $sql         = "SELECT data, lifetime FROM " . $options['table'] . " WHERE key_name = ?";
        $cache       = $options['db']->fetchOne($sql, Db::FETCH_ASSOC, array($prefixedKey));

        if (!$cache) {
            $options['db']->execute("INSERT INTO " . $options['table'] . " VALUES (?, ?, ?)", array(
                $prefixedKey,
                $frontend->beforeStore($content),
                time()
            ));
        } else {
            $options['db']->execute(
                "UPDATE " . $options['table'] . " SET data = ?, lifetime = ? WHERE key_name = ?",
                array(
                    $frontend->beforeStore($content),
                    time(),
                    $prefixedKey
                )
            );
        }

        // Stop the buffer, this only applies for Phalcon\Cache\Frontend\Output
        if ($stopBuffer) {
            $frontend->stop();
        }

        // Print the buffer, this only applies for Phalcon\Cache\Frontend\Output
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
        $options     = $this->getOptions();
        $sql         = "SELECT COUNT(*) AS rowcount FROM " . $options['table'] . " WHERE key_name = ?";
        $row         = $options['db']->fetchOne($sql, Db::FETCH_ASSOC, array($prefixedKey));

        if (!$row['rowcount']) {
            return false;
        }

        return $options['db']->execute("DELETE FROM " . $options['table'] . " WHERE key_name = ?", array($prefixedKey));
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $prefix
     * @return array
     */
    public function queryKeys($prefix = null)
    {
        $options       = $this->getOptions();
        $optionsPrefix = !empty($options['prefix'])
            ? $options['prefix']
            : '';

        if ($prefix != null || !empty($optionsPrefix)) {
            if ($prefix == null) {
                $prefix = $this->getPrefixedIdentifier('');
            } else {
                $prefix = $this->getPrefixedIdentifier($prefix);
            }

            $sql    = "SELECT key_name FROM " . $options['table'] . " WHERE key_name LIKE ? ORDER BY lifetime";
            $caches = $options['db']->query($sql, array($prefix));
        } else {
            $sql    = "SELECT key_name FROM " . $options['table'] . " ORDER BY lifetime";
            $caches = $options['db']->query($sql);
        }

        $caches->setFetchMode(Db::FETCH_ASSOC);

        $keys = array();
        while ($row = $caches->fetch()) {
            $keys[] = !empty($optionsPrefix)
                ? str_replace($optionsPrefix, '', $row['key_name'])
                : $row['key_name'];
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
        $options     = $this->getOptions();
        $sql         = "SELECT lifetime FROM " . $options['table'] . " WHERE key_name = ?";
        $cache       = $options['db']->fetchOne($sql, Db::FETCH_ASSOC, array($prefixedKey));

        if (!$cache) {
            return false;
        }

        if ($lifetime === null) {
            $lifetime = $this->getFrontend()->getLifetime();
        }

        //Remove the cache if expired
        if ($cache['lifetime'] < (time() - $lifetime)) {
            $options['db']->execute("DELETE FROM " . $options['table'] . " WHERE key_name = ?", array($prefixedKey));

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
        $options = $this->getOptions();
        $options['db']->execute('DELETE FROM ' . $options['table']);

        return true;
    }
}
