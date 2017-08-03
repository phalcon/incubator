# Phalcon\Db\Adapter

Usage examples of the adapters available here:

## Cacheable\Mysql

Implements an aggressive cache. Every query performed is cached with the same lifetime.
This adapter is specially suitable for applications with very few inserts/updates/deletes
and a higher read rate.

```php
use Pdo;
use Phalcon\Cache\Backend\File;
use Phalcon\Cache\Frontend\Data;
use Phalcon\Db\Adapter\Cacheable\Mysql;

$di->set('db', function() {
    /** @var \Phalcon\DiInterface $this */
    $connection = new Mysql([
        'host'     => $this->getShared('config')->database->host,
        'username' => $this->getShared('config')->database->username,
        'password' => $this->getShared('config')->database->password,
        'dbname'   => $this->getShared('config')->database->name,
        'options'  => [Pdo::ATTR_EMULATE_PREPARES => false]
    ]);

    $frontCache = new Data(['lifetime' => 2592000]);

    // File backend settings
    $connection->setCache(new File($frontCache, ['cacheDir' => __DIR__ . '/../../var/db/']));

    return $connection;
});
```

## Pdo\Oracle

Specific functions for the Oracle RDBMS.

```php
use Phalcon\Db\Adapter\Pdo\Oracle;

$di->set('db', function() {
    /** @var \Phalcon\DiInterface $this */
    $connection = new Oracle([
        'dbname'   => $this->getShared('config')->database->dbname,
        'username' => $this->getShared('config')->database->username,
        'password' => $this->getShared('config')->database->password,
    ]);
    
    return $connection;
});
```

## Mongo\Client

Extends client class of MongoDb native extension to take advantage of DbRef, Document, Collection, Db.

This will solve cursor and related records problems on the ODM.

```php
use Phalcon\Db\Adapter\Mongo\Client;

$di->setShared('mongo', function() {
    /** @var \Phalcon\DiInterface $this */
    $mongo = new Client();

    return $mongo->selectDB($this->getShared('config')->database->dbname);
});
```

## MongoDB\Client

Enables the use of the new MongoDB PHP extension.

This will enable use of a mongo database using PHP7 with Phalcon 2.1

```php
use Phalcon\Mvc\Collection\Manager;
use Phalcon\Db\Adapter\MongoDB\Client;

// Initialise the mongo DB connection.
$di->setShared('mongo', function () {
    /** @var \Phalcon\DiInterface $this */
    $config = $this->getShared('config');
    
    if (!$config->database->mongo->username || !$config->database->mongo->password) {
        $dsn = 'mongodb://' . $config->database->mongo->host;
    } else {
        $dsn = sprintf(
            'mongodb://%s:%s@%s',
            $config->database->mongo->username,
            $config->database->mongo->password,
            $config->database->mongo->host
        );
    }
    
    $mongo = new Client($dsn);

    return $mongo->selectDatabase($config->database->mongo->dbname);
});

// Collection Manager is required for MongoDB
$di->setShared('collectionManager', function () {
    return new Manager();
});
```

Collection example:

```php
use Phalcon\Mvc\MongoCollection;

class UserCollection extends MongoCollection
{
    public $name;
    public $email;
    public $password;
    
    public function getSource()
    {
        return 'users';
    }
}
```
