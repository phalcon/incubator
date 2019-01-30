# Phalcon\Cache\Backend

Usage examples of the adapters available here:

## Aerospike

This adapter uses an Aerospike Database to store the cached content.

To use this adapter on your machine, you need at least:

- [Aerospike Server][1] >= 3.5.3
- [Aerospike PHP Extension][2]

Usage:

```php
use Phalcon\Cache\Backend\Aerospike as BackendCache;
use Phalcon\Cache\Frontend\Data;

$di->set('cache', function () {
    $cache = new BackendCache(new Data(['lifetime' => 3600]), [
        'hosts' => [
            ['addr' => '127.0.0.1', 'port' => 3000]
        ],
        'persistent' => true,
        'namespace'  => 'test',
        'prefix'     => 'cache_',
        'options'    => [
            \Aerospike::OPT_CONNECT_TIMEOUT => 1250,
            \Aerospike::OPT_WRITE_TIMEOUT   => 1500
        ]
    ]);

    return $cache;
});
```

## Database

This adapter uses a database backend to store the cached content:

```php
use Phalcon\Cache\Backend\Database;
use Phalcon\Cache\Frontend\Data;
use Phalcon\Db\Adapter\Pdo\Mysql;

$di->set('cache', function() {
	// Create a connection
	$connection = new Mysql([
	    'host'     => 'localhost',
	    'username' => 'root',
	    'password' => 'secret',
	    'dbname'   => 'cache_db'
	]);

	// Create a Data frontend and set a default lifetime to 1 hour
	$frontend = new Data(['lifetime' => 3600]);

	// Create the cache passing the connection
	$cache = new Database($frontend, [
	    'db'    => $connection,
	    'table' => 'cache_data'
	]);

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

## Wincache

This adapter uses [windows cache extension](http://pecl.php.net/package/wincache) for PHP


## NullCache

NullCache adapter can be useful when you have dependency injection and wants to provider an Adapter that does nothing.  

[1]: http://www.aerospike.com/
[2]: http://www.aerospike.com/docs/client/php/install/
