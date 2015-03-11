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

/**
 * Phalcon\Cache\Backend\Redis
 * This backend uses redis as cache backend
 */
class Redis extends Prefixable
{

    /**
     * Class constructor.
     *
     * @param  \Phalcon\Cache\FrontendInterface $frontend
     * @param  array                            $options
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
     * {@inheritdoc}
     *
     * @param  string     $keyName
     * @param  integer    $lifetime
     * @return mixed|null
     */
    public function get($keyName, $lifetime = null)
    {
        $options = $this->getOptions();

        $value = $options['redis']->get($this->getPrefixedIdentifier($keyName));
        if ($value === false) {
            return null;
        }

        $frontend = $this->getFrontend();

        $this->setLastKey($keyName);

        return $frontend->afterRetrieve($value);
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

        // Get the lifetime from the frontend
        if ($lifetime === null) {
            $lifetime = $frontend->getLifetime();
        }

        $options['redis']->setex($this->getPrefixedIdentifier($lastKey), $lifetime, $frontend->beforeStore($content));

        $isBuffering = $frontend->isBuffering();

        // Stop the buffer, this only applies for Phalcon\Cache\Frontend\Output
        if ($stopBuffer) {
            $frontend->stop();
        }

        // Print the buffer, this only applies for Phalcon\Cache\Frontend\Output
        if ($isBuffering) {
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
        $options = $this->getOptions();
        $redisPrefix = $options['redis']->getOption(\Redis::OPT_PREFIX);

        if (!empty($redisPrefix)) {
            $redisPrefixLen = strlen($redisPrefix);
            $keys = $options['redis']->getKeys($this->getPrefixedIdentifier($keyName) . '*');
            $keys = array_map(function ($key) use ($redisPrefixLen) {
                return substr($key, $redisPrefixLen);
            }, $keys);

            return $options['redis']->delete($keys) > 0;
        } else {
            return $options['redis']->delete($this->getPrefixedIdentifier($keyName)) > 0;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $prefix
     * @return array
     */
    public function queryKeys($prefix = null)
    {
        $options = $this->getOptions();

        if ($prefix === null) {
            $result = $options['redis']->getKeys($this->getPrefixedIdentifier('*'));
        } else {
            $result = $options['redis']->getKeys($this->getPrefixedIdentifier($prefix) . '*');
        }

        if (!empty($options['prefix']) && !empty($result)) {
            $optionsPrefix = $options['prefix'];

            array_walk($result, function (&$key) use ($optionsPrefix) {
                $key = str_replace($optionsPrefix, '', $key);
            });
        }

        return $result;
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
        $options = $this->getOptions();

        return $options['redis']->exists($this->getPrefixedIdentifier($keyName));
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean
     */
    public function flush()
    {
        $options = $this->getOptions();
        $keys = $options['redis']->keys($this->getPrefixedIdentifier('*'));
        $redisPrefix = $options['redis']->getOption(\Redis::OPT_PREFIX);

        if (!empty($redisPrefix)) {
            $redisPrefixLen = strlen($redisPrefix);
            $keys = array_map(function ($key) use ($redisPrefixLen) {
                return substr($key, $redisPrefixLen);
            }, $keys);
        }

        return $options['redis']->delete($keys);
    }
}
