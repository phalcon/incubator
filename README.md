# Phalcon Incubator

Phalcon PHP is a web framework delivered as a C extension providing high
performance and lower resource consumption.

This is a repository to publish/share/experimient with new adapters, prototypes
or functionality that potentially can be incorporated to the C-framework.

Also we welcome submissions from the community of snippets that could
extend the framework more.

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

Run the composer installer:

```bash
php composer.phar install
```

### Installing via Github

Just clone the repository in a common location or inside your project:

```
git clone https://github.com/phalcon/incubator.git
```

## Autoloading from the Incubator

Add or register the following namespace strategy to your Phalcon\Loader in order
to load classes from the incubator repository:

```php

$loader = new Phalcon\Loader();

$loader->registerNamespaces(array(
	'Phalcon' => '/path/to/incubator/Library/Phalcon/'
));

$loader->register();
```

## Contributions Index

### Acl
* [Phalcon\Acl\Adapter\Database](https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Acl/Adapter) - ACL lists stored in database tables
* [Phalcon\Acl\Adapter\Mongo](https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Acl/Adapter) - ACL lists stored in Mongo collections

### Cache
* [Phalcon\Cache\Backend\Database](https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Cache/Backend) - Database backend for caching data (phalcon)
* [Phalcon\Cache\Backend\Redis](https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Cache/Backend) - Redis backend for caching data (kenjikobe)

### Config
* [Phalcon\Config\Adapter\Json](https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Config/Adapter) - Json adapter (ofpiyush)
* [Phalcon\Config\Adapter\Yaml](https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Config/Adapter) - YAML adapter (freekzy)

### Database
* [Phalcon\Db\Adapter\Cacheable\Mysql](https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Db) - MySQL adapter that agressively caches all the queries executed (phalcon)

### Logger
* [Phalcon\Logger\Adapter\Firephp](https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Logger) - Adapter to log messages in Firebug (phalcon)
* [Phalcon\Logger\Adapter\Database](https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Logger) - Adapter to store logs in a database table (phalcon)

### Template Engines
* [Phalcon\Mvc\View\Engine\Mustache](https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Mvc/View/Engine) - Adapter for Mustache (phalcon)
* [Phalcon\Mvc\View\Engine\Twig](https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Mvc/View/Engine) - Adapter for Twig (phalcon)
* [Phalcon\Mvc\View\Engine\Smarty](https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Mvc/View/Engine) - Adapter for Smarty (phalcon)

### ORM Validators
* [Phalcon\Mvc\Model\Validator\ConfirmationOf](https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Mvc/Model) - Allows to validate if a field has a confirmation field with the same value (suxxes)

### Error Handling
* [Phalcon\Error](https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Error) - Error handler used to centralize the error handling and displaying clean error pages (theDisco)
* [Phalcon\Utils\PrettyExceptions](https://github.com/phalcon/pretty-exceptions) - Pretty Exceptions is an utility to show exceptions/errors/warnings/notices using a nicely visualization. (phalcon/kenjikobe)

### Queue
* [Phalcon\Queue\Beanstalk\Extended](https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Queue/Beanstalk) - Extended class to access the beanstalk queue service (endeveit)

### Test
* [Phalcon\Test\FunctionalTestCase](https://github.com/silverbadge/incubator/tree/master/Library/Phalcon/Test) - Mvc app test case wrapper (thecodeassassin)
* [Phalcon\Test\ModelTestCase](https://github.com/silverbadge/incubator/tree/master/Library/Phalcon/Test) - Model test case wrapper (thecodeassassin)
* [Phalcon\Test\UnitTestCase](https://github.com/silverbadge/incubator/tree/master/Library/Phalcon/Test) - Generic test case wrapper (thecodeassassin)

### Translate
* [Phalcon\Translate\Adapter\Gettext](https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Translate/Adapter) - Translation adapter for Gettext (phalcon)
* [Phalcon\Translate\Adapter\Database](https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Translate/Adapter) - Translation adapter using relational databases (phalcon)
* [Phalcon\Translate\Adapter\Csv](https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Translate/Adapter) - Translation adapter using CSV (phalcon)

### Session
* [Phalcon\Session\Adapter\Database](https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Session/Adapter) - Database adapter for storing sessions (phalcon)
* [Phalcon\Session\Adapter\Memcache](https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Session/Adapter) - Memcache adapter for storing sessions (meets-ecommerce)
* [Phalcon\Session\Adapter\Mongo](https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Session/Adapter) - MongoDb adapter for storing sessions (phalcon)
* [Phalcon\Session\Adapter\Redis](https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Session/Adapter) - Redis adapter for storing sessions (phalcon)
* [Phalcon\Session\Adapter\HandlerSocket](https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Session/Adapter) - HandlerSocket adapter for storing sessions (Xrymz)

### Utils
* [Phalcon\Utils\Slug](https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Utils) - Creates a slug for the passed string taking into account international characters. (niden)
