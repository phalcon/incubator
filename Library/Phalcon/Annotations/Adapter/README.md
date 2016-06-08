# Phalcon\Annotations\Adapter

Usage examples of the adapters available here:

## Memcached

Stores the parsed annotations to Memcached.
This adapter uses a `Phalcon\Cache\Backend\Libmemcached` backend to store the cached content:

```php
use Phalcon\Annotations\Adapter\Memcached;

$di->set('annotations', function () {
    return new Memcached([
        'lifetime' => 8600,
        'host'     => 'localhost',
        'port'     => 11211,
        'weight'   => 1,
        'prefix'   => 'prefix.',
    ]);
});
```

## Redis

Stores the parsed annotations to Redis.
This adapter uses a `Phalcon\Cache\Backend\Redis` backend to store the cached content:

```php
use Phalcon\Annotations\Adapter\Redis;

$di->set('annotations', function () {
    return new Redis([
        'lifetime' => 8600,
        'host'     => 'localhost',
        'port'     => 6379,
        'prefix'   => 'annotations_',
    ]);
});
```
