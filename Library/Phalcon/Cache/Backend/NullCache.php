<?php

namespace Phalcon\Cache\Backend;

use Phalcon\Cache\BackendInterface;
use Phalcon\Cache\Frontend\None as NoneFrontend;

/**
 * A cache adapter that does nothing.
 */
final class NullCache implements BackendInterface
{
    public function start($keyName, $lifetime = null)
    {
        return true;
    }

    public function stop($stopBuffer = true)
    {
    }

    public function getFrontend()
    {
        return NoneFrontend();
    }

    public function getOptions()
    {
        return [];
    }

    public function isFresh()
    {
        return true;
    }

    public function isStarted()
    {
        return true;
    }

    public function setLastKey($lastKey)
    {
    }

    public function getLastKey()
    {
        return '';
    }

    public function get($keyName, $lifetime = null)
    {
        return null;
    }

    public function save($keyName = null, $content = null, $lifetime = null, $stopBuffer = true)
    {
        return true;
    }

    public function delete($keyName)
    {
        return true;
    }

    public function queryKeys($prefix = null)
    {
        return [];
    }

    public function exists($keyName = null, $lifetime = null)
    {
        return false;
    }
}
