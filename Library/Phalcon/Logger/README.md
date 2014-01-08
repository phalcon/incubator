Phalcon\Logger\Adapter
======================

Usage examples of the adapters available here:

Database
--------
Adapter to store logs in a database table:

```php

$di->set('logger', function() {

	$connection = new \Phalcon\Db\Adapter\Pdo\Mysql(array(
		"host" => "localhost",
		"username" => "root",
		"password" => "secret",
		"dbname" => "audit"
	));

	$logger = new Phalcon\Logger\Adapter\Database('errors', array(
		'db' => $connection,
		'table' => 'logs'
	));

	return $logger;
});

```

The following table is used to store the logs:

```sql
CREATE TABLE `logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) DEFAULT NULL,
  `type` int(3) NOT NULL,
  `content` text,
  `created_at` int(18) unsigned NOT NULL,
  PRIMARY KEY (`id`)
)
```

Firephp
-------
Adapter to send messages to [Firebug](https://getfirebug.com/). You need
the [Firephp](http://www.firephp.org/) extension installed in your browser.

```php
$logger = new Phalcon\Logger\Adapter\Firephp('debug', null);

$logger->log('Plain Message');
$logger->info('Info Message');
$logger->warning('Warn Message');
$logger->error('Error Message');
```

