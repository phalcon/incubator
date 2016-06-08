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
  | Authors: Ilya Gusev <mail@igusev.ru>                                   |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Annotations\Adapter;

use Phalcon\Annotations\Adapter;

/**
 * \Phalcon\Annotations\Adapter\Base
 *
 * Base class for annotations adapters.
 *
 * @package Phalcon\Annotations\Adapter
 */
abstract class Base extends Adapter
{
    /**
     * Default option for cache lifetime.
     *
     * @var array
     */
    protected static $defaultLifetime = 8600;

    /**
     * Default option for prefix.
     *
     * @var string
     */
    protected static $defaultPrefix = '';

    /**
     * Backend's options.
     *
     * @var array
     */
    protected $options = null;

    /**
     * Class constructor.
     *
     * @param null|array $options
     *
     * @throws \Phalcon\Mvc\Model\Exception
     */
    public function __construct($options = null)
    {
        if (!is_array($options) || !isset($options['lifetime'])) {
            $options['lifetime'] = self::$defaultLifetime;
        }

        if (!is_array($options) || !isset($options['prefix'])) {
            $options['prefix'] = self::$defaultPrefix;
        }

        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $key
     *
     * @return array
     */
    public function read($key)
    {
        return $this->getCacheBackend()->get(
            $this->prepareKey($key),
            $this->options['lifetime']
        );
    }

    /**
     * {@inheritdoc}
     *
     * @param string $key
     * @param array $data
     */
    public function write($key, $data)
    {
        $this->getCacheBackend()->save(
            $this->prepareKey($key),
            $data,
            $this->options['lifetime']
        );
    }

    /**
     * Returns the key with a prefix or other changes
     *
     * @param string $key
     *
     * @return string
     */
    abstract protected function prepareKey($key);

    /**
     * Returns cache backend instance.
     *
     * @return \Phalcon\Cache\BackendInterface
     */
    abstract protected function getCacheBackend();
}
