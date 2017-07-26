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
  | Authors: Serghei Iakovlev <serghei@phalconphp.com>                     |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Annotations\Extended\Adapter;

use Phalcon\Annotations\Exception;
use Phalcon\Annotations\Reflection;
use Phalcon\Annotations\Extended\AbstractAdapter;

/**
 * Phalcon\Annotations\Extended\Adapter\Apc
 *
 * Extended Apc adapter for storing annotations in the APC(u).
 * This adapter is suitable for production.
 *
 * <code>
 * use Phalcon\Annotations\Extended\Adapter\Apc;
 *
 * $annotations = new Apc(
 *     [
 *         'prefix'   => 'app-annotations', // Optional
 *         'lifetime' => 8600,              // Optional
 *         'statsKey' => '_PHAN',           // Optional
 *     ]
 * );
 * </code>
 *
 * @package Phalcon\Annotations\Extended\Adapter
 */
class Apc extends AbstractAdapter
{
    /**
     * The storage key prefix.
     * @var string
     */
    protected $prefix = '';

    /**
     * The storage lifetime.
     * @var int
     */
    protected $lifetime = 8600;

    /**
     * Storage stats key
     * @var string
     */
    protected $statsKey = '_PHAN';

    /**
     * Configurable properties.
     * @var array
     */
    protected $configurable = [
        'prefix',
        'lifetime',
        'statsKey',
    ];

    /**
     * Reads parsed annotations from APC(u).
     *
     * @param  string $key
     * @return Reflection|bool
     *
     * @throws Exception
     */
    public function read($key)
    {
        $this->checkKey($key);

        $prefixedKey = $this->getPrefixedIdentifier($key);

        if (function_exists('apcu_fetch')) {
            $result = apcu_fetch($prefixedKey);
        } else {
            $result = apc_fetch($prefixedKey);
        }

        return $this->castResult($result);
    }

    /**
     * Writes parsed annotations to APC(u)
     *
     * @param  string     $key
     * @param  Reflection $data
     * @return bool
     *
     * @throws Exception
     */
    public function write($key, Reflection $data)
    {
        $this->checkKey($key);

        $prefixedKey = $this->getPrefixedIdentifier($key);

        if (function_exists('apcu_store')) {
            return apcu_store($prefixedKey, $data, $this->lifetime);
        }

        return apc_store($prefixedKey, $data, $this->lifetime);
    }

    /**
     * {@inheritdoc}
     *
     * <code>
     * use Phalcon\Annotations\Extended\Apc;
     *
     * $annotations = new Apc(['prefix' => 'app-annotations']);
     * $annotations->flush();
     * </code>
     *
     * @return bool
     */
    public function flush()
    {
        $prefixPattern = '#^_PHAN' . preg_quote("{$this->prefix}", '#') . '#';

        if (class_exists('\APCuIterator')) {
            foreach (new \APCuIterator($prefixPattern) as $item) {
                apcu_delete($item['key']);
            }

            return true;
        }

        foreach (new \APCIterator('user', $prefixPattern) as $item) {
            apc_delete($item['key']);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $id
     * @return string
     */
    protected function getPrefixedIdentifier($id)
    {
        return $this->statsKey . $this->prefix . $id;
    }

    /**
     * Sets the storage key prefix.
     *
     * @param  string $prefix The storage key prefix.
     * @return $this
     */
    protected function setPrefix($prefix)
    {
        $this->prefix = (string) $prefix;

        return $this;
    }

    /**
     * Sets the storage lifetime.
     *
     * @param  int $lifetime The storage lifetime.
     * @return $this
     */
    protected function setLifetime($lifetime)
    {
        $this->lifetime = (int) $lifetime;

        return $this;
    }

    /**
     * Sets the storage stats key.
     *
     * @param  string $statsKey The storage key prefix.
     * @return $this
     */
    protected function setStatsKey($statsKey)
    {
        $this->statsKey = (string) $statsKey;

        return $this;
    }
}
