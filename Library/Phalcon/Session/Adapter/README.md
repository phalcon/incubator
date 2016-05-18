# Phalcon\Session\Adapter

Usage examples of the adapters available here:

## Aerospike

This adapter uses an Aerospike Database to store session data.

To use this adapter on your machine, you need at least:

- [Aerospike Server][1] >= 3.5.3
- [Aerospike PHP Extension][2]

Usage:

```php
use Phalcon\Session\Adapter\Aerospike as SessionHandler;

$di->set('session', function () {
    $session = new SessionHandler([
        'hosts' => [
            ['addr' => '127.0.0.1', 'port' => 3000]
        ],
        'persistent' => true,
        'namespace'  => 'test',
        'prefix'     => 'session_',
        'lifetime'   => 8600,
        'uniqueId'   => '3Hf90KdjQ18',
        'options'    => [
            \Aerospike::OPT_CONNECT_TIMEOUT => 1250,
            \Aerospike::OPT_WRITE_TIMEOUT   => 1500
        ]
    ]);

    $session->start();

    return $session;
});
```


## Database

This adapter uses a database backend to store session data:

```php
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Session\Adapter\Database;

$di->set('session', function() {
    // Create a connection
    $connection = new Mysql([
        'host'     => 'localhost',
        'username' => 'root',
        'password' => 'secret',
        'dbname'   => 'test'
    ]);

    $session = new Database([
        'db'    => $connection,
        'table' => 'session_data'
    ]);

    $session->start();

    return $session;
});

```

This adapter uses the following table to store the data:

```sql
 CREATE TABLE `session_data` (
  `session_id` VARCHAR(35) NOT NULL,
  `data` text NOT NULL,
  `created_at` INT unsigned NOT NULL,
  `modified_at` INT unsigned DEFAULT NULL,
  PRIMARY KEY (`session_id`)
);
```

## Mongo

This adapter uses a Mongo database backend to store session data:

```php
use Phalcon\Session\Adapter\Mongo as MongoSession;

$di->set('session', function() {
    // Create a connection to mongo
    $mongo = new \Mongo();

    // Passing a collection to the adapter
    $session = new MongoSession([
        'collection' => $mongo->test->session_data
    ]);

    $session->start();

    return $session;
});

```

## Redis

This adapter uses a [Redis][2] backend to store session data.
You would need a [phpredis][4] extension installed to use it:

```php
use Phalcon\Session\Adapter\Redis;

$di->set('session', function() {
    $session = new Redis([
        'path' => 'tcp://127.0.0.1:6379?weight=1'
    ]);

    $session->start();

    return $session;
});

```

## HandlerSocket

This adapter uses the MySQL's plugin HandlerSocket. HandlerSocket is a NoSQL plugin for MySQL. It works as a daemon inside the
mysqld process, accept tcp connections, and execute requests from clients. HandlerSocket does not support SQL queries.
Instead, it supports simple CRUD operations on tables.

```sql
CREATE TABLE `php_session` (
    `id` VARCHAR(32) NOT NULL DEFAULT '',
    `modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
    `data` TEXT,
    PRIMARY KEY (`id`),
    KEY `modified` (`modified`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8;
```

```php
use Phalcon\Session\Adapter\HandlerSocket;

$di->set('session', function() {
    $session = new HandlerSocket([
        'cookie_path'   => '/',
        'cookie_domain' => '',
        'lifetime'      => 3600,
        'server' => [
            'host'    => 'localhost',
            'port'    => 9999,
            'dbname'  => 'session',
            'dbtable' => 'php_session'
        ]
    ]);

    $session->start();

    return $session;
});

```

The extension [`handlersocket`][5] is required to use this adapter.

[1]: http://www.aerospike.com/
[2]: http://www.aerospike.com/docs/client/php/install/
[3]: http://redis.io
[4]: https://github.com/nicolasff/phpredis
[5]: https://github.com/kjdev/php-ext-handlersocketi
