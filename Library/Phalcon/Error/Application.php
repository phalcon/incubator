<?php

/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2012 Phalcon Team (http://www.phalconphp.com)       |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file docs/LICENSE.txt.                        |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Andres Gutierrez <andres@phalconphp.com>                      |
  |          Eduar Carvajal <eduar@phalconphp.com>                         |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Error;

use Phalcon\Error\Handler as ErrorHandler;

class Application extends \Phalcon\Mvc\Application
{
	const ENV_PRODUCTION = 'production';
	const ENV_STAGING = 'staging';
	const ENV_TEST = 'test';
	const ENV_DEVELOPMENT = 'development';

	/**
	 * Class constructor registers autoloading and error
	 * handler.
	 *
	 * @return \Phalcon\Error\Application
	 */
	public function __construct()
	{
		$this->_registerAutoloaders();
		ErrorHandler::register();
	}

	/**
	 * Registers the services and dispatches the application.
	 *
	 * @return \Phalcon\Http\Response
	 */
	public function main()
	{
		$this->_registerServices();
		return $this->handle()->send();
	}

	/**
	 * Registers the services in di container.
	 *
	 * @return void
	 */
	private function _registerServices()
	{
		$di = new \Phalcon\DI\FactoryDefault();

		$di->set('config', function() {
			ob_start();
			$config = include APPLICATION_ENV . '.php';
			ob_end_clean();
			return new Phalcon\Config($config);
		});

		$di->set('dispatcher', function(){
			$dispatcher = new \Phalcon\Mvc\Dispatcher();
			$dispatcher->setDefaultNamespace('Application\Controllers\\');
			return $dispatcher;
		});

		$di->set('view', function() {
			$view = new \Phalcon\Mvc\View();
			$view->setViewsDir(ROOT_PATH . '/application/views/');
			return $view;
		});

		$this->setDI($di);
	}

	/**
	 * Registers autoloading.
	 *
	 * @return void
	 */
	private function _registerAutoloaders()
	{
		$loader = new \Phalcon\Loader();
		$loader->registerNamespaces([
			'Phalcon\Error' => '.',
		]);
		$loader->register();
	}

}