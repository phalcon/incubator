<?php
namespace Phalcon\Cache\Backend;

use Memcache as NativeMemcache;
use Phalcon\Cache\Backend;
use Phalcon\Cache\BackendInterface;
use Phalcon\Cache\Exception;

/**
 * Class Memcache
 *
 * @package Phalcon\Cache\Backend
 * @author  Tobias Plaputta, Affinitas GmbH
 *          Released under the MIT license. http://nischenspringer.de/license
 */
class Memcache extends Backend implements BackendInterface
{
    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT = 11211;
    const DEFAULT_PERSISTENT = true;
    const DEFAULT_WEIGHT = 1;
    const DEFAULT_TIMEOUT = 1;
    const DEFAULT_RETRY_INTERVAL = 10;
    const DEFAULT_TRACKING_KEY = '_LMTD';
    const DEFAULT_TRACKING = false;

    /**
     * @var \Memcache
     */
    protected $memcache;

    /**
     * Class constructor.
     *
     * @param \Phalcon\Cache\FrontendInterface $frontend
     * @param array                            $options
     */
    public function __construct($frontend, $options = null)
    {
        $this->_memcache = new NativeMemcache();

        if (!isset($options['servers'])) {
            $options['servers'] = array(
                array(
                    'host' => self::DEFAULT_HOST
                )
            );
        }

        foreach ($options['servers'] as $server) {
            if (!array_key_exists('port', $server)) {
                $server['port'] = self::DEFAULT_PORT;
            }
            if (!array_key_exists('persistent', $server)) {
                $server['persistent'] = self::DEFAULT_PERSISTENT;
            }
            if (!array_key_exists('weight', $server)) {
                $server['weight'] = self::DEFAULT_WEIGHT;
            }
            if (!array_key_exists('timeout', $server)) {
                $server['timeout'] = self::DEFAULT_TIMEOUT;
            }
            if (!array_key_exists('retry_interval', $server)) {
                $server['retry_interval'] = self::DEFAULT_RETRY_INTERVAL;
            }
            $this->_memcache->addServer(
                $server['host'],
                $server['port'],
                $server['persistent'],
                $server['weight'],
                $server['timeout'],
                $server['retry_interval']
            );
        }

        if (!isset($options['tracking'])) {
            $options['tracking'] = self::DEFAULT_TRACKING;
        }

        if (!isset($options['tracking_key'])) {
            $options['tracking_key'] = self::DEFAULT_TRACKING_KEY;
        }

        parent::__construct($frontend, $options);
    }

    /**
     * {@inheritdoc}
     *
     * @param  string  $keyName
     * @param  integer $lifetime
     * @return mixed
     */
    public function get($keyName, $lifetime = null)
    {
        $tmp = $this->_memcache->get($this->getPrefixedKey($keyName));
        if (is_array($tmp) && isset($tmp[0])) {
            $frontend = $this->getFrontend();

            $this->setLastKey($keyName);

            return $frontend->afterRetrieve($tmp[0]);
        }

        return null;
    }

