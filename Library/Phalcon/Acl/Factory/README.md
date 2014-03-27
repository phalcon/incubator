
Phalcon\Acl\Factory
===================

Phalcon\Acl\Factory\Memory
__________________________
This factory is intended to be used to ease setup of \Phalcon\Acl\Adapter\Memory
in case \Phalcon\Config or one of its adapters is used for configuration.

To setup acl service in DI service.php file using acl.ini file:
(example of structure and options in ini file can be found in [tests/Phalcon\Acl\Factory\_fixtures\acl.ini](https://github.com/phalcon/incubator/blob/master/tests/Phalcon/Acl/Factory/_fixtures/acl.ini))

```php
<?php
$di['acl'] = function () {
    $aclIniConfig = new \Phalcon\Config\Adapter\Ini(__DIR__ . '/acl.ini');
    $factory = new \Phalcon\Acl\Factory\Memory();
    // returns instance of \Phalcon\Acl\Adapter\Memory
    return $factory->create($aclIniConfig->get('acl')); // note the [acl] section in ini file
}
?>
```

To setup acl service in DI service.php file using acl.php (array) file:
(example of structure and options in ini file can be found in [tests/Phalcon\Acl\Factory\_fixtures\acl.php](https://github.com/phalcon/incubator/blob/master/tests/Phalcon/Acl/Factory/_fixtures/acl.php))

```php
<?php
$di['acl'] = function () use ($di) {
    $aclPhpConfig = new \Phalcon\Config\Adapter\Php(__DIR__ . '/acl.php');
    $factory = new \Phalcon\Acl\Factory\Memory();
    // returns instance of \Phalcon\Acl\Adapter\Memory
    return $factory->create($aclPhpConfig);
}
?>
```