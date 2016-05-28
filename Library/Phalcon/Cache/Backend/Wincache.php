<?php
/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2016 Phalcon Team (http://www.phalconphp.com)       |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file docs/LICENSE.txt.                        |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Maciej Ka <maciej@balooncloud.com>                            |
  |          Nikita Vershinin <endeveit@gmail.com>                         |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Cache\Backend;

use Phalcon\Cache\Exception;
use Phalcon\Cache\Backend;
use Phalcon\Cache\BackendInterface;

/**
 * Phalcon\Cache\Backend\Wincache
 *
 * This backend uses wincache as cache backend
 *
 * @package Phalcon\Cache\Backend
 * @property \Phalcon\Cache\FrontendInterface _frontend
 */
class Wincache extends Backend implements BackendInterface
{
    use Prefixable;

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
        $cachedContent  = wincache_ucache_get($prefixedKey, $success);
        $this->_lastKey = $prefixedKey;

        if ($success === false) {
            return null;
        }

        return $this->_frontend->afterRetrieve($cachedContent);
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
            $lastKey = $this->_lastKey;
        } else {
            $lastKey = $this->getPrefixedIdentifier($keyName);
        }

        if (!$lastKey) {
            throw new Exception('The cache must be started first');
        }

        /** @var \Phalcon\Cache\FrontendInterface $frontend */
        $frontend = $this->getFrontend();

        if ($content === null) {
            $cachedContent = $frontend->getContent();
        } else {
            $cachedContent = $content;
        }

        $preparedContent = $frontend->beforeStore($cachedContent);

        if ($lifetime === null) {
            $lifetime = $this->_lastLifetime;

            if ($lifetime === null) {
                $ttl = $frontend->getLifetime();
            } else {
                $ttl = $lifetime;
            }
        } else {
            $ttl = $lifetime;
        }

        $status = wincache_ucache_set($lastKey, $preparedContent, $ttl);

        if (!$status) {
            throw new Exception('Failed storing data by using wincache');
        }

        $isBuffering = $frontend->isBuffering();

        if ($stopBuffer) {
            $frontend->stop();
        }

        if ($isBuffering) {
            echo $content;
        }

        $this->_started = false;

        return $status;
    }

    /**
     * {@inheritdoc}
     *
     * @param  string  $keyName
     * @return boolean
     */
    public function delete($keyName)
    {
        return wincache_ucache_delete($this->getPrefixedIdentifier($keyName));
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $prefix
     * @return array
     */
    public function queryKeys($prefix = null)
    {
        $info    = wincache_ucache_info();
        $entries = array();

        if (!$prefix) {
            $prefix = $this->_prefix;
        } else {
            $prefix = $this->getPrefixedIdentifier($prefix);
        }

        foreach ($info['ucache_entries'] as $entry) {
            $keys[] = !empty($prefix) ? str_replace($prefix, '', $entry['key_name']) : $entry['key_name'];
        }

        return $entries;
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
        if ($keyName === null) {
            $lastKey = $this->_lastKey;
        } else {
            $lastKey = $this->getPrefixedIdentifier($keyName);
        }

        return wincache_ucache_exists($lastKey);
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean
     */
    public function flush()
    {
        return wincache_ucache_clear();
    }
}
