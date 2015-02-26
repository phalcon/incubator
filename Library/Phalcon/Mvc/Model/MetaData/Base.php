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
 * @author Ilya Gusev <mail@igusev.ru>
 */
namespace Phalcon\Mvc\Model\MetaData;

use Phalcon\Mvc\Model\MetaData;

/**
 * \Phalcon\Mvc\Model\MetaData\Base
 * Base class for \Phalcon\Mvc\Model\MetaData\Memcache and \Phalcon\Mvc\Model\MetaData\Redis adapters.
 */
abstract class Base extends MetaData {

    /**
     * Default options for cache backend.
     *
     * @var array
     */
    protected static $defaults = array(
        'lifetime' => 8600,
        'prefix'   => '',
    );

    /**
     * Backend's options.
     *
     * @var array
     */
    protected $options = null;

    /**
     * Class constructor.
     *
     * @param  null|array                   $options
     * @throws \Phalcon\Mvc\Model\Exception
     */
    public function __construct($options = null)
    {
        if (is_array($options)) {
            if (!isset($options['lifetime'])) {
                $options['lifetime'] = self::$defaults['lifetime'];
            }

            if (!isset($options['prefix'])) {
                $options['prefix'] = self::$defaults['prefix'];
            }
        }

        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     * @param  string $key
     * @return array
     */
    public function read($key)
    {
        return $this->getCacheBackend()->get($this->prepareKey($key), $this->options['lifetime']);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $key
     * @param array  $data
     */
    public function write($key, $data)
    {
        $this->getCacheBackend()->save($this->prepareKey($key), $data, $this->options['lifetime']);
    }

    /**
     * Returns the key with a prefix or other changes
     *
     * @param  string $key
     * @return string
     */
    protected function prepareKey($key)
    {
        return $this->options['prefix'] . $key;
    }

    /**
     * Returns cache backend instance.
     *
     * @return \Phalcon\Cache\BackendInterface
     */
    abstract protected function getCacheBackend();
}
