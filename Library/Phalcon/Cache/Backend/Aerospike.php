<?php

/*
 +------------------------------------------------------------------------+
 | Phalcon Framework                                                      |
 +------------------------------------------------------------------------+
 | Copyright (c) 2011-2015 Phalcon Team (http://www.phalconphp.com)       |
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

namespace Phalcon\Cache\Backend;

use Aerospike as AerospikeDb;
use Phalcon\Cache\FrontendInterface;
use Phalcon\Cache\Exception;

/**
 * Phalcon\Cache\Backend\Aerospike
 *
 * Allows to cache output fragments, PHP data or raw data to a Aerospike backend.
 *
 * <code>
 * use Phalcon\Cache\Frontend\Data;
 * use Phalcon\Cache\Backend\Aerospike as CacheBackend;
 *
 * // Cache data for 2 days
 * $frontCache = new Data(['lifetime' => 172800]);
 *
 * // Create the Cache setting redis connection options
 * $cache = new CacheBackend($frontCache, [
 *     'hosts' => [
 *         ['addr' => '127.0.0.1', 'port' => 3000]
 *     ],
 *     'persistent' => true,
 *     'namespace'  => 'test',
 *     'prefix'     => 'session_',
 *     'options'    => [
 *         \Aerospike::OPT_CONNECT_TIMEOUT => 1250,
 *         \Aerospike::OPT_WRITE_TIMEOUT   => 1500
 *     ]
 * ]);
 *
 * // Cache arbitrary data
 * $cache->save('my-data', [1, 2, 3, 4, 5]);
 *
 * // Get data
 * $data = $cache->get('my-data');
 * </code>
 *
 * @package Phalcon\Cache\Backend
 * @property \Phalcon\Cache\FrontendInterface _frontend
 */
class Aerospike extends Prefixable
{
    /**
     * The Aerospike DB
     * @var AerospikeDb
     */
    protected $db;

    /**
     * Default Aerospike namespace
     * @var string
     */
    protected $namespace = 'test';

    /**
     * The Aerospike Set for store sessions
     * @var string
     */
    protected $set = 'cache';

    /**
     * Phalcon\Cache\Backend\Aerospike constructor
     *
     * @param  FrontendInterface $frontend Frontend Interface
     * @param  array             $options  Constructor options
     * @throws Exception
     */
    public function __construct(FrontendInterface $frontend, array $options)
    {
        if (!isset($options['hosts']) || !is_array($options['hosts'])) {
            throw new Exception('No hosts given in options');
        }

        if (isset($options['namespace'])) {
            $this->namespace = $options['namespace'];
        }

        if (isset($options['prefix'])) {
            $this->_prefix = $options['persistent'];
        }

        $persistent = false;
        if (isset($options['persistent'])) {
            $persistent = (bool) $options['persistent'];
        }

        $opts = [];
        if (isset($options['options']) && is_array($options['options'])) {
            $opts = $options['options'];
        }

        $this->db = new AerospikeDb(['hosts' => $options['hosts']], $persistent, $opts);

        if (!$this->db->isConnected()) {
            throw new Exception(
                sprintf("Aerospike failed to connect[%s]: %s", $this->db->errorno(), $this->db->error())
            );
        }

        parent::__construct($frontend, $options);
    }

