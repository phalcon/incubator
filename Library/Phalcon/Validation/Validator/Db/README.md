Phalcon\Validation\Validator\Db
===============================

Usage examples of db validators available here:

Uniqueness
----------

```php
$connection = new \Phalcon\Db\Adapter\Pdo\Sqlite(array("dbname" => "sample.db"));

$uniqueness = new \Phalcon\Validation\Validator\Db\Uniqueness(
    array(
        'table' => 'users',
        'column' => 'login',
        'message' => 'already taken',
    ),
    $connection;
);
```