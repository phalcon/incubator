Phalcon\Cache\Frontend
=====================

Usage examples of the adapters available here:

Msgpack
-------

This adapter uses a Msgpack frontend to store the cached content and requires [msgpack-php](https://github.com/msgpack/msgpack-php) extension:

```php
$di->set('cache', function() {

    // Create a Data frontend and set a default lifetime to 1 hour
    $frontend = new Phalcon\Cache\Frontend\Msgpack([
        'lifetime' => 3600
    ]);

    $cache = new Phalcon\Cache\Backend\Memory($frontend);

    return $cache;
});
```
