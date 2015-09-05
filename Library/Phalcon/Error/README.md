# Phalcon\Error

Error handler used to centralize the error handling and displaying clean error pages.

## Configuration

For the error handler to work properly, following section has to be created in the configuration file (in this case php array).

```php
use Phalcon\Logger\Adapter\File as FileLogger;
use Phalcon\Logger\Formatter\Line as LineFormatter;

return [
	'error' => [
		'logger'     => new FileLogger(ROOT_PATH . '/log/' . APPLICATION_ENV . '.log'),
		'formatter'  => new LineFormatter('[%date%][%type%] %message%', 'Y-m-d H:i:s O'),
		'controller' => 'error',
		'action'     => 'index',
	]
];

```

| Param        | Description                                                                                                      | Optional |
| ------------ | ---------------------------------------------------------------------------------------------------------------- | -------- |
| `logger`     | Defines an object used for logging. It has to implement `log` method in order for error handler to work properly | No       |
| `formatter`  | Sets the message formatter                                                                                       | Yes      |
| `controller` | Is the name of error controller, which will be dispatched, when an exception or error occurs                     | No       |
| `action`     | Is the name of action in the error controller, which will be called, when an exception or error occurs           | No       |

In the Application file (please take a look at `\Phalcon\Error\Application` for example)
error handler has to be registered. Application must also define constants for application environments:

```php
class Application extends \Phalcon\Mvc\Application
{
	const ENV_PRODUCTION = 'production';
	const ENV_STAGING = 'staging';
	const ENV_TEST = 'test';
	const ENV_DEVELOPMENT = 'development';

	public function __construct(DiInterface $dependencyInjector = null)
	{
		$this->_registerAutoloaders();
		ErrorHandler::register();
		
		parent::__construct($dependencyInjector);
	}
}
```

In the error controller `\Phalcon\Error\Error` can be retrieved through the dispatcher:

```php
public function indexAction()
{
	/** @var \Phalcon\Error\Error $error */
	$error = $this->dispatcher->getParam('error');

	switch ($error->type()) {
		case 404:
			$code = 404;
			break;
		case 403:
			$code = 403;
			break;
		case 401:
			$code = 401;
			break;
		default:
			$code = 500;
	}

	$this->response->resetHeaders()->setStatusCode($code, null);

	$this->view->setVars([
		'error' => $error,
		'code'  => $code,
		'dev'   => APPLICATION_ENV != \Phalcon\Error\Application::ENV_PRODUCTION
	]);
}
```

Error message could be displayed to the user this way:

```php

<h1>Error <?php echo $code ?></h1>

<?php echo $error->message(); ?>
<?php if ($dev): ?>
	<br>in <?php echo $error->file(); ?> on line <?php echo $error->line(); ?><br>
	<?php if ($error->isException()) { ?>
		<pre><?php echo $error->exception()->getTraceAsString(); ?></pre>
	<?php } ?>
<?php endif; ?>
```
