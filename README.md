Phalcon Incubator
=================

Phalcon PHP is a web framework delivered as a C extension providing high
performance and lower resource consumption.

This is a repository to publish/share/experimient with new adapters, prototypes
or functionality that potentially can be incorporated to the C-framework.

Also we welcome submissions from the community of snippets that could
extend the framework more.

The code in this repository is written in PHP.

Autoloading from the Incubator
------------------------------
Add or register the following namespace strategy to your Phalcon\Loader in order
to load classes from the incubator repository:

```php

$loader = new Phalcon\Loader();

$loader->registerNamespaces(array(
	'Phalcon' => '/path/to/incubator/Library/Phalcon/'
));

$loader->register();

```

Contributions Index
-------------------
[Phalcon\Cache\Backend\Database](https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Cache/Backend) - Database backend for caching data (phalcon)
[Phalcon\Mvc\Model\Behavior](https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Mvc/Model/Behavior) - ORM Behaviors (theDisco)
[Phalcon\Session\Adapter\Database](https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Session/Adapter) - Database adapter for storing sessions (phalcon)


