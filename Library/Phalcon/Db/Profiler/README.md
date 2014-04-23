Phalcon\Db\Profiler
==================

QueryLogger
---------------
Query logging and profiling component.
Meant to be used for debugging and/or optimization purposes in combination with Phalcon's DB events.
By default, logs query information (sql, vars to be binded, query exection time,
bind vars types and db host which executes query) using Firephp adapter with priority set to \Phalcon\Logger::DEBUG.
See [http://docs.phalconphp.com/en/latest/reference/db.html#profiling-sql-statements]

Example of usage:
```php
// services.php
$di['db'] = function () use (\Phalcon\Config $config) {
    $eventsManager = new \Phalcon\Events\Manager();
    $queryLogger = new \Phalcon\Db\Profiler\QueryLogger();
    $eventsManager->attach('db', $queryLogger);

    $adapter = new \Phalcon\Db\Adapter\Pdo\Mysql(
        array(
            "host" => $config->database->host,
            "username" => $config->database->username,
            "password" => $config->database->password,
            "dbname" => $config->database->dbname,
            "charset" => 'utf8',
        )
    );

    // remember not to use in production since it can reveal very sensitive data
    if ($config->debug) {
        $adapter->setEventsManager($eventsManager);
    }

    return $adapter
};
```


