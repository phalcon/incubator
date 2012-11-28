
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

Redis
-----
This adapter uses a [Redis](http://redis.io) backend to store the cached content:

```php

$di->set('cache', function() {

	//Connect to redis
	$redis = new Redis();
	$redis->connect('127.0.0.1', 6379);

	//Create a Data frontend and set a default lifetime to 1 hour
	$frontend = new Phalcon\Cache\Frontend\Data(array(
	    'lifetime' => 3600
	));

	//Create the cache passing the connection
	$cache = new Phalcon\Cache\Backend\Database($frontend, array(
		'redis' => $redis
	));

	return $cache;
});

```