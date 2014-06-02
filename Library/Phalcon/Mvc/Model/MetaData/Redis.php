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

use Phalcon\Cache\Backend\Redis as CacheBackend;
use Phalcon\Cache\Frontend\Data as CacheFrontend;
use Phalcon\Mvc\Model\Exception;

/**
 * \Phalcon\Mvc\Model\MetaData\Redis
 * Redis adapter for \Phalcon\Mvc\Model\MetaData
 */
class Redis extends Base
{

    /**
     * Redis backend instance.
     *
     * @var \Phalcon\Cache\Backend\Redis
     */
    protected $redis = null;

    /**
     * {@inheritdoc}
     *
     * @param  null|array                   $options
     * @throws \Phalcon\Mvc\Model\Exception
     */
    public function __construct($options = null)
    {
        if (is_array($options)) {
            if (!isset($options['redis'])) {
                throw new Exception('Parameter "redis" is required');
            }
        } else {
            throw new Exception('No configuration given');
        }

        parent::__construct($options);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Phalcon\Cache\Backend\Redis
     */
    protected function getCacheBackend()
    {
        if (null === $this->redis) {
            $this->redis = new CacheBackend(
                new CacheFrontend(array('lifetime' => $this->options['lifetime'])),
                array(
                    'redis' => $this->options['redis'],
                )
            );
        }

        return $this->redis;
    }
    
    /**
     * {@inheritdoc}
     * @param  string $key
     * @return array
     */
    public function read($key)
    {
        return parent::read($key) ?: null;
    }
    
    /**
     * Returns the sessionId with prefix
     *
     * @param  string $id
     * @return string
     */
    protected function getId($id)
    {
        return str_replace('\\', ':', parent::getId($id));
    }
}
