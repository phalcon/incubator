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

Install Composer in a common location or in your project:

```bash
curl -s http://getcomposer.org/installer | php
```

Then create the `composer.json` file as follows:

```json
{
    "require": {
        "phalcon/incubator": "^2.1"
    }
}
```

If you are still using Phalcon 2.0.x, create the `composer.json` file as follows:

```json
{
    "require": {
        "phalcon/incubator": "^2.0"
    }
}
```

If you are still using Phalcon 1.3.x, create a `composer.json` with the following instead:

```json
{
    "require": {
        "phalcon/incubator": "^1.3"
    }
}
```


Run the composer installer:

```bash
$ php composer.phar install
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

## Testing

See [TESTING.md](docs/TESTING.md)

# Contributing

See [CONTRIBUTING.md](docs/CONTRIBUTING.md)

## Contributions Index

### Acl
* [Phalcon\Acl\Adapter\Database](Library/Phalcon/Acl/Adapter) - ACL lists stored in database tables (@phalcon)
* [Phalcon\Acl\Adapter\Mongo](Library/Phalcon/Acl/Adapter) - ACL lists stored in Mongo collections (@phalcon)
* [Phalcon\Acl\Adapter\Redis](Library/Phalcon/Acl/Adapter) - ACL lists stored in a Redis cluster (@Green-Cat)
* [Phalcon\Acl\Factory\Memory](Library/Phalcon/Acl/Factory) - ACL factory class intended for use with Memory adapter (@digitronac)

### Annotations
* [Phalcon\Annotations\Adapter\Memcached](Library/Phalcon/Annotations/Adapter) - Memcached adapter for storing annotations (@igusev)
* [Phalcon\Annotations\Adapter\Redis](Library/Phalcon/Annotations/Adapter) - Redis adapter for storing annotations (@sergeyklay)

### Behaviors
* [Phalcon\Mvc\Model\Behavior\Blameable](Library/Phalcon/Mvc/Model/Behavior) - logs with every created or updated row in your database who created and who updated it (@phalcon)
* [Phalcon\Mvc\Model\Behavior\NestedSet](Library/Phalcon/Mvc/Model/Behavior) - Nested Set behavior for models (@braska)

### Cache
* [Phalcon\Cache\Backend\Aerospike](Library/Phalcon/Cache/Backend) - Aerospike backend for caching data (@sergeyklay)
* [Phalcon\Cache\Backend\Database](Library/Phalcon/Cache/Backend) - Database backend for caching data (@phalcon)
* [Phalcon\Cache\Backend\Wincache](Library/Phalcon/Cache/Backend) - Wincache backend for caching data (@nazwa)

### Config
* [Phalcon\Config\Loader](Library/Phalcon/Config) - Dynamic config loader by file extension (@Kachit)
* [Phalcon\Config\Adapter\Xml](Library/Phalcon/Config) - Reads xml files and converts them to Phalcon\Config objects. (@sergeyklay)

### Console
* [Phalcon\Cli\Console\Extended](Library/Phalcon/Cli/Console) - Extended Console application that uses annotations in order to create automatically a help description (@sarrubia)
* [Phalcon\Cli\Environment](Library/Phalcon/Cli/Environment) - This component provides functionality that helps writing CLI oriented code that has runtime-specific execution params (@sergeyklay)

### Crypt
* [Phalcon\Legacy\Crypt](Library/Phalcon/Legacy) - Port of Phalcon 2.0.x (legacy) `Phalcon\Crypt` (@sergeyklay)

### Database
* [Phalcon\Db\Adapter\Cacheable\Mysql](Library/Phalcon/Db) - MySQL adapter that aggressively caches all the queries executed (@phalcon)
* [Phalcon\Db\Adapter\Factory](Library/Phalcon/Db/Adapter/Factory.php) - Phalcon DB adapters Factory (@Kachit)

### Http
* [Phalcon\Http](Library/Phalcon/Http) - Uri utility (@tugrul)
* [Phalcon\Http\Client](Library/Phalcon/Http/Client) - Http Request and Response (@tugrul)

### Logger
* [Phalcon\Logger\Adapter\Database](Library/Phalcon/Logger) - Adapter to store logs in a database table (!phalcon)
* [Phalcon\Logger\Adapter\Firelogger](Library/Phalcon/Logger) - Adapter to log messages in the Firelogger console in Firebug (@phalcon)
* [Phalcon\Logger\Adapter\Udplogger](Library/Phalcon/Logger) - Adapter to log messages using UDP protocol to external server (@vitalypanait)
* [Phalcon\Logger\Adapter\File\Multiple](Library/Phalcon/Logger) - Adapter to log to multiple files (@rlaffers)

### Mailer
* [Phalcon\Mailer\Manager](Library/Phalcon/Mailer) - Mailer wrapper over SwiftMailer (@KorsaR-ZN)

### Model MetaData Adapters
* [Phalcon\Mvc\Model\MetaData\Wincache](Library/Phalcon/Mvc/Model/MetaData) - Adapter for the Wincache php extension

### Template Engines
* [Phalcon\Mvc\View\Engine\Mustache](Library/Phalcon/Mvc/View/Engine) - Adapter for Mustache (@phalcon)
* [Phalcon\Mvc\View\Engine\Twig](Library/Phalcon/Mvc/View/Engine) - Adapter for Twig (@phalcon)
* [Phalcon\Mvc\View\Engine\Smarty](Library/Phalcon/Mvc/View/Engine) - Adapter for Smarty (@phalcon)

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
* [Phalcon\Session\Adapter\Aerospike](Library/Phalcon/Session/Adapter) - Aerospike adapter for storing sessions (@sergeyklay)
* [Phalcon\Session\Adapter\Database](Library/Phalcon/Session/Adapter) - Database adapter for storing sessions (@phalcon)
* [Phalcon\Session\Adapter\Mongo](Library/Phalcon/Session/Adapter) - MongoDb adapter for storing sessions (@phalcon)
* [Phalcon\Session\Adapter\HandlerSocket](Library/Phalcon/Session/Adapter) - HandlerSocket adapter for storing sessions (@Xrymz)

### Utils
* [Phalcon\Utils\Slug](Library/Phalcon/Utils) - Creates a slug for the passed string taking into account international characters. (@niden)
* [Phalcon\Avatar\Gravatar](Library/Phalcon/Avatar) - Provides an easy way to retrieve a user's profile image from Gravatar site based on a given email address (@sergeyklay)

### Validators
* [Phalcon\Validation\Validator\CardNumber](Library/Phalcon/Validation/Validator) - Allows to validate credit card number using Luhn algorithm (@parshikov)
* [Phalcon\Validation\Validator\ConfirmationOf](Library/Phalcon/Validation/Validator) - Validates confirmation of other field value (@davihu)
* [Phalcon\Validation\Validator\Decimal](Library/Phalcon/Validation/Validator) - Allows to validate if a field has a valid number in proper decimal format (negative and decimal numbers allowed) (@sergeyklay)
* [Phalcon\Validation\Validator\MongoId](Library/Phalcon/Validation/Validator) - Validate MongoId value (@Kachit)
* [Phalcon\Validation\Validator\PasswordStrength](Library/Phalcon/Validation/Validator) - Validates password strength (@davihu)

## License

Incubator is open-sourced software licensed under the [New BSD License](docs/LICENSE.md). © Phalcon Framework Team and contributors
