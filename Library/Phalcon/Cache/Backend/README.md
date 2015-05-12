Phalcon\Cache\Backend
=====================

Usage examples of the adapters available here:

Database
--------
This adapter uses a database backend to store the cached content:

```php

$di->set('cache', function() {

	// Create a connection
	$connection = new \Phalcon\Db\Adapter\Pdo\Mysql(array(
	    "host" => "localhost",
	    "username" => "root",
	    "password" => "secret",
	    "dbname" => "cache_db"
	));

	//Create a Data frontend and set a default lifetime to 1 hour
	$frontend = new Phalcon\Cache\Frontend\Data(array(
	    'lifetime' => 3600
	));

	//Create the cache passing the connection
	$cache = new Phalcon\Cache\Backend\Database($frontend, array(
		'db' => $connection,
		'table' => 'cache_data'
	));

	return $cache;
});

```

This adapter uses the following table to store the data:

```sql
 CREATE TABLE `cache_data` (
  `key_name` varchar(40) NOT NULL,
  `data` text,
  `lifetime` int(15) unsigned NOT NULL,
  PRIMARY KEY (`key_name`),
  KEY `lifetime` (`lifetime`)
)
```

Using the cache adapter:

```php

$time = $this->cache->get('le-time');
if ($time === null) {
    $time = date('r');
    $this->cache->save('le-time', $time);
}

echo $time;

```

Memcached
-----
This adapter uses a Memcache backend to store the cached content:

```php

$di->set('cache', function() {

	//Create a Data frontend and set a default lifetime to 1 hour
	$frontend = new Phalcon\Cache\Frontend\Data(array(
	    'lifetime' => 3600
	));

	// Set up Memcached and use tracking to be able to clean it later.
	// You should not use tracking if you're going to store a lot of keys!
    $cache = new Memcached($frontend, array(
        'tracking' => true
    ));

	return $cache;
});

```
