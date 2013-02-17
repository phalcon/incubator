Phalcon\Db\Adapter
=================

Usage examples of the adapters available here:

Cacheable\Mysql
---------------
Implements an agressive cache. Every query performed is cached with the same lifetime.
This adapter is specially suitable for applications with very few inserts/updates/deletes.

```php

$di->set('db', function() use ($config) {

	$connection = new \Phalcon\Adapter\Cacheable\Mysql(array(
		"host" => $config->database->host,
		"username" => $config->database->username,
		"password" => $config->database->password,
		"dbname" => $config->database->name,
		"options" => array(
			\PDO::ATTR_EMULATE_PREPARES => false
		)
	));

	$frontCache = new \Phalcon\Cache\Frontend\Data(array(
		"lifetime" => 2592000
	));

	//File backend settings
	$connection->setCache(new \Phalcon\Cache\Backend\File($frontCache, array(
		"cacheDir" => __DIR__ . "/../../var/db/",
	)));

	return $connection;
});

```