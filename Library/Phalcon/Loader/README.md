# Phalcon\Loader

## Phalcon\Loader\Extended

This component extends [Phalcon\Loader][1] and added ability to
set multiple directories per namespace.

```php
use Phalcon\Loader\Extended as Loader;

// Creates the autoloader
$loader = new Loader();

// Register some namespaces
$loader->registerNamespaces(
    [
        'Example\Base' => 'vendor/example/base/',
        'Some\Adapters' => [
            'vendor/example/adapters/src/',
            'vendor/example/adapters/test/',
        ]
    ]
);

// Register autoloader
$loader->register();

// Requiring this class will automatically include
// file vendor/example/adapters/src/Some.php
$adapter = Example\Adapters\Some();

// Requiring this class will automatically include
// file vendor/example/adapters/test/Another.php
$adapter = Example\Adapters\Another();
```

## Phalcon\Loader\PSR

Implements [PSR-0][2] autoloader for your apps.

[1]: https://docs.phalconphp.com/en/latest/api/Phalcon_Loader.html
[2]: http://www.php-fig.org/psr/psr-0/
