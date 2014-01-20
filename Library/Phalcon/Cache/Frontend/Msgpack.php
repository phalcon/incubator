<?php

namespace Phalcon\Cache\Frontend;

use Phalcon\Cache\FrontendInterface;
use Phalcon\Cache\Exception;

/**
 * Class Msgpack
 * @package Phalcon\Cache\Frontend
 *
 * @author Yoshihiro Misawa
 */
class Msgpack implements FrontendInterface
{

    protected $frontendOptions;

    /**
     * Phalcon\Cache\Frontend\Msgpack constructor
     *
     * @param array $frontendOptions
     */
    public function __construct($frontendOptions = null)
    {
        $this->frontendOptions = $frontendOptions;
    }

    /**
     * Returns the cache lifetime
     *
     * @return int
     */
    public function getLifetime()
    {
        $options = $this->frontendOptions;
        if (isset($options['lifetime'])) {
            return $options['lifetime'];
        }
        return 1;
    }

    /**
     * Check whether if frontend is buffering output
     *
     * @return boolean
     */
    public function isBuffering()
    {
        return false;
    }

    /**
     * Starts output frontend. Actually, does nothing
     */
    public function start()
    {
    }

    /**
     * Returns output cached content
     *
     * @return string
     */
    public function getContent()
    {
        return null;
    }

    /**
     * Stops output frontend
     */
    public function stop()
    {
    }

    /**
     * Serializes data before storing them
     *
     * @param mixed $data
     * @return string
     */
    public function beforeStore($data)
    {
        return msgpack_pack($data);
    }

    /**
     * Unserializes data after retrieval
     *
     * @param mixed $data
     * @return mixed
     */
    public function afterRetrieve($data)
    {
        return msgpack_unpack($data);
    }
}
