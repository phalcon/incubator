<?php
/**
 * Phalcon Framework
 *
 * This source file is subject to the New BSD License that is bundled
 * with this package in the file docs/LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@phalconphp.com so we can send you a copy immediately.
 *
 * PHP version 5
 *
 * @category Phalcon
 * @package  Phalcon\Mvc\Model\MetaData
 * @author   Nikita Vershinin    <endeveit@gmail.com>
 * @author   Dmitriy Zhavoronkov <dimaz.lark@gmail.com>
 * @author   Ilya Gusev <mail@igusev.ru>
 * @license  New BSD License
 * @link     http://phalconphp.com/
 */
namespace Phalcon\Mvc\Model\MetaData;

use Phalcon\Cache\Backend\Libmemcached as CacheBackend;
use Phalcon\Cache\Frontend\Data as CacheFrontend;
use Phalcon\Mvc\Model\Exception;
use Memcached as MemcachedGeneric;

/**
 * \Phalcon\Mvc\Model\MetaData\Memcached
 * Memcached adapter for \Phalcon\Mvc\Model\MetaData
 *
 * @category Phalcon
 * @package  Phalcon\Mvc\Model\MetaData
 * @author   Nikita Vershinin    <endeveit@gmail.com>
 * @author   Dmitriy Zhavoronkov <dimaz.lark@gmail.com>
 * @license  New BSD License
 * @link     http://phalconphp.com/
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
     * @var \Phalcon\Cache\Backend\Libmemcached
     */
    protected $memcached = null;

    /**
     * {@inheritdoc}
     *
     * @param null|array $options options array
     *
     * @throws \Phalcon\Mvc\Model\Exception
     */
    public function __construct($options = null)
    {
        if (!is_array($options)) {
            throw new Exception('No configuration given');
        }

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
     * @return \Phalcon\Cache\Backend\Libmemcached
     */
    protected function getCacheBackend()
    {
        if (null === $this->memcached) {
            $this->memcached = new CacheBackend(
                new CacheFrontend(array('lifetime' => $this->options['lifetime'])),
                array(
                    'servers' => array(
                        array(
                            'host' => $this->options['host'],
                            'port' => $this->options['port'],
                            'weight' => $this->options['weight']
                        ),
                    ),
                    'client' => array(
                        MemcachedGeneric::OPT_HASH => MemcachedGeneric::HASH_MD5,
                        MemcachedGeneric::OPT_PREFIX_KEY => $this->options['prefix'],
                    )
                )
            );
        }

        return $this->memcached;
    }

    /**
     * {@inheritdoc}
     *
     * @value string $key
     *
     * @return string
     */
    protected function prepareKey($key)
    {
        return $key;
    }
}
