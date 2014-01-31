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

use Phalcon\Cache\Backend\Wincache as CacheBackend;
use Phalcon\Cache\Frontend\Data as CacheFrontend;
use Phalcon\Mvc\Model\Exception;

/**
 * \Phalcon\Mvc\Model\MetaData\Redis
 * Redis adapter for \Phalcon\Mvc\Model\MetaData
 */
class Wincache extends Base
{
    /**
     * Memcache backend instance.
     *
     * @var \Phalcon\Cache\Backend\Wincache
     */
    protected $wincache = null;

    /**
     * {@inheritdoc}
     *
     * @return \Phalcon\Cache\Backend\Wincache
     */
    protected function getCacheBackend()
    {
        if (null === $this->wincache) {
            $this->wincache = new CacheBackend(
                new CacheFrontend(array('lifetime' => $this->options['lifetime'])),
                array()
            );
        }

        return $this->wincache;
    }
}
