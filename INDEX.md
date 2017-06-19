# Contributions Index

## Acl
* [Phalcon\Acl\Adapter\Database](Library/Phalcon/Acl/Adapter) - ACL lists stored in database tables ([@andresgutierrez](https://github.com/andresgutierrez))
* [Phalcon\Acl\Adapter\Mongo](Library/Phalcon/Acl/Adapter) - ACL lists stored in Mongo collections ([@andresgutierrez](https://github.com/andresgutierrez))
* [Phalcon\Acl\Adapter\Redis](Library/Phalcon/Acl/Adapter) - ACL lists stored in a Redis cluster ([@Green-Cat](https://github.com/Green-Cat))
* [Phalcon\Acl\Factory\Memory](Library/Phalcon/Acl/Factory) - ACL factory class intended for use with Memory adapter ([@digitronac](https://github.com/digitronac))

## Annotations
* [Phalcon\Annotations\Adapter\Memcached](Library/Phalcon/Annotations/Adapter) - Memcached adapter for storing annotations ([@igusev](https://github.com/igusev))
* [Phalcon\Annotations\Adapter\Redis](Library/Phalcon/Annotations/Adapter) - Redis adapter for storing annotations ([@sergeyklay](https://github.com/sergeyklay))
* [Phalcon\Annotations\Adapter\Aerospike](Library/Phalcon/Annotations/Adapter) - Aerospike adapter for storing annotations ([@sergeyklay](https://github.com/sergeyklay))
* [Phalcon\Annotations\Extended\Adapter\Apc](Library/Phalcon/Annotations/Extended/Adapter) - Extended Apc adapter for storing annotations in the APC(u) ([@sergeyklay](https://github.com/sergeyklay))
* [Phalcon\Annotations\Extended\Adapter\Memory](Library/Phalcon/Annotations/Extended/Adapter) - Extended Memory adapter for storing annotations in the memory ([@sergeyklay](https://github.com/sergeyklay))
* [Phalcon\Annotations\Extended\Adapter\Files](Library/Phalcon/Annotations/Extended/Adapter) - Extended Files adapter for storing annotations in files ([@sergeyklay](https://github.com/sergeyklay))

## Behaviors
* [Phalcon\Mvc\Model\Behavior\Blameable](Library/Phalcon/Mvc/Model/Behavior) - logs with every created or updated row in your database who created and who updated it ([@andresgutierrez](https://github.com/andresgutierrez))
* [Phalcon\Mvc\Model\Behavior\NestedSet](Library/Phalcon/Mvc/Model/Behavior) - Nested Set behavior for models ([@braska](https://github.com/braska))

## Cache
* [Phalcon\Cache\Backend\Aerospike](Library/Phalcon/Cache/Backend) - Aerospike backend for caching data ([@sergeyklay](https://github.com/sergeyklay))
* [Phalcon\Cache\Backend\Database](Library/Phalcon/Cache/Backend) - Database backend for caching data ([@andresgutierrez](https://github.com/andresgutierrez))
* [Phalcon\Cache\Backend\Wincache](Library/Phalcon/Cache/Backend) - Wincache backend for caching data ([@nazwa](https://github.com/nazwa))

## Config
* [Phalcon\Config\Loader](Library/Phalcon/Config) - Dynamic config loader by file extension ([@Kachit](https://github.com/Kachit))
* [Phalcon\Config\Adapter\Xml](Library/Phalcon/Config) - Reads xml files and converts them to Phalcon\Config objects. ([@sergeyklay](https://github.com/sergeyklay))

## Console
* [Phalcon\Cli\Console\Extended](Library/Phalcon/Cli/Console) - Extended Console application that uses annotations in order to create automatically a help description ([@sarrubia](https://github.com/sarrubia))
* [Phalcon\Cli\Environment](Library/Phalcon/Cli/Environment) - This component provides functionality that helps writing CLI oriented code that has runtime-specific execution params ([@sergeyklay](https://github.com/sergeyklay))

## Crypt
* [Phalcon\Legacy\Crypt](Library/Phalcon/Legacy) - Port of Phalcon 2.0.x (legacy) `Phalcon\Crypt` ([@sergeyklay](https://github.com/sergeyklay))

## Database

### Adapter
* [Phalcon\Db\Adapter\Cacheable\Mysql](Library/Phalcon/Db/Adapter) - MySQL adapter that aggressively caches all the queries executed ([@andresgutierrez](https://github.com/andresgutierrez))
* [Phalcon\Db\Adapter\Factory](Library/Phalcon/Db/Adapter) - Phalcon DB adapters Factory ([@Kachit](https://github.com/Kachit))
* [Phalcon\Db\Adapter\MongoDB](Library/Phalcon/Db/Adapter) - Database adapter for the new MongoDB extension ([@tigerstrikemedia](https://github.com/tigerstrikemedia))
* [Phalcon\Db\Adapter\Pdo\Oracle](Library/Phalcon/Db/Adapter) - Database adapter for the Oracle for the Oracle RDBMS. ([@sergeyklay](https://github.com/sergeyklay))

### Dialect
* [Phalcon\Db\Dialect\MysqlExtended](Library/Phalcon/Db/Dialect) - Generates database specific SQL for the MySQL RDBMS. Extended version. ([@andresgutierrez](https://github.com/andresgutierrez))
* [Phalcon\Db\Dialect\Oracle](Library/Phalcon/Db/Dialect) - Generates database specific SQL for the Oracle RDBMS. ([@sergeyklay](https://github.com/sergeyklay))

## Http
* [Phalcon\Http](Library/Phalcon/Http) - Uri utility ([@tugrul](https://github.com/tugrul))
* [Phalcon\Http\Client](Library/Phalcon/Http/Client) - Http Request and Response ([@tugrul](https://github.com/tugrul))

## Logger
* [Phalcon\Logger\Adapter\Database](Library/Phalcon/Logger) - Adapter to store logs in a database table ([@andresgutierrez](https://github.com/andresgutierrez))
* [Phalcon\Logger\Adapter\Firelogger](Library/Phalcon/Logger) - Adapter to log messages in the Firelogger console in Firebug ([@andresgutierrez](https://github.com/andresgutierrez))
* [Phalcon\Logger\Adapter\File\Multiple](Library/Phalcon/Logger) - Adapter to log to multiple files ([@rlaffers](https://github.com/rlaffers))

## Mailer
* [Phalcon\Mailer\Manager](Library/Phalcon/Mailer) - Mailer wrapper over SwiftMailer ([@KorsaR-ZN](https://github.com/KorsaR-ZN))

## Model MetaData Adapters
* [Phalcon\Mvc\Model\MetaData\Wincache](Library/Phalcon/Mvc/Model/MetaData) - Adapter for the Wincache php extension

## MVC
* [Phalcon\Mvc\MongoCollection](Library/Phalcon/MVC/MongoCollection) - Collection class for the new MongoDB Extension ([@tigerstrikemedia](https://github.com/tigerstrikemedia))

## Template Engines
* [Phalcon\Mvc\View\Engine\Mustache](Library/Phalcon/Mvc/View/Engine) - Adapter for Mustache ([@andresgutierrez](https://github.com/andresgutierrez))
* [Phalcon\Mvc\View\Engine\Twig](Library/Phalcon/Mvc/View/Engine) - Adapter for Twig ([@andresgutierrez](https://github.com/andresgutierrez))
* [Phalcon\Mvc\View\Engine\Smarty](Library/Phalcon/Mvc/View/Engine) - Adapter for Smarty ([@andresgutierrez](https://github.com/andresgutierrez))

## Error Handling
* [Phalcon\Error](Library/Phalcon/Error) - Error handler used to centralize the error handling and displaying clean error pages ([@theDisco](https://github.com/theDisco))

## Queue
* [Phalcon\Queue\Beanstalk\Extended](Library/Phalcon/Queue/Beanstalk) - Extended class to access the beanstalk queue service ([@endeveit](https://github.com/endeveit))

## Test
* [Phalcon\Test\FunctionalTestCase](Library/Phalcon/Test) - Mvc app test case wrapper ([@thecodeassassin](https://github.com/thecodeassassin))
* [Phalcon\Test\ModelTestCase](Library/Phalcon/Test) - Model test case wrapper ([@thecodeassassin](https://github.com/thecodeassassin))
* [Phalcon\Test\UnitTestCase](Library/Phalcon/Test) - Generic test case wrapper ([@thecodeassassin](https://github.com/thecodeassassin))

## Translate
* [Phalcon\Translate\Adapter\Database](Library/Phalcon/Translate/Adapter) - Translation adapter using relational databases ([@andresgutierrez](https://github.com/andresgutierrez))
* [Phalcon\Translate\Adapter\Mongo](Library/Phalcon/Translate/Adapter) - Implements a Mongo adapter for translations ([@gguridi](https://github.com/gguridi))
* [Phalcon\Translate\Adapter\ResourceBundle](Library/Phalcon/Translate/Adapter) - Translation adapter using ResourceBundle ([@andresgutierrez](https://github.com/andresgutierrez))

## Session
* [Phalcon\Session\Adapter\Aerospike](Library/Phalcon/Session/Adapter) - Aerospike adapter for storing sessions ([@sergeyklay](https://github.com/sergeyklay))
* [Phalcon\Session\Adapter\Database](Library/Phalcon/Session/Adapter) - Database adapter for storing sessions ([@andresgutierrez](https://github.com/andresgutierrez))
* [Phalcon\Session\Adapter\Mongo](Library/Phalcon/Session/Adapter) - MongoDb adapter for storing sessions ([@andresgutierrez](https://github.com/andresgutierrez))
* [Phalcon\Session\Adapter\HandlerSocket](Library/Phalcon/Session/Adapter) - HandlerSocket adapter for storing sessions ([@Xrymz](https://github.com/Xrymz))

## Utils
* [Phalcon\Utils\Slug](Library/Phalcon/Utils) - Creates a slug for the passed string taking into account international characters. ([@niden](https://github.com/niden))
* [Phalcon\Avatar\Gravatar](Library/Phalcon/Avatar) - Provides an easy way to retrieve a user's profile image from Gravatar site based on a given email address ([@sergeyklay](https://github.com/sergeyklay))

## Validators
* [Phalcon\Validation\Validator\AlphaCompleteValidator](Library/Phalcon/Validation/AlphaCompleteValidator) - Validates a string containing alphanumeric, underscore, white spaces, slashes, apostrophes, brackets and punctuation characters. Optionally other characters can be allowed ([@micheleangioni](https://github.com/micheleangioni))
* [Phalcon\Validation\Validator\AlphaNamesValidator](Library/Phalcon/Validation/AlphaNamesValidator) - Validates a string containing alphanumeric, menus, apostrophe, underscore and white space characters. Optionally other numbers too can be allowed ([@micheleangioni](https://github.com/micheleangioni))
* [Phalcon\Validation\Validator\AlphaNumericValidator](Library/Phalcon/Validation/AlphaNumericValidator) - Validates a string containing alphanumeric characters. Optionally white spaces and underscores can be allowed ([@micheleangioni](https://github.com/micheleangioni))
* [Phalcon\Validation\Validator\CardNumber](Library/Phalcon/Validation/Validator) - Allows to validate credit card number using Luhn algorithm ([@parshikov](https://github.com/parshikov))
* [Phalcon\Validation\Validator\ConfirmationOf](Library/Phalcon/Validation/Validator) - Validates confirmation of other field value ([@davihu](https://github.com/davihu))
* [Phalcon\Validation\Validator\Decimal](Library/Phalcon/Validation/Validator) - Allows to validate if a field has a valid number in proper decimal format (negative and decimal numbers allowed) ([@sergeyklay](https://github.com/sergeyklay))
* [Phalcon\Validation\Validator\IpValidator](Library/Phalcon/Validation/IpValidator) - Validates an ip address ([@micheleangioni](https://github.com/micheleangioni))
* [Phalcon\Validation\Validator\MongoId](Library/Phalcon/Validation/Validator) - Validate MongoId value ([@Kachit](https://github.com/Kachit))
* [Phalcon\Validation\Validator\NumericValidator](Library/Phalcon/Validation/NumericValidator) - Validates a numeric string. Optionally can contain and sign (+/-) and allow floats ([@micheleangioni](https://github.com/micheleangioni))
* [Phalcon\Validation\Validator\PasswordStrength](Library/Phalcon/Validation/Validator) - Validates password strength ([@davihu](https://github.com/davihu))
* [Phalcon\Validation\Validator\ReCaptcha](Library/Phalcon/Validation/Validator) - The reCAPTCHA Validator ([@pflorek](https://github.com/pflorek))

## Traits

* [Phalcon\Traits\ConfigurableTrait](Library/Phalcon/Traits) - Allows to define parameters which can be set by passing them to the class constructor ([@sergeyklay](https://github.com/sergeyklay))
