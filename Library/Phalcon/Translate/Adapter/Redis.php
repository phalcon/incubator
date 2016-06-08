<?php
namespace Phalcon\Translate\Adapter;

use Phalcon\Translate\AdapterInterface;
use Phalcon\Translate\Exception;

/**
 * Class Redis
 *
 * @package Phalcon\Translate\Adapter
 */
class Redis extends Base implements AdapterInterface
{
    /**
     * Redis object.
     *
     * @var \Redis
     */
    protected $redis;

    /**
     * Language.
     *
     * @var string
     */
    protected $language;

    /**
     * How much containers to use in Redis for translations. Calculated with 16^$levels.
     *
     * @var integer
     */
    protected $levels = 2;

    /**
     * Local cache.
     *
     * @var array
     */
    protected $cache = [];

    /**
     * Class constructor.
     *
     * @param array $options
     * @throws \Phalcon\Translate\Exception
     */
    public function __construct(array $options)
    {
        if (!isset($options['redis'])) {
            throw new Exception("Parameter 'redis' is required");
        }

        if (!isset($options['language'])) {
            throw new Exception("Parameter 'language' is required");
        }

        $this->redis    = $options['redis'];
        $this->language = $options['language'];

        if (isset($options['levels'])) {
            $this->levels = $options['levels'];
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param  string  $translateKey
     * @return boolean
     */
    public function exists($translateKey)
    {
        $index = $this->getLongKey($translateKey);
        $key   = $this->getShortKey($index);

        $this->loadValueByKey($key);

        return (isset($this->cache[$key]) && isset($this->cache[$key][$index]));
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $translateKey
     * @param  array  $placeholders
     * @return string
     */
    public function query($translateKey, $placeholders = null)
    {
        $index = $this->getLongKey($translateKey);
        $key   = $this->getShortKey($index);

        $this->loadValueByKey($key);

        return isset($this->cache[$key]) && isset($this->cache[$key][$index])
            ? $this->cache[$key][$index]
            : $translateKey;
    }

    /**
     * Adds a translation for given key (No existence check!)
     *
     * @param  string  $translateKey
     * @param  string  $message
     * @return boolean
     */
    public function add($translateKey, $message)
    {
        $index = $this->getLongKey($translateKey);
        $key   = $this->getShortKey($index);

        $this->loadValueByKey($key);

        if (!isset($this->cache[$key])) {
            $this->cache[$key] = [];
        }

        $this->cache[$key][$index] = $message;

        return $this->redis->set($key, serialize($this->cache[$key]));
    }

    /**
     * Update a translation for given key (No existence check!)
     *
     * @param  string  $translateKey
     * @param  string  $message
     * @return boolean
     */
    public function update($translateKey, $message)
    {
        return $this->add($translateKey, $message);
    }

    /**
     * Deletes a translation for given key (No existence check!)
     *
     * @param  string  $translateKey
     * @return boolean
     */
    public function delete($translateKey)
    {
        $index    = $this->getLongKey($translateKey);
        $key      = $this->getShortKey($index);
        $nbResult = $this->redis->del($key);

        unset($this->cache[$key]);

        return $nbResult > 0;
    }

    /**
     * Sets (insert or updates) a translation for given key
     *
     * @param  string  $translateKey
     * @param  string  $message
     * @return boolean
     */
    public function set($translateKey, $message)
    {
        return $this->exists($translateKey) ?
            $this->update($translateKey, $message) : $this->add($translateKey, $message);
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $translateKey
     * @return string
     */
    public function offsetExists($translateKey)
    {
        return $this->exists($translateKey);
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $translateKey
     * @param  string $message
     * @return string
     */
    public function offsetSet($translateKey, $message)
    {
        return $this->update($translateKey, $message);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $translateKey
     * @return string
     */
    public function offsetGet($translateKey)
    {
        return $this->query($translateKey);
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $translateKey
     * @return string
     */
    public function offsetUnset($translateKey)
    {
        return $this->delete($translateKey);
    }

    /**
     * Loads key from Redis to local cache.
     *
     * @param string $key
     */
    protected function loadValueByKey($key)
    {
        if (!isset($this->cache[$key])) {
            $result = $this->redis->get($key);
            $result = unserialize($result);

            if (is_array($result)) {
                $this->cache[$key] = $result;
            }
        }
    }

    /**
     * Returns long key for index.
     *
     * @param  string $index
     * @return string
     */
    protected function getLongKey($index)
    {
        return md5($this->language . ':' . $index);
    }

    /**
     * Returns short key for index.
     *
     * @param  string $index
     * @return string
     */
    protected function getShortKey($index)
    {
        return substr($index, 0, $this->levels);
    }
}
