# Phalcon\Validation\Validator\Db

Usage examples of DB validators available here:

## Uniqueness

```php
$connection = new \Phalcon\Db\Adapter\Pdo\Sqlite(
    [
        'dbname' => 'sample.db',
    ]
);

$uniqueness = new \Phalcon\Validation\Validator\Db\Uniqueness(
    [
        'table'   => 'users',
        'column'  => 'login',
        'message' => 'already taken',
    ],
    $connection
);
```
