# Contributions Index

## Acl
* [Phalcon\Acl\Adapter\Database](Library/Phalcon/Acl/Adapter) - ACL lists stored in database tables (@phalcon)
* [Phalcon\Acl\Adapter\Mongo](Library/Phalcon/Acl/Adapter) - ACL lists stored in Mongo collections (@phalcon)
* [Phalcon\Acl\Adapter\Redis](Library/Phalcon/Acl/Adapter) - ACL lists stored in a Redis cluster (@Green-Cat)
* [Phalcon\Acl\Factory\Memory](Library/Phalcon/Acl/Factory) - ACL factory class intended for use with Memory adapter (@digitronac)

## Annotations
* [Phalcon\Annotations\Adapter\Memcached](Library/Phalcon/Annotations/Adapter) - Memcached adapter for storing annotations (@igusev)
* [Phalcon\Annotations\Adapter\Redis](Library/Phalcon/Annotations/Adapter) - Redis adapter for storing annotations (@sergeyklay)
* [Phalcon\Annotations\Adapter\Aerospike](Library/Phalcon/Annotations/Adapter) - Aerospike adapter for storing annotations (@sergeyklay)
* [Phalcon\Annotations\Extended\Adapter\Apc](Library/Phalcon/Annotations/Extended/Adapter) - Extended Apc adapter for storing annotations in the APC(u) (@sergeyklay)
* [Phalcon\Annotations\Extended\Adapter\Memory](Library/Phalcon/Annotations/Extended/Adapter) - Extended Memory adapter for storing annotations in the memory (@sergeyklay)
* [Phalcon\Annotations\Extended\Adapter\Files](Library/Phalcon/Annotations/Extended/Adapter) - Extended Files adapter for storing annotations in files (@sergeyklay)

## Behaviors
* [Phalcon\Mvc\Model\Behavior\Blameable](Library/Phalcon/Mvc/Model/Behavior) - logs with every created or updated row in your database who created and who updated it (@phalcon)
* [Phalcon\Mvc\Model\Behavior\NestedSet](Library/Phalcon/Mvc/Model/Behavior) - Nested Set behavior for models (@braska)

## Cache
* [Phalcon\Cache\Backend\Aerospike](Library/Phalcon/Cache/Backend) - Aerospike backend for caching data (@sergeyklay)
* [Phalcon\Cache\Backend\Database](Library/Phalcon/Cache/Backend) - Database backend for caching data (@phalcon)
* [Phalcon\Cache\Backend\Wincache](Library/Phalcon/Cache/Backend) - Wincache backend for caching data (@nazwa)

## Config
* [Phalcon\Config\Loader](Library/Phalcon/Config) - Dynamic config loader by file extension (@Kachit)
* [Phalcon\Config\Adapter\Xml](Library/Phalcon/Config) - Reads xml files and converts them to Phalcon\Config objects. (@sergeyklay)

## Console
* [Phalcon\Cli\Console\Extended](Library/Phalcon/Cli/Console) - Extended Console application that uses annotations in order to create automatically a help description (@sarrubia)
* [Phalcon\Cli\Environment](Library/Phalcon/Cli/Environment) - This component provides functionality that helps writing CLI oriented code that has runtime-specific execution params (@sergeyklay)

## Crypt
* [Phalcon\Legacy\Crypt](Library/Phalcon/Legacy) - Port of Phalcon 2.0.x (legacy) `Phalcon\Crypt` (@sergeyklay)

## Database

### Adapter
* [Phalcon\Db\Adapter\Cacheable\Mysql](Library/Phalcon/Db/Adapter) - MySQL adapter that aggressively caches all the queries executed (@phalcon)
* [Phalcon\Db\Adapter\Factory](Library/Phalcon/Db/Adapter) - Phalcon DB adapters Factory (@Kachit)
* [Phalcon\Db\Adapter\MongoDB](Library/Phalcon/Db/Adapter) - Database adapter for the new MongoDB extension (@tigerstrikemedia)
* [Phalcon\Db\Adapter\Pdo\Oracle](Library/Phalcon/Db/Adapter) - Database adapter for the Oracle for the Oracle RDBMS. (@sergeyklay)

### Dialect
* [Phalcon\Db\Dialect\MysqlExtended](Library/Phalcon/Db/Dialect) - Generates database specific SQL for the MySQL RDBMS. Extended version. (@phalcon)
* [Phalcon\Db\Dialect\Oracle](Library/Phalcon/Db/Dialect) - Generates database specific SQL for the Oracle RDBMS. (@sergeyklay)

## Http
* [Phalcon\Http](Library/Phalcon/Http) - Uri utility (@tugrul)
* [Phalcon\Http\Client](Library/Phalcon/Http/Client) - Http Request and Response (@tugrul)

## Logger
* [Phalcon\Logger\Adapter\Database](Library/Phalcon/Logger) - Adapter to store logs in a database table (@phalcon)
* [Phalcon\Logger\Adapter\Firelogger](Library/Phalcon/Logger) - Adapter to log messages in the Firelogger console in Firebug (@phalcon)
* [Phalcon\Logger\Adapter\File\Multiple](Library/Phalcon/Logger) - Adapter to log to multiple files (@rlaffers)

## Mailer
* [Phalcon\Mailer\Manager](Library/Phalcon/Mailer) - Mailer wrapper over SwiftMailer (@KorsaR-ZN)

