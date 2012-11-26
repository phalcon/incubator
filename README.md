Phalcon Incubator
=================

Phalcon PHP is a web framework delivered as a C extension providing high
performance and lower resource consumption.

This is repository to publish/share/experimient with new adapters, prototypes
or functionality that potentially can be incorporated to the C-framework.

The code in this repository is written in PHP.

Autoloading from the Incubator
------------------------------
Add or register the following namespace strategy to your Phalcon\Loader to load classes from
the incubator:

```php

$loader = new Phalcon\Loader();

$loader->registerNamespaces(array(
	'Phalcon' => '/path/to/incubator/Library/Phalcon/'
));

$loader->register();

```
