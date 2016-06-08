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
  | Authors: Serghei Iakovlev <serghei@phalconphp.com>                     |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Annotations\Adapter;

use Phalcon\Cache\Backend\Redis as BackendRedis;
use Phalcon\Cache\Frontend\Data as FrontendData;

/**
 * Class Redis
 *
 * Stores the parsed annotations to the Redis database.
 * This adapter is suitable for production.
 *
 *<code>
 * use Phalcon\Annotations\Adapter\Redis;
 *
 * $annotations = new Redis([
 *     'lifetime' => 8600,
 *     'host'     => 'localhost',
 *     'port'     => 6379,
 *     'prefix'   => 'annotations_',
 * ]);
 *</code>
 *
 * @package Phalcon\Annotations\Adapter
 */
class Redis extends Base
{
    protected $redis;

    /**
     * {@inheritdoc}
     *
     * @param null|array $options options array
     */
    public function __construct(array $options)
    {
        if (!isset($options['host'])) {
            $options['host'] = '127.0.0.1';
        }

        if (!isset($options['port'])) {
            $options['port'] = 6379;
        }

        if (!isset($options['persistent'])) {
            $options['persistent'] = false;
        }

        parent::__construct($options);

        $this->redis = new BackendRedis(
            new FrontendData(['lifetime' => $this->options['lifetime']]),
            $options
        );
    }

    /**
     * {@inheritdoc}
     *
     * @return \Phalcon\Cache\Backend\Redis
     */
    protected function getCacheBackend()
    {
        return $this->redis;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $key
     * @return string
     */
    protected function prepareKey($key)
    {
        return strval($key);
    }
}