## Model MetaData Adapters
* [Phalcon\Mvc\Model\MetaData\Wincache](Library/Phalcon/Mvc/Model/MetaData) - Adapter for the Wincache php extension

## MVC
* [Phalcon\Mvc\MongoCollection](Library/Phalcon/MVC/MongoCollection) - Collection class for the new MongoDB Extension (@tigerstrikemedia)

## Template Engines
* [Phalcon\Mvc\View\Engine\Mustache](Library/Phalcon/Mvc/View/Engine) - Adapter for Mustache (@phalcon)
* [Phalcon\Mvc\View\Engine\Twig](Library/Phalcon/Mvc/View/Engine) - Adapter for Twig (@phalcon)
* [Phalcon\Mvc\View\Engine\Smarty](Library/Phalcon/Mvc/View/Engine) - Adapter for Smarty (@phalcon)

## Error Handling
* [Phalcon\Error](Library/Phalcon/Error) - Error handler used to centralize the error handling and displaying clean error pages (@theDisco)

## Queue
* [Phalcon\Queue\Beanstalk\Extended](Library/Phalcon/Queue/Beanstalk) - Extended class to access the beanstalk queue service (@endeveit)

## Test
* [Phalcon\Test\FunctionalTestCase](Library/Phalcon/Test) - Mvc app test case wrapper (@thecodeassassin)
* [Phalcon\Test\ModelTestCase](Library/Phalcon/Test) - Model test case wrapper (@thecodeassassin)
* [Phalcon\Test\UnitTestCase](Library/Phalcon/Test) - Generic test case wrapper (@thecodeassassin)

## Translate
* [Phalcon\Translate\Adapter\Database](Library/Phalcon/Translate/Adapter) - Translation adapter using relational databases (@phalcon)
* [Phalcon\Translate\Adapter\Mongo](Library/Phalcon/Translate/Adapter) - Implements a Mongo adapter for translations (@gguridi)
* [Phalcon\Translate\Adapter\ResourceBundle](Library/Phalcon/Translate/Adapter) - Translation adapter using ResourceBundle (@phalcon)

## Session
* [Phalcon\Session\Adapter\Aerospike](Library/Phalcon/Session/Adapter) - Aerospike adapter for storing sessions (@sergeyklay)
* [Phalcon\Session\Adapter\Database](Library/Phalcon/Session/Adapter) - Database adapter for storing sessions (@phalcon)
* [Phalcon\Session\Adapter\Mongo](Library/Phalcon/Session/Adapter) - MongoDb adapter for storing sessions (@phalcon)
* [Phalcon\Session\Adapter\HandlerSocket](Library/Phalcon/Session/Adapter) - HandlerSocket adapter for storing sessions (@Xrymz)

## Utils
* [Phalcon\Utils\Slug](Library/Phalcon/Utils) - Creates a slug for the passed string taking into account international characters. (@niden)
* [Phalcon\Avatar\Gravatar](Library/Phalcon/Avatar) - Provides an easy way to retrieve a user's profile image from Gravatar site based on a given email address (@sergeyklay)

## Validators
* [Phalcon\Validation\Validator\AlphaCompleteValidator](Library/Phalcon/Validation/AlphaCompleteValidator) - Validates a string containing alphanumeric, underscore, white spaces, slashes, apostrophes, brackets and punctuation characters. Optionally other characters can be allowed (@micheleangioni)
* [Phalcon\Validation\Validator\AlphaNamesValidator](Library/Phalcon/Validation/AlphaNamesValidator) - Validates a string containing alphanumeric, menus, apostrophe, underscore and white space characters. Optionally other numbers too can be allowed (@micheleangioni)
* [Phalcon\Validation\Validator\AlphaNumericValidator](Library/Phalcon/Validation/AlphaNumericValidator) - Validates a string containing alphanumeric characters. Optionally white spaces and underscores can be allowed (@micheleangioni)
* [Phalcon\Validation\Validator\CardNumber](Library/Phalcon/Validation/Validator) - Allows to validate credit card number using Luhn algorithm (@parshikov)
* [Phalcon\Validation\Validator\ConfirmationOf](Library/Phalcon/Validation/Validator) - Validates confirmation of other field value (@davihu)
* [Phalcon\Validation\Validator\Decimal](Library/Phalcon/Validation/Validator) - Allows to validate if a field has a valid number in proper decimal format (negative and decimal numbers allowed) (@sergeyklay)
* [Phalcon\Validation\Validator\IpValidator](Library/Phalcon/Validation/IpValidator) - Validates an ip address (@micheleangioni)
* [Phalcon\Validation\Validator\MongoId](Library/Phalcon/Validation/Validator) - Validate MongoId value (@Kachit)
* [Phalcon\Validation\Validator\NumericValidator](Library/Phalcon/Validation/NumericValidator) - Validates a numeric string. Optionally can contain and sign (+/-) and allow floats (@micheleangioni)
* [Phalcon\Validation\Validator\PasswordStrength](Library/Phalcon/Validation/Validator) - Validates password strength (@davihu)
* [Phalcon\Validation\Validator\ReCaptcha](Library/Phalcon/Validation/Validator) - The reCAPTCHA Validator (@pflorek)

## Traits

* [Phalcon\Traits\ConfigurableTrait](Library/Phalcon/Traits) - Allows to define parameters which can be set by passing them to the class constructor (@sergeyklay)
