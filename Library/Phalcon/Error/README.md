Phalcon\Error
=======================

Error handler used to centralize the error handling and displaying clean
error pages.

Configuration
-------------

For the error handler to work properly, following section has to be created
in the configuration file (in this case php array). The `logger`, `controller`, `action` options are mandatory:

```php
<?php
return [
	'error' => [
		'logger' => new \Phalcon\Logger\Adapter\File(ROOT_PATH . '/log/' . APPLICATION_ENV . '.log'),
		'formatter' => new \Phalcon\Logger\Formatter\Line('[%date%][%type%] %message%', 'Y-m-d H:i:s O'),
		'controller' => 'error',
		'action' => 'index',
	]
];

```

* `logger` defines an object used for logging. It has to implement `log` method in order for
error handler to work properly.
* `formatter` sets the message formatter.
* `controller` is the name of error controller, which will be dispatched, when an exception or error
occurs.
* `action` is the name of action in the error controller, which will be called, when an exception or error
occurs.

In the Application file (please take a look at \Phalcon\Error\Application for reference)
error handler has to be registered. Application must also define constants for application environments:

```php
<?php
class Application extends \Phalcon\Mvc\Application
{
	const ENV_PRODUCTION = 'production';
	const ENV_STAGING = 'staging';
	const ENV_TEST = 'test';
	const ENV_DEVELOPMENT = 'development';

	public function __construct()
	{
		$this->_registerAutoloaders();
		ErrorHandler::register();
	}
}

```

In the error controller \Phalcon\Error\Error can be retrieved through the dispatcher:

```php
public function indexAction()
{
	$error = $this->dispatcher->getParam('error');

	switch ($error->code()) {
		case 404:
			$code = 404;
			break;
		default:
			$code = 500;
	}

	$this->getDi()->getShared('response')->resetHeaders()->setStatusCode($code, null);
	$this->view->setVar('error', $error);
}

```

Error message could be displayed to the user this way:

```php
<?php echo $error->message(); ?>
<?php if (APPLICATION_ENV != \Phalcon\Error\Application::ENV_PRODUCTION): ?>
	<br>in <?php echo $error->file(); ?> on line <?php echo $error->line(); ?><br>
	<?php if ($error->isException()) { ?>
		<pre><?php echo $error->exception()->getTraceAsString(); ?></pre>
	<?php } ?>
<?php endif; ?>
```
