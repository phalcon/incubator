# Phalcon\Db

Usage examples of the adapters available here:

## Cacheable\Mysql

Implements an aggressive cache. Every query performed is cached with the same lifetime.
This adapter is specially suitable for applications with very few inserts/updates/deletes
and a higher read rate.

```php
use PDO;
use Phalcon\Cache\Frontend\Data;
use Phalcon\Cache\Backend\File;
use Phalcon\Db\Adapter\Cacheable\Mysql;

$di->set('db', function() use ($config) {

  $connection = new Mysql([
    'host'     => $config->database->host,
    'username' => $config->database->username,
    'password' => $config->database->password,
    'dbname'   => $config->database->name,
    'options'  => [PDO::ATTR_EMULATE_PREPARES => false]
    ]);

  $frontCache = new Data(['lifetime' => 2592000]);

  // File backend settings
  $connection->setCache(new File($frontCache, ['cacheDir' => __DIR__ . '/../../var/db/']));

  return $connection;
});
```

## Dialect\MysqlExtended

This is an extended MySQL dialect that introduces workarounds for some common MySQL-only functions like
search based on FULLTEXT indexes and operations with date intervals. Since PHQL does not support
these syntax you can use these functions:

```php
$di->set('db', function() use ($config) {
  return new \Phalcon\Db\Adapter\Pdo\Mysql([
    "host"         => $config->database->host,
    "username"     => $config->database->username,
    "password"     => $config->database->password,
    "dbname"       => $config->database->name,
    "dialectClass" => '\Phalcon\Db\Dialect\MysqlExtended'
  ]);
});

```

Usage:

```php
// SELECT `customers`.`created_at` - INTERVAL 7 DAY FROM `customers`
$data = $this->modelsManager->executeQuery(
  'SELECT created_at - DATE_INTERVAL(7, "DAY") FROM App\Models\Customers'
);

// SELECT `customers`.`id`, `customers`.`name` FROM `customers` WHERE MATCH(`customers`.`name`, `customers`.`description`) AGAINST ("+CEO")
$data = $this->modelsManager->executeQuery(
  'SELECT id, name FROM App\Models\Customers WHERE FULLTEXT_MATCH(name, description, "+CEO")'
);

// SELECT `customers`.`id`, `customers`.`name` FROM `customers` WHERE MATCH(`customers`.`name`, `customers`.`description`) AGAINST ("+CEO" IN BOOLEAN MODE)
$data = $this->modelsManager->executeQuery(
  'SELECT id, name FROM App\Models\Customers WHERE FULLTEXT_MATCH_BMODE(name, description, "+CEO")'
);

```

## Mongo\Client

Extends client class of MongoDb native extension to take advantage of DbRef, Document, Collection, Db.

This will solve cursor and related records problems on the ODM.

```php
$di->set('mongo', function() {
    $mongo = new \Phalcon\Db\Adapter\Mongo\Client();
    return $mongo->selectDB('sitemap');
});
```
