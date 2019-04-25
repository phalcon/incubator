<?php

namespace Phalcon\Cache\Backend;

use Phalcon\Cache\BackendInterface;
use Phalcon\Cache\Frontend\None as NoneFrontend;
use Phalcon\Cache\FrontendInterface;

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

    public function getFrontend(): FrontendInterface
    {
        return new NoneFrontend();
    }

    public function getOptions(): array
    {
        return [];
    }

    public function isFresh(): bool
    {
        return true;
    }

    public function isStarted(): bool
    {
        return true;
    }

    public function setLastKey($lastKey)
    {
    }

    public function getLastKey(): string
    {
        return '';
    }

    public function get($keyName, $lifetime = null)
    {
        return null;
    }

    public function save($keyName = null, $content = null, $lifetime = null, $stopBuffer = true): bool
    {
        return true;
    }

    public function delete($keyName): bool
    {
        return true;
    }

    public function queryKeys($prefix = null): array
    {
        return [];
    }

    public function exists($keyName = null, $lifetime = null): bool
    {
        return false;
    }
}