    /**
     * {@inheritdoc}
     *
     * @param int|string $keyName
     * @param string     $content
     * @param int        $lifetime
     * @param bool       $stopBuffer
     *
     * @throws Exception
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

        if (null === $content) {
            $cachedContent = $this->_frontend->getContent();
        } else {
            $cachedContent = $content;
        }

        if (null === $lifetime) {
            $lifetime = $this->_lastLifetime;

            if (null === $lifetime) {
                $lifetime = $this->_frontend->getLifetime();
            }
        }

        $aKey = $this->buildKey($prefixedKey);

        if (is_numeric($cachedContent)) {
            $bins = ['value' => $cachedContent];
        } else {
            $bins = ['value' => $this->_frontend->beforeStore($cachedContent)];
        }

        $status = $this->db->put(
            $aKey,
            $bins,
            $lifetime,
            [AerospikeDb::OPT_POLICY_KEY => AerospikeDb::POLICY_KEY_SEND]
        );

        if (AerospikeDb::OK != $status) {
            throw new Exception(
                sprintf('Failed storing data in Aerospike: %s', $this->db->error()),
                $this->db->errorno()
            );
        }

        if (true === $stopBuffer) {
            $this->_frontend->stop();
        }

        if (true === $this->_frontend->isBuffering()) {
            echo $cachedContent;
        }

        $this->_started = false;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $prefix
     * @return array
     */
    public function queryKeys($prefix = null)
    {
        if (!$prefix) {
            $prefix = $this->_prefix;
        } else {
            $prefix = $this->getPrefixedIdentifier($prefix);
        }

        $keys = [];

        $this->db->scan($this->namespace, $this->set, function ($record) use (&$keys, $prefix) {
            $keys[] = str_replace($prefix, '', $record['key']['key']);
        });

        return $keys;
    }

    /**
     * {@inheritdoc}
     *
     * @param int|string $keyName
     * @param int        $lifetime
     * @return mixed
     */
    public function get($keyName, $lifetime = null)
    {
        $prefixedKey    = $this->getPrefixedIdentifier($keyName);
        $aKey           = $this->buildKey($prefixedKey);
        $this->_lastKey = $prefixedKey;

        $status = $this->db->get($aKey, $cache);

        if ($status != AerospikeDb::OK) {
            return null;
        }

        $cachedContent = $cache['bins']['value'];

        if (is_numeric($cachedContent)) {
            return $cachedContent;
        }

        return $this->_frontend->afterRetrieve($cachedContent);
    }

    /**
     * {@inheritdoc}
     *
     * @param int|string $keyName
     * @return boolean
     */
    public function delete($keyName)
    {
        $prefixedKey    = $this->getPrefixedIdentifier($keyName);
        $aKey           = $this->buildKey($prefixedKey);
        $this->_lastKey = $prefixedKey;

        $status = $this->db->remove($aKey);

        return $status == AerospikeDb::OK;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $keyName
     * @param int    $lifetime
     * @return boolean
     */
    public function exists($keyName = null, $lifetime = null)
    {
        if ($keyName === null) {
            $prefixedKey = $this->_lastKey;
        } else {
            $prefixedKey = $this->getPrefixedIdentifier($keyName);
        }

        if (!$prefixedKey) {
            return false;
        }

        $aKey = $this->buildKey($prefixedKey);
        $status = $this->db->get($aKey, $cache);

        return $status == AerospikeDb::OK;
    }

    /**
     * Increment of given $keyName by $value
     *
     * @param  string $keyName
     * @param  int    $value
     * @return int|false
     */
    public function increment($keyName = null, $value = null)
    {
        if ($keyName === null) {
            $prefixedKey = $this->_lastKey;
        } else {
            $prefixedKey = $this->getPrefixedIdentifier($keyName);
        }

        if (!$prefixedKey) {
            return false;
        }

        $this->_lastKey = $prefixedKey;

        if (!$value) {
            $value = 1;
        }

        $aKey = $this->buildKey($prefixedKey);
        $this->db->increment($aKey, 'value', $value);

        $status = $this->db->get($aKey, $cache);

        if ($status != AerospikeDb::OK) {
            return false;
        }

        return $cache['bins']['value'];
    }

    /**
     * Decrement of $keyName by given $value
     *
     * @param  string $keyName
     * @param  int    $value
     * @return int|false
     */
    public function decrement($keyName = null, $value = null)
    {
        if (!$value) {
            $value = -1;
        } elseif ($value > 0) {
            $value = -1 * abs($value);
        }

        return $this->increment($keyName, $value);
    }

    /**
     * Generates a unique key used for storing cache data in Aerospike DB.
     *
     * @param string $key Cache key
     * @return array
     */
    protected function buildKey($key)
    {
        return $this->db->initKey(
            $this->namespace,
            $this->set,
            $key
        );
    }
}
