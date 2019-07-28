# Phalcon\Db\Dialect

## MysqlExtended

Generates database specific SQL for the MySQL RDBMS.

This is an extended MySQL dialect that introduces workarounds for some common MySQL-only functions like
search based on FULLTEXT indexes and operations with date intervals. Since PHQL does not support
these syntax you can use these functions:

```php
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Db\Dialect\MysqlExtended;

$di->set(
    'db',
    function () {
        return new Mysql(
            [
                'host'         => 'localhost',
                'username'     => 'root',
                'password'     => 'secret',
                'dbname'       => 'enigma',
                'dialectClass' => MysqlExtended::class,
            ]
        );
    }
);
```

Usage:

```php
// SELECT `customers`.`created_at` - INTERVAL 7 DAY FROM `customers`
$data = $this->modelsManager->executeQuery(
    'SELECT created_at - DATE_INTERVAL(7, "DAY") FROM App\Models\Customers'
);

// SELECT `customers`.`id`, `customers`.`name` FROM `customers`
// WHERE MATCH(`customers`.`name`, `customers`.`description`) AGAINST ("+CEO")
$data = $this->modelsManager->executeQuery(
    'SELECT id, name FROM App\Models\Customers WHERE FULLTEXT_MATCH(name, description, "+CEO")'
);

// SELECT `customers`.`id`, `customers`.`name` FROM `customers`
// WHERE MATCH(`customers`.`name`, `customers`.`description`) AGAINST ("+CEO" IN BOOLEAN MODE)
$data = $this->modelsManager->executeQuery(
    'SELECT id, name FROM App\Models\Customers WHERE FULLTEXT_MATCH_BMODE(name, description, "+CEO")'
);

// SELECT `customers`.`id`, `customers`.`name` FROM `customers` WHERE `customers`.`name` REGEXP('^John')
$data = $this->modelsManager->executeQuery(
    'SELECT id, name FROM App\Models\Customers WHERE REGEXP(name, "^John")'
);
```

## Oracle

Generates database specific SQL for the Oracle RDBMS.

```php
use Phalcon\Db\Adapter\Pdo\Oracle;
use Phalcon\Db\Adapter\Pdo\Oracle as Connection;

$di->set(
    'db',
    function () {
        return new Connection(
            [
                'dbname'       => '//localhost/enigma',
                'username'     => 'oracle',
                'password'     => 'secret',
                'dialectClass' => Oracle::class,
            ]
        );
    }
);
```
