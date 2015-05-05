Phalcon\Session\Adapter
=======================

Usage examples of the adapters available here:

Database
--------
This adapter uses a database backend to store session data:

```php

$di->set('session', function() {

	// Create a connection
	$connection = new \Phalcon\Db\Adapter\Pdo\Mysql(array(
	    "host" => "localhost",
	    "username" => "root",
	    "password" => "secret",
	    "dbname" => "test"
	));

	$session = new Phalcon\Session\Adapter\Database(array(
		'db' => $connection,
		'table' => 'session_data'
	));

	$session->start();

	return $session;
});

```

This adapter uses the following table to store the data:

```sql
 CREATE TABLE `session_data` (
  `session_id` varchar(35) NOT NULL,
  `data` text NOT NULL,
  `created_at` int(15) unsigned NOT NULL,
  `modified_at` int(15) unsigned DEFAULT NULL,
  PRIMARY KEY (`session_id`)
)
```

Mongo
-----
This adapter uses a Mongo database backend to store session data:

```php

$di->set('session', function() {

	//Create a connection to mongo
	$mongo = new Mongo();

	//Passing a collection to the adapter
	$session = new Phalcon\Session\Adapter\Mongo(array(
	    'collection' => $mongo->test->session_data
	));

	$session->start();

	return $session;
});

```

Redis
-----

This adapter uses a [Redis](http://redis.io) backend to store session data.
You would need a [phpredis](https://github.com/nicolasff/phpredis) extension installed to use it:

```php

$di->set('session', function() {

	$session = new Phalcon\Session\Adapter\Redis(array(
		'path' => "tcp://127.0.0.1:6379?weight=1"
	));

	$session->start();

	return $session;
});

```

HandlerSocket
-------------

This adapter uses the MySQL's plugin HandlerSocket. HandlerSocket is a NoSQL plugin for MySQL. It works as a daemon inside the
mysqld process, accept tcp connections, and execute requests from clients. HandlerSocket does not support SQL queries.
Instead, it supports simple CRUD operations on tables.

```sql
CREATE TABLE `php_session` (
 	`id`       varchar(32) NOT NULL DEFAULT '',
 	`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 	`data`     text,
 	PRIMARY KEY (`id`),
 	KEY `modified` (`modified`)
 ) ENGINE=InnoDB DEFAULT CHARACTER SET utf8;
 ```

 ```php

$di->set('session', function() {

	$session = new Phalcon\Session\Adapter\HandlerSocket(array(
	        'cookie_path' => '/',
	        'cookie_domain' => '',
	        'lifetime' => 3600,
	        'server' => array(
				'host' => 'localhost',
				'port' => 9999,
				'dbname' => 'session',
				'dbtable' => 'php_session'
			)
	));

	$session->start();

	return $session;
});

```

The extension handlersocket (https://github.com/kjdev/php-ext-handlersocketi/) is required to use this adapter.
