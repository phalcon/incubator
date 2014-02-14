<?php
namespace Phalcon\Logger\Adapter;

class Memcache extends \Phalcon\Logger\Adapter
{
    /**
     * Memcache backend
     * @var \Phalcon\Cache\Backend\Memcache
     */
    private $memcache;

    /**
     * Cache key to be used
     * @var string
     */
    private $cacheKey = 'memache.logger';

    /**
     * Cache ttl
     * @var int
     */
    private $ttl = 0;

    /**
     * Constructor. Sets memcache instance and optional ttl and cacheKey.
     *
     * @param \Phalcon\Cache\Backend\Memcache $memcache memcache backend
     * @param int                             $ttl      ttl in seconds
     * @param string                          $cacheKey cache key to be used
     */
    public function __construct(\Phalcon\Cache\Backend\Memcache $memcache, $ttl = 0, $cacheKey = 'memcache.logger')
    {
        $this->memcache = $memcache;
        $this->ttl = $ttl;
        $this->cacheKey = $cacheKey;
    }

    /**
     * Logs to memcache backend instance.
     *
     * @param       $message   message
     * @param       $type      log priority
     * @param       $timestamp timestamp
     * @param array $context   context
     *
     * @throws \Phalcon\Logger\Exception if Json (currently only supported) backend is not set
     *
     * @return mixed return value of $memcache->save() method
     */
    public function logInternal($message, $type, $timestamp, $context = array())
    {
        $formatter = $this->getFormatter();
        if (!$formatter instanceof \Phalcon\Logger\Formatter\Json) {
            throw new \Phalcon\Logger\Exception('Only Json formatter is supported with memcache logger adapter.');
        }
        $item = $formatter->format($message, $type, $timestamp);

        $previousItems = $this->memcache->get($this->cacheKey);

        // add break to improve human readability
        $allItems = $previousItems . PHP_EOL . $item;

        return $this->memcache->save($this->cacheKey, $allItems, $this->ttl);
    }


    /**
     * Returns memcache backend instance in use.
     *
     * @return \Phalcon\Cache\Backend\Memcache
     */
    public function getMemcache()
    {
        return $this->memcache;
    }

    /**
     * Returns cache key which is used for storing logs.
     *
     * @return string
     */
    public function getCacheKey()
    {
        return $this->cacheKey;
    }

    /**
     * Returns ttl in seconds.
     *
     * @return int
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * Returns the internal formatter
     *
     * @return \Phalcon\Logger\FormatterInterface
     */
    public function getFormatter()
    {
        return $this->_formatter;
    }
}