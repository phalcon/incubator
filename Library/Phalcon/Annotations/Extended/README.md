# Phalcon\Annotations\Extended\Adapter

The main goals of this package:

* Extended `AdapterInterface` with methods `read`, `write` and `flush`
* Work only with `Phalcon\Annotations\Reflection` (`read` and `write`)
* Ability to set custom `statsKey`
* Work separately from current `Phalcon\Cache\BackendInterface`

In the future a set of these adapters will be part of the Phalcon Framework.
Usage examples of the adapters available here:

## Apc

Stores the parsed annotations in the [Alternative PHP Cache (APC)](http://php.net/manual/en/intro.apcu.php)
using either _APCu_ or _APC_ extension. This adapter is suitable for production.

```php
use Phalcon\Annotations\Extended\Adapter\Apc;

$di->set('annotations', function () {
    return new Apc([
        'lifetime' => 8600,               // Optional
        'statsSey' => '_PHAN',            // Optional
        'prefix'   => 'app-annotations-', // Optional
    ]);
});
```

## Memory

Stores the parsed annotations in the memory. This adapter is the suitable development/testing.

```php
use Phalcon\Annotations\Extended\Adapter\Memory;

$di->set('annotations', function () {
    return new Memory();
});
```

## Files

Stores the parsed annotations in files. This adapter is suitable for production.

```php
use Phalcon\Annotations\Adapter\Files;

$annotations = new Files(
    [
        "annotationsDir" => "app/cache/annotations/",
    ]
);
```
