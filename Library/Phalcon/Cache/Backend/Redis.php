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

/**
 * Phalcon\Cache\Backend\Redis
 * This backend uses redis as cache backend
 */
class Redis extends Backend implements BackendInterface
{

    /**
     * Phalcon\Cache\Backend\Redis constructor
     *
     * @param \Phalcon\Cache\FrontendInterface $frontend
     * @param array                            $options
     * @throws \Phalcon\Cache\Exception
     */
    public function __construct($frontend, $options = null)
    {
        if (!isset($options['redis'])) {
            throw new Exception("Parameter 'redis' is required");
        }

        parent::__construct($frontend, $options);
    }

    /**
     * Get cached content from the Redis backend
     *
     * @param string $keyName
     * @param null   $lifetime
     * @param int    $lifetime
     * @return mixed|null
     */
    public function get($keyName, $lifetime = null)
    {
        $options = $this->getOptions();

        $value = $options['redis']->get($keyName);
        if ($value === false) {
            return null;
        }

        $frontend = $this->getFrontend();

        $this->setLastKey($keyName);

        return $frontend->afterRetrieve($value);
    }

    /**
     * Stores cached content into the Redis backend and stops the frontend
     *
     * @param string  $keyName
     * @param string  $content
     * @param int     $lifetime
     * @param boolean $stopBuffer
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

        //Get the lifetime from the frontend
        if ($lifetime === null) {
            $lifetime = $frontend->getLifetime();
        }

        $options['redis']->setex($lastKey, $lifetime, $frontend->beforeStore($content));

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
    public function delete($keyName)
    {
        $options = $this->getOptions();
        return $options['redis']->delete($keyName) > 0;
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
        if ($prefix === null) {
            return $options['redis']->getKeys('*');
        } else {
            return $options['redis']->getKeys($prefix . '*');
        }
    }

    /**
     * Checks if a value exists in the cache by checking its key.
     *
     * @param string $keyName
     * @param string $lifetime
     * @return boolean
     */
    public function exists($keyName = null, $lifetime = null)
    {
        $options = $this->getOptions();
        return $options['redis']->exists($keyName);
    }

}
