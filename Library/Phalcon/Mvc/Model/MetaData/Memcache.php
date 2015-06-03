<?php
/**
 * Phalcon Framework
 * This source file is subject to the New BSD License that is bundled
 * with this package in the file docs/LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@phalconphp.com so we can send you a copy immediately.
 *
 * @author Nikita Vershinin <endeveit@gmail.com>
 */
namespace Phalcon\Mvc\Model\MetaData;

use Phalcon\Cache\Backend\Memcache as CacheBackend;
use Phalcon\Cache\Frontend\Data as CacheFrontend;
use Phalcon\Mvc\Model\Exception;

/**
 * \Phalcon\Mvc\Model\MetaData\Memcache
 * Memcache adapter for \Phalcon\Mvc\Model\MetaData
 */
class Memcache extends Base
{

    /**
     * Default option for memcache port.
     *
     * @var array
     */
    protected static $defaultPort = 11211;

    /**
     * Default option for persistent session.
     *
     * @var boolean
     */
    protected static $defaultPersistent = false;

    /**
     * Memcache backend instance.
     *
     * @var \Phalcon\Cache\Backend\Memcache
     */
    protected $memcache = null;

    /**
     * {@inheritdoc}
     *
     * @param  null|array                   $options
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

        if (!isset($options['persistent'])) {
            $options['persistent'] = self::$defaultPersistent;
        }

        parent::__construct($options);
    }

    /**
     * {@inheritdoc}
     * @return \Phalcon\Cache\Backend\Memcache
     */
    protected function getCacheBackend()
    {
        if (null === $this->memcache) {
            $this->memcache = new CacheBackend(
                new CacheFrontend(array('lifetime' => $this->options['lifetime'])),
                array(
                    'host'       => $this->options['host'],
                    'port'       => $this->options['port'],
                    'persistent' => $this->options['persistent'],
                )
            );
        }

        return $this->memcache;
    }
}
