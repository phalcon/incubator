# Phalcon\Mvc\Model\MetaData

Usage examples of the adapters available here:

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