# Phalcon\Acl\Factory

## Phalcon\Acl\Factory\Memory

This factory is intended to be used to ease setup of `\Phalcon\Acl\Adapter\Memory`
in case `\Phalcon\Config` or one of its adapters is used for configuration.

To setup `acl` service in DI `service.php` file using `acl.ini` file:
(example of structure and options in ini file can be found in [tests/_fixtures/Acl/acl.ini](tests/_fixtures/Acl/acl.ini)

```php
use Phalcon\Config\Adapter\Ini as ConfigIni;
use Phalcon\Acl\Factory\Memory as AclMemory;

$di->setShared(
    'acl'
    function () {
        $config  = new ConfigIni(APP_PATH . '/config/acl.ini');
        $factory = new AclMemory();

        // returns instance of \Phalcon\Acl\Adapter\Memory
        // note the [acl] section in ini file
        return $factory->create(
            $config->get('acl')
        );
    }
);
```

To setup `acl` service in DI `service.php` file using `acl.php` (array) file:
(example of structure and options in ini file can be found in [tests/_fixtures/Acl/acl.php](tests/_fixtures/Acl/acl.php)

```php
use Phalcon\Config;
use Phalcon\Acl\Factory\Memory as AclMemory;

$di->setShared(
    'acl'
    function () {
        $config  = new Config(APP_PATH . '/config/acl.php');
        $factory = new AclMemory();

        // returns instance of \Phalcon\Acl\Adapter\Memory
        return $factory->create($config);
    }
);
```
