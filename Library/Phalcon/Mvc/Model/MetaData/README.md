# Phalcon\Mvc\Model\MetaData

Usage examples of the adapters available here:

## Memcache

This adapter uses a Memcache backend to store the cached content:

```php
$di->set('modelsMetadata', function ()
{
    return new \Phalcon\Mvc\Model\MetaData\Memcache(array(
        'lifetime'   => 8600,
        'host'       => 'localhost',
        'port'       => 11211,
        'persistent' => false,
    ));
});
```

## Memcached

This adapter uses a Libmemcached backend to store the cached content:

```php
$di->set('modelsMetadata', function ()
{
    return new \Phalcon\Mvc\Model\MetaData\Memcached(array(
        'lifetime' => 8600,
        'host'     => 'localhost',
        'port'     => 11211,
        'weight'   => 1,
        'prefix'   => 'prefix.',
    ));
});
```

## Redis

This adapter uses a [Redis](http://redis.io/) backend to store the cached content and [phpredis](https://github.com/phpredis/phpredis) extension:

```php
$di->set('modelsMetadata', function ()
{
    $redis = new Redis();
    $redis->connect('localhost', 6379);
    
    return new \Phalcon\Mvc\Model\MetaData\Redis(array(
        'redis' => $redis,
    ));
});
```

## Wincache

This adapter uses a Wincache backend to store the cached content:

```php
$di->set('modelsMetadata', function ()
{
    return new \Phalcon\Mvc\Model\MetaData\Wincache(array(
        'lifetime' => 8600,
    ));
});
```