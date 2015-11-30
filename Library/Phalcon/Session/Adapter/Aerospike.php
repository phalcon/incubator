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

namespace Phalcon\Session\Adapter;

use Aerospike as AerospikeDb;
use Phalcon\Session\Adapter;
use Phalcon\Session\AdapterInterface;
use Phalcon\Session\Exception;

/**
 * Phalcon\Session\Adapter\Aerospike
 *
 * This adapter store sessions in Aerospike
 *
 * <code>
 * use Phalcon\Session\Adapter\Aerospike as AerospikeSession;
 *
 * $session = new AerospikeSession([
 *     'hosts' => [
 *         ['addr' => '127.0.0.1', 'port' => 3000]
 *     ],
 *     'persistent' => true,
 *     'namespace'  => 'test',
 *     'prefix'     => 'session_',
 *     'lifetime'   => 8600,
 *     'uniqueId'   => '3Hf90KdjQ18',
 *     'options'    => [
 *         \Aerospike::OPT_CONNECT_TIMEOUT => 1250,
 *         \Aerospike::OPT_WRITE_TIMEOUT   => 1500
 *     ]
 * ]);
 *
 * $session->start();
 *
 * $session->set('var', 'some-value');
 *
 * echo $session->get('var');
 * </code>
 */
class Aerospike extends Adapter implements AdapterInterface
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
    protected $set = 'session';

    /**
     * Key prefix
     * @var string
     */
    protected $prefix = '';

    /**
     * Session lifetime
     * @var int
     */
    protected $lifetime = 8600;

    /**
     * Phalcon\Session\Adapter\Aerospike constructor
     *
     * @param array $options Constructor options
     * @throws Exception
     */
    public function __construct(array $options)
    {
        if (!isset($options['hosts']) || !is_array($options['hosts'])) {
            throw new Exception('No hosts given in options');
        }

        if (isset($options['namespace'])) {
            $this->namespace = $options['namespace'];
        }

        if (isset($options['prefix'])) {
            $this->prefix = $options['persistent'];
        } else {
            $this->prefix = substr(hash('sha256', uniqid(time(), true)), 0, 5);
        }

        if (isset($options['lifetime'])) {
            $this->lifetime = $options['lifetime'];
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

        parent::__construct($options);

        session_set_save_handler(
            [$this, 'open'],
            [$this, 'close'],
            [$this, 'read'],
            [$this, 'write'],
            [$this, 'destroy'],
            [$this, 'gc']
        );
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function open()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $sessionId Session variable name
     * @return string
     */
    public function read($sessionId)
    {
        $key = $this->buildKey($sessionId);
        $status = $this->db->get($key, $record);

        if ($status != AerospikeDb::OK) {
            error_log(sprintf('%s:%s:%s - %s', __CLASS__, __METHOD__, __LINE__, ''));
            return '';
        }

        return base64_decode($record['bins']['value']);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $sessionId Session variable name
     * @param string $data      Session data
     */
    public function write($sessionId, $data)
    {
        $key = $this->buildKey($sessionId);
        $bins = ['value' => base64_encode($data)];

        $this->db->put($key, $bins, $this->lifetime);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $sessionId Session variable name [Optional]
     * @return bool
     */
    public function destroy($sessionId = null)
    {
        $sessionId = $sessionId ?: $this->getId();
        $key = $this->buildKey($sessionId);

        $status = $this->db->remove($key);

        return $status == AerospikeDb::OK;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function gc()
    {
        return true;
    }

    /**
     * Generates a unique key used for storing session data in Aerospike DB.
     *
     * @param string $sessionId Session variable name
     * @return array
     */
    protected function buildKey($sessionId)
    {
        return $this->db->initKey(
            $this->namespace,
            $this->set,
            $this->prefix . md5(json_encode([__CLASS__, $sessionId]))
        );
    }
}
