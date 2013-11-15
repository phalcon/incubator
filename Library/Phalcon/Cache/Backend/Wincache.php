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

use Phalcon\Cache\Backend,
    Phalcon\Cache\BackendInterface,
    Phalcon\Cache\Exception;

/**
 * Phalcon\Cache\Backend\Wincache
 *
 * This backend uses Wincache as cache backend
 */
class Wincache extends Backend implements BackendInterface {

    /**
     * Phalcon\Cache\Backend\Wincache constructor
     *
     * @param \Phalcon\Cache\FrontendInterface $frontend
     * @param array $options
     * @throws \Phalcon\Cache\Exception
     */
    public function __construct($frontend, $options = null) {
        parent::__construct($frontend, $options);
    }

    /**
     * Get cached content from the Wincache backend
     *
     * @param string $keyName
     * @param null $lifetime
     * @param int $lifetime
     * @return mixed|null
     */
    public function get($keyName, $lifetime = null) {
        $value = wincache_ucache_get($this->_prefix . $keyName, $success);
        if (!$success || empty($value)) {
            return null;
        }

        $frontend = $this->getFrontend();
        $this->setLastKey($this->_prefix . $keyName);

        return $frontend->afterRetrieve($value);
    }

    /**
     * Stores cached content into the Wincache backend and stops the frontend
     *
     * @param string $keyName
     * @param string $content
     * @param int $lifetime
     * @param boolean $stopBuffer
     * @throws \Phalcon\Cache\Exception
     */
    public function save($keyName = null, $content = null, $lifetime = null, $stopBuffer = true) {

        if ($keyName === null) {
            $lastKey = $this->_lastKey;
        } else {
            $lastKey = $this->_prefix . $keyName;
        }

        if (!$lastKey) {
            throw new Exception('The cache must be started first');
        }

        $frontend = $this->getFrontend();

        if ($content === null) {
            $content = $frontend->getContent();
        }

        //Get the lifetime from the frontend
        if ($lifetime === null) {
            $lifetime = $frontend->getLifetime();
        }

        wincache_ucache_set($lastKey, $frontend->beforeStore($content), $lifetime);

        $isBuffering = $frontend->isBuffering();

        //Stop the buffer, this only applies for Phalcon\Cache\Frontend\Output
        if ($stopBuffer) {
            $frontend->stop();
        }

        //Print the buffer, this only applies for Phalcon\Cache\Frontend\Output
        if ($isBuffering) {
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
    public function delete($keyName) {
        return wincache_ucache_delete($this->_prefix . $keyName);
    }

    /**
     * Query the existing cached keys
     *
     * @param string $prefix
     * @return array
     */
    public function queryKeys($prefix = null) {
        $options = $this->getOptions();
        if (is_null($prefix)) {
            $prefix = $this->_prefix;
        }
        $ucache_info = wincache_ucache_info();
        $entries = $ucache_info["ucache_entries"];
        $keys = array();
        foreach ($entries as $entry) {
            if (is_null($prefix)) {
                $keys[] = $entry['key_name'];
            } else {
                if (preg_match("/^$prefix/", $entry['key_name'])) {
                    $keys[] = $entry['key_name'];
                }
            }
        }
        return $keys;
    }

    /**
     * Checks if a value exists in the cache by checking its key.
     *
     * @param string $keyName
     * @param string $lifetime
     * @return boolean
     */
    public function exists($keyName = null, $lifetime = null) {
        return wincache_ucache_exists($this->_prefix . $keyName);
    }

}
