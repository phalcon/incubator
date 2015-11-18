[![Build Status](https://img.shields.io/travis/phalcon/incubator/master.svg?style=flat-square)](https://travis-ci.org/phalcon/incubator)
[![Latest Version](https://img.shields.io/packagist/v/phalcon/incubator.svg?style=flat-square)](https://github.com/phalcon/incubator/releases)
[![Software License](https://img.shields.io/badge/license-BSD--3-brightgreen.svg?style=flat-square)](docs/LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/phalcon/incubator.svg?style=flat-square)](https://packagist.org/packages/phalcon/incubator)
[![Daily Downloads](https://img.shields.io/packagist/dd/phalcon/incubator.svg?style=flat-square)](https://packagist.org/packages/phalcon/incubator)

# Phalcon Incubator

Phalcon is a web framework delivered as a C extension providing high performance and lower resource consumption.

This is a repository to publish/share/experiment with new adapters, prototypes or functionality that can potentially be incorporated into the framework.

We also welcome submissions of snippets from the community, to further extend the framework.

The code in this repository is written in PHP.

## Installation

### Installing via Composer

Install composer in a common location or in your project:

```bash
curl -s http://getcomposer.org/installer | php
```

Create the composer.json file as follows:

```json
{
    "require": {
        "phalcon/incubator": "dev-master"
    }
}
```

If you are still using Phalcon 1.3.x, create a composer.json with the following instead:

```json
{
    "require": {
        "phalcon/incubator": "v1.3.5"
    }
}
```


Run the composer installer:

```bash
php composer.phar install
```

### Installing via GitHub

Just clone the repository in a common location or inside your project:

```
git clone https://github.com/phalcon/incubator.git
```

For a specific Git branch (eg 1.3.5) please use:

```
git clone -b 1.3.5 git@github.com:phalcon/incubator.git
```

## Autoloading from the Incubator

Add or register the following namespace strategy to your Phalcon\Loader in order
to load classes from the incubator repository:

```php

$loader = new Phalcon\Loader();

$loader->registerNamespaces([
    'Phalcon' => '/path/to/incubator/Library/Phalcon/'
]);

$loader->register();
```

## Tests

Incubator uses [Codeception](http://codeception.com/) unit tests.

First you need to re-generate base classes for all suites:

```sh
$ vendor/bin/codecept build
```

You can execute all test with `run` command:

```sh
$ vendor/bin/codecept run
# OR
$ vendor/bin/codecept run --debug # Detailed output
```

Execute test groups with `run -g <group_name>` command.

Available groups:
* `Acl`
* `Annotation`
* `Cache`
* `Config`
* `Loader`
* `DbValidation`
* `MetaData`
* `EagerLoading`
* `Paginator`
* `Beanstalk`
* `Utils`
* `Validation`

Read more about the installation and configuration of Codeception:
* [Codeception Introduction](http://codeception.com/docs/01-Introduction)
* [Codeception Console Commands](http://codeception.com/docs/reference/Commands)

Some tests require a connection to the database. For those you need to create a test database using MySQL:
```sh
$ echo 'create database incubator_tests charset=utf8mb4 collate=utf8mb4_unicode_ci;' | mysql -u root
```

For these tests we use the user `root` without a password. You may need to change this in `codeception.yml` file.

Obviously, Beanstalk-tests use Beanstalk and Memcached-tests use Memcached.

We use the following settings of these services:

**Beanstalk**
+ Host: `127.0.0.1`
+ Port: `11300`

**Memcached**
+ Host: `127.0.0.1`
+ Port: `11211`

You can change the connection settings of these services **before** running tests
by using [environment variables](https://wiki.archlinux.org/index.php/Environment_variables):
```sh
# Beanstalk
export TEST_BT_HOST="127.0.0.1"
export TEST_BT_PORT="11300"

# Memcached
export TEST_MC_HOST="127.0.0.1"
export TEST_MC_PORT="11211"
```

If you cannot run the tests, please refer to the `.travis.yml` file for more instructions how we test Incubator.
For detailed information on our testing environment setting refer to `tests/_bootstrap.php` file.

## The testing process

Incubator is built under [Travis CI](https://travis-ci.org/) service.
Every commit pushed to this repository will queue a build into the continuous integration service and will run all tests
to ensure that everything is going well and the project is stable.

# Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md)

## Contributions Index

### Acl
* [Phalcon\Acl\Adapter\Database](Library/Phalcon/Acl/Adapter) - ACL lists stored in database tables (@phalcon)
* [Phalcon\Acl\Adapter\Mongo](Library/Phalcon/Acl/Adapter) - ACL lists stored in Mongo collections (@phalcon)
* [Phalcon\Acl\Adapter\Redis](Library/Phalcon/Acl/Adapter) - ACL lists stored in a Redis cluster (@Green-Cat)
* [Phalcon\Acl\Factory\Memory](Library/Phalcon/Acl/Factory) - ACL factory class intended for use with Memory adapter (@digitronac)

### Annotations
* [Phalcon\Annotations\Adapter\Memcached](Library/Phalcon/Annotations/Adapter) - Memcached adapter for storing annotations (@igusev)

### Behaviors
* [Phalcon\Mvc\Model\Behavior\Blameable](Library/Phalcon/Mvc/Model/Behavior) - logs with every created or updated row in your database who created and who updated it (@phalcon) 
* [Phalcon\Mvc\Model\Behavior\NestedSet](Library/Phalcon/Mvc/Model/Behavior) - Nested Set behavior for models (@braska)

### Cache
* [Phalcon\Cache\Backend\Database](Library/Phalcon/Cache/Backend) - Database backend for caching data (@phalcon)
* [Phalcon\Cache\Backend\Wincache](Library/Phalcon/Cache/Backend) - Wincache backend for caching data (@nazwa)

### Config
* [Phalcon\Config\Loader](Library/Phalcon/Config) - Dynamic config loader by file extension (@Kachit)

### Console
* [Phalcon\Cli\Console\Extended](Library/Phalcon/Cli/Console) - Extended Console application that uses annotations in order to create automatically a help description (@sarrubia)
* [Phalcon\Cli\Environment](Library/Phalcon/Cli/Environment) - This component provides functionality that helps writing CLI oriented code that has runtime-specific execution params (@sergeyklay)

### Database
* [Phalcon\Db\Adapter\Cacheable\Mysql](Library/Phalcon/Db) - MySQL adapter that aggressively caches all the queries executed (@phalcon)
* [Phalcon\Db\Adapter\Factory](Library/Phalcon/Db/Adapter/Factory.php) - Phalcon DB adapters Factory (@Kachit)

### Loader
* [Phalcon\Loader\Extended](Library/Phalcon/Loader/Extended.php) - This component extends `Phalcon\Loader` and added ability to set multiple directories per namespace (@sergeyklay)
* [Phalcon\Loader\PSR](Library/Phalcon/Loader/PSR.php) - Implements PSR-0 autoloader for your apps (!Piyush)

### Logger
* [Phalcon\Logger\Adapter\Database](Library/Phalcon/Logger) - Adapter to store logs in a database table (!phalcon)
* [Phalcon\Logger\Adapter\Firelogger](Library/Phalcon/Logger) - Adapter to log messages in the Firelogger console in Firebug (@phalcon)
* [Phalcon\Logger\Adapter\File\Multiple](Library/Phalcon/Logger) - Adapter to log to multiple files (@rlaffers)

### Mailer
* [Phalcon\Mailer\Manager](Library/Phalcon/Mailer) - Mailer wrapper over SwiftMailer (@KorsaR-ZN)

### Template Engines
* [Phalcon\Mvc\View\Engine\Mustache](Library/Phalcon/Mvc/View/Engine) - Adapter for Mustache (@phalcon)
* [Phalcon\Mvc\View\Engine\Twig](Library/Phalcon/Mvc/View/Engine) - Adapter for Twig (@phalcon)
* [Phalcon\Mvc\View\Engine\Smarty](Library/Phalcon/Mvc/View/Engine) - Adapter for Smarty (@phalcon)

### ORM Validators
* [Phalcon\Mvc\Model\Validator\ConfirmationOf](Library/Phalcon/Mvc/Model/Validator) - Allows to validate if a field has a confirmation field with the same value (@suxxes)
* [Phalcon\Mvc\Model\Validator\CardNumber](Library/Phalcon/Mvc/Model/Validator) - Allows to validate credit card number using Luhn algorithm (@parshikov)
* [Phalcon\Mvc\Model\Validator\Decimal](Library/Phalcon/Mvc/Model/Validator) - Allows to validate if a field has a valid number in proper decimal format (negative and decimal numbers allowed) (@sergeyklay) 
* [Phalcon\Mvc\Model\Validator\Between](Library/Phalcon/Mvc/Model/Validator) - Validates that a value is between a range of two values (@sergeyklay)

### Error Handling
* [Phalcon\Error](Library/Phalcon/Error) - Error handler used to centralize the error handling and displaying clean error pages (theDisco)
* [Phalcon\Utils\PrettyExceptions](https://github.com/phalcon/pretty-exceptions) - Pretty Exceptions is an utility to show exceptions/errors/warnings/notices using a nicely visualization. (@phalcon / @kenjikobe)

### Queue
* [Phalcon\Queue\Beanstalk\Extended](Library/Phalcon/Queue/Beanstalk) - Extended class to access the beanstalk queue service (@endeveit)

### Test
* [Phalcon\Test\FunctionalTestCase](https://github.com/silverbadge/incubator/tree/master/Library/Phalcon/Test) - Mvc app test case wrapper (@thecodeassassin)
* [Phalcon\Test\ModelTestCase](https://github.com/silverbadge/incubator/tree/master/Library/Phalcon/Test) - Model test case wrapper (@thecodeassassin)
* [Phalcon\Test\UnitTestCase](https://github.com/silverbadge/incubator/tree/master/Library/Phalcon/Test) - Generic test case wrapper (@thecodeassassin)

### Translate
* [Phalcon\Translate\Adapter\Database](Library/Phalcon/Translate/Adapter) - Translation adapter using relational databases (@phalcon)
* [Phalcon\Translate\Adapter\ResourceBundle](Library/Phalcon/Translate/Adapter) - Translation adapter using ResourceBundle (@phalcon)

### Session
* [Phalcon\Session\Adapter\Database](Library/Phalcon/Session/Adapter) - Database adapter for storing sessions (@phalcon)
* [Phalcon\Session\Adapter\Mongo](Library/Phalcon/Session/Adapter) - MongoDb adapter for storing sessions (@phalcon)
* [Phalcon\Session\Adapter\HandlerSocket](Library/Phalcon/Session/Adapter) - HandlerSocket adapter for storing sessions (@Xrymz)

### Utils
* [Phalcon\Utils\Slug](Library/Phalcon/Utils) - Creates a slug for the passed string taking into account international characters. (@niden)

### Validators
* [Phalcon\Validation\Validator\MongoId](Library/Phalcon/Validation/Validator) - Validate MongoId value (@Kachit)

## License

Incubator is open-sourced software licensed under the [New BSD License](docs/LICENSE.md). © Phalcon Framework Team and contributors
