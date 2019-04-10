# Phalcon\Annotations\Adapter

Usage examples of the adapters available here:

## Memcached

Stores the parsed annotations to Memcached.
This adapter uses a `Phalcon\Cache\Backend\Libmemcached` backend to store the cached content:

```php
use Phalcon\Annotations\Adapter\Memcached;

$di->set(
    'annotations',
    function () {
        return new Memcached(
            [
                'lifetime' => 8600,
                'host'     => 'localhost',
                'port'     => 11211,
                'weight'   => 1,
                'prefix'   => 'prefix.',
            ]
        );
    }
);
```

## Redis

Stores the parsed annotations to Redis.
This adapter uses a `Phalcon\Cache\Backend\Redis` backend to store the cached content:

```php
use Phalcon\Annotations\Adapter\Redis;

$di->set(
    'annotations',
    function () {
        return new Redis(
            [
                'lifetime' => 8600,
                'host'     => 'localhost',
                'port'     => 6379,
                'prefix'   => 'annotations_',
            ]
        );
    }
);
```

## Aerospike

Stores the parsed annotations to the Aerospike database.
This adapter uses a `Phalcon\Cache\Backend\Aerospike` backend to store the cached content:

```php
use Phalcon\Annotations\Adapter\Aerospike;

$di->set(
    'annotations',
    function () {
        return new Aerospike(
            [
                'hosts' => [
                    [
                        'addr' => '127.0.0.1',
                        'port' => 3000,
                    ],
                ],
                'persistent' => true,
                'namespace'  => 'test',
                'prefix'     => 'annotations_',
                'lifetime'   => 8600,
                'options'    => [
                    \Aerospike::OPT_CONNECT_TIMEOUT => 1250,
                    \Aerospike::OPT_WRITE_TIMEOUT   => 1500,
                ],
            ]
        );
    }
);
```
