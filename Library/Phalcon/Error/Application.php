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
  |          Nikita Vershinin <endeveit@gmail.com>                         |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Error;

use Phalcon\Config;
use Phalcon\DI\FactoryDefault;
use Phalcon\Error\Handler as ErrorHandler;
use Phalcon\Loader;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\View;

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
        $this->registerAutoloaders();

        ErrorHandler::register();
    }

    /**
     * Registers the services and dispatches the application.
     *
     * @return \Phalcon\Http\Response
     */
    public function main()
    {
        $this->registerServices();

        return $this->handle()->send();
    }

    /**
     * Registers autoloading.
     *
     * @return void
     */
    private function registerAutoloaders()
    {
        $loader = new Loader();
        $loader->registerNamespaces(array(
            'Phalcon\Error' => '.',
        ));
        $loader->register();
    }

    /**
     * Registers the services in di container.
     *
     * @return void
     */
    private function registerServices()
    {
        $di = new FactoryDefault();

        $di->set('config', function () {
            ob_start();
            $config = include APPLICATION_ENV . '.php';
            ob_end_clean();

            return new Config($config);
        });

        $di->set('dispatcher', function () {
            $dispatcher = new Dispatcher();
            $dispatcher->setDefaultNamespace('Application\Controllers\\');

            return $dispatcher;
        });

        $di->set('view', function () {
            $view = new View();
            $view->setViewsDir(ROOT_PATH . '/application/views/');

            return $view;
        });

        $this->setDI($di);
    }

}
