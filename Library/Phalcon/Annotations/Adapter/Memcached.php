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
  | Authors: Ilya Gusev <mail@igusev.ru>                                   |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Annotations\Adapter;

use Phalcon\Cache\Backend\Libmemcached as CacheBackend;
use Phalcon\Cache\Frontend\Data as CacheFrontend;
use Phalcon\Annotations\Exception;
use Memcached as MemcachedGeneric;
use Phalcon\Annotations\Adapter;

/**
 * Class Memcached
 *
 * Stores the parsed annotations to Memcached.
 * This adapter is suitable for production.
 *
 *<code>
 * use Phalcon\Annotations\Adapter\Memcached;
 *
 * $annotations = new Memcached([
 *     'lifetime' => 8600,
 *     'host'     => 'localhost',
 *     'port'     => 11211,
 *     'weight'   => 1,
 *     'prefix'   => 'prefix.',
 * ]);
 *</code>
 *
 * @package Phalcon\Annotations\Adapter
 */
class Memcached extends Base
{
    /**
     * Default option for memcached port.
     *
     * @var array
     */
    protected static $defaultPort = 11211;

    /**
     * Default option for weight.
     *
     * @var int
     */
    protected static $defaultWeight = 1;

    /**
     * Memcached backend instance.
     *
     * @var CacheBackend
     */
    protected $memcached = null;

    /**
     * {@inheritdoc}
     *
     * @param array $options options array
     *
     * @throws Exception
     */
    public function __construct(array $options)
    {
        if (!isset($options['host'])) {
            throw new Exception('No host given in options');
        }

        if (!isset($options['port'])) {
            $options['port'] = self::$defaultPort;
        }

        if (!isset($options['weight'])) {
            $options['weight'] = self::$defaultWeight;
        }

        parent::__construct($options);
    }

    /**
     * {@inheritdoc}
     *
     * @return CacheBackend
     */
    protected function getCacheBackend()
    {
        if (null === $this->memcached) {
            $this->memcached = new CacheBackend(
                new CacheFrontend(['lifetime' => $this->options['lifetime']]),
                [
                    'servers' => [
                        [
                            'host' => $this->options['host'],
                            'port' => $this->options['port'],
                            'weight' => $this->options['weight']
                        ],
                    ],
                    'client' => [
                        MemcachedGeneric::OPT_HASH => MemcachedGeneric::HASH_MD5,
                        MemcachedGeneric::OPT_PREFIX_KEY => $this->options['prefix']
                    ]
                ]
            );
        }

        return $this->memcached;
    }


    /**
     * {@inheritdoc}
     *
     * @param string $key
     * @return string
     */
    protected function prepareKey($key)
    {
        return $key;
    }
}
