# Phalcon\Annotations\Adapter

Usage examples of the adapters available here:

## Memcached

This adapter uses a Libmemcached backend to store the cached content:

```php
$di->set('annotations', function () {
    return new \Phalcon\Annotations\Adapter\Memcached([
        'lifetime' => 8600,
        'host'     => 'localhost',
        'port'     => 11211,
        'weight'   => 1,
        'prefix'   => 'prefix.',
    ]);
});
```