    /**
     * S{@inheritdoc}
     *
     * @param  string                   $keyName
     * @param  string                   $content
     * @param  integer                  $lifetime
     * @param  boolean                  $stopBuffer
     * @return boolean
     * @throws \Phalcon\Cache\Exception
     */
    public function save($keyName = null, $content = null, $lifetime = null, $stopBuffer = true)
    {
        if ($keyName === null) {
            $lastKey = $this->_lastKey;
        } else {
            $lastKey = $keyName;
        }

        if (!$lastKey) {
            throw new Exception('The cache must be started first.', E_ERROR);
        }

        $frontend = $this->getFrontend();

        if ($content === null) {
            $content = $frontend->getContent();
        }

        //Get the lifetime from the frontend
        if ($lifetime === null) {
            $lifetime = $frontend->getLifetime();
        }

        $time = time();

        // Add tracking if enabled.
        $this->addTracking($keyName, $time, $lifetime);

        // Using set because add needs a second request if item already exists
        $result = @$this->_memcache->set(
            $this->getPrefixedKey($keyName),
            array($frontend->beforeStore($content), $time, $lifetime),
            0,
            $lifetime
        );

        $isBuffering = $frontend->isBuffering();

        // Stop the buffer, this only applies for Phalcon\Cache\Frontend\Output
        if ($stopBuffer) {
            $frontend->stop();
        }

        // Print the buffer, this only applies for Phalcon\Cache\Frontend\Output
        if ($isBuffering) {
            echo $content;
        }

        $this->_started = false;

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @param  string  $keyName
     * @return boolean
     */
    public function delete($keyName)
    {
        // Remove tracking for this key if enabled.
        $this->removeTracking($keyName, time());

        return $this->_memcache->delete($this->getPrefixedKey($keyName), 0);
    }

    /**
     * Unsupported.
     * Memcache::getAllKeys() queries each memcache server and retrieves an array of all keys stored
     * on them at that point in time.
     * This is not an atomic operation, so it isn't a truly consistent snapshot of the keys at point in time.
     * As memcache doesn't guarantee to return all keys you also cannot assume that all keys have been returned.
     * Therefore it is not used in this adapter. See listKeys.
     *
     * @see \Phalcon\Cache\Backend\Memcache::listKeys()
     * @param  string                   $prefix
     * @return array
     * @throws \Phalcon\Cache\Exception
     */
    public function queryKeys($prefix = null)
    {
        throw new Exception("Method queryKeys is not supported in Libmemcached backend adapter!", E_ERROR);
    }

    /**
     * Query the existing cached keys in this instance.
     * For performance reasons we do not remove keys from this item when they time out, therefor you should not rely
     * on the keys returned by this method being existent.
     * Only works if tracking is enabled.
     *
     * @return array
     * @throws \Phalcon\Cache\Exception
     */
    public function listKeys()
    {
        $options = $this->getOptions();
        if (!$options['tracking']) {
            throw new Exception("Tracking must be enabled to support key listing!", E_ERROR);
        }

        $tmp = $this->_memcache->get($this->getPrefixedKey($options['tracking_key']));
        if (is_array($tmp) && isset($tmp[0])) {
            return $tmp[0];
        }

        return array();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Phalcon\Cache\Exception
     */
    public function clean()
    {
        $options = $this->getOptions();
        if (!$options['tracking']) {
            throw new Exception("Tracking must be enabled to support clean!", E_ERROR);
        }

        foreach ($this->listKeys() as $key) {
            $this->delete($key);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param  string  $keyName
     * @param  string  $lifetime
     * @return boolean
     */
    public function exists($keyName = null, $lifetime = null)
    {
        $tmp = $this->_memcache->get($this->getPrefixedKey($keyName));
        if (is_array($tmp)) {
            return true;
        }

        return false;
    }

    /**
     * Checks if cache exists and is set to a value that represents a boolean true.
     *
     * @param  string  $keyName
     * @param  string  $lifetime
     * @return boolean
     */
    public function isValid($keyName = null, $lifetime = null)
    {
        return ($this->exists($keyName, $lifetime) && $this->get($keyName, $lifetime));
    }

    /**
     * Adds a prefix to the key if set.
     *
     * @param  string $keyName
     * @return string
     */
    protected function getPrefixedKey($keyName)
    {
        $options = $this->getOptions();

        if (!isset($options['prefix'])) {
            return $keyName;
        }

        return $options['prefix'] . $keyName;
    }

    /**
     * Adds the key to our tracking item if tracking is enabled.
     *
     * @param  string  $keyName
     * @param  integer $time
     * @param  integer $lifetime
     * @return boolean
     */
    protected function addTracking($keyName, $time, $lifetime)
    {
        $options = $this->getOptions();
        if (!$options['tracking']) {
            return false;
        }

        $trackingData = $this->_memcache->get($this->getPrefixedKey($options['tracking_key']));
        if (!is_array($trackingData) || !isset($trackingData[0])) {
            $trackingData = array(array($keyName), $time, $lifetime);
        }

        // If the remaining lifetime is shorter than the lifetime of this key, increase it.
        // Since the tracking key needs to be updated, the new lifetime has to be recalculated.
        if (!in_array($keyName, $trackingData[0])) {
            $trackingData[0][] = $keyName;
        }

        $trackingData[1] = $time;
        $trackingData[2] = max($lifetime, ($trackingData[2] + $trackingData[1]) - $time);

        // Using set instead of add to avoid two function calls.
        return @$this->_memcache->set(
            $this->getPrefixedKey($options['tracking_key']),
            $trackingData,
            0,
            $trackingData[2]
        );
    }

    /**
     * Removes a key from our tracking item if tracking is enabled (used if a key is removed).
     *
     * @param  string  $keyName
     * @param  integer $time
     * @return boolean
     */
    protected function removeTracking($keyName, $time)
    {
        $options = $this->getOptions();
        if (!$options['tracking']) {
            return false;
        }

        $trackingData = $this->_memcache->get($this->getPrefixedKey($options['tracking_key']));
        if (!is_array($trackingData) || !isset($trackingData[0])) {
            return false;
        }

        // Since the tracking key needs to be updated, the new lifetime has to be recalculated.
        $pos = array_search($keyName, $trackingData[0]);
        if ($pos === false) {
            return false;
        }

        unset($trackingData[0][$pos]);

        $trackingData[1] = $time;
        $trackingData[2] = ($trackingData[2] + $trackingData[1]) - $time;

        return @$this->_memcache->set(
            $this->getPrefixedKey($options['tracking_key']),
            $trackingData,
            0,
            $trackingData[2]
        );
    }
}
