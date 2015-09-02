<?php
/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2015 Phalcon Team (http://www.phalconphp.com)       |
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
  |          Serghei Iakovlev <serghei@phalconphp.com>                     |
  +------------------------------------------------------------------------+
*/
namespace Phalcon\Error;

use Phalcon\Di;
use Phalcon\Logger\Formatter;

class Handler
{
    /**
     * Registers itself as error and exception handler.
     *
     * @return void
     */
    public static function register()
    {
        switch (APPLICATION_ENV) {
            case Application::ENV_PRODUCTION:
            case Application::ENV_STAGING:
            default:
                ini_set('display_errors', 0);
                error_reporting(0);
                break;
            case Application::ENV_TEST:
            case Application::ENV_DEVELOPMENT:
                ini_set('display_errors', 1);
                error_reporting(-1);
                break;
        }

        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            if (!($errno & error_reporting())) {
                return;
            }

            $options = [
                'type'    => $errno,
                'message' => $errstr,
                'file'    => $errfile,
                'line'    => $errline,
                'isError' => true,
            ];

            static::handle(new Error($options));
        });

        set_exception_handler(function (\Exception $e) {
            $options = [
                'type'        => $e->getCode(),
                'message'     => $e->getMessage(),
                'file'        => $e->getFile(),
                'line'        => $e->getLine(),
                'isException' => true,
                'exception'   => $e,
            ];

            static::handle(new Error($options));
        });

        register_shutdown_function(function () {
            if (!is_null($options = error_get_last())) {
                static::handle(new Error($options));
            }
        });
    }

    /**
     * Logs the error and dispatches an error controller.
     *
     * @param  \Phalcon\Error\Error $error
     * @return mixed
     */
    public static function handle(Error $error)
    {
        $di = Di::getDefault();
        $config = $di->getShared('config')->error;
        $type = static::getErrorType($error->type());
        $message = "$type: {$error->message()} in {$error->file()} on line {$error->line()}";

        if (isset($config->formatter) && $config->formatter instanceof Formatter) {
            $config->logger->setFormatter($config->formatter);
        }

        $config->logger->log($message);

        switch ($error->type()) {
            case E_WARNING:
            case E_NOTICE:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
            case E_USER_NOTICE:
            case E_STRICT:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
            case E_ALL:
                break;
            case 0:
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
                if ($di->has('view')) {
                    $dispatcher = $di->getShared('dispatcher');
                    $view = $di->getShared('view');
                    $response = $di->getShared('response');

                    $dispatcher->setControllerName($config->controller);
                    $dispatcher->setActionName($config->action);
                    $dispatcher->setParams(['error' => $error]);

                    $view->start();
                    $dispatcher->dispatch();
                    $view->render($config->controller, $config->action, $dispatcher->getParams());
                    $view->finish();

                    return $response->setContent($view->getContent())->send();
                } else {
                    echo sprintf('%s in %s:%s', $error->message(), $error->file(), $error->line());
                }
        }
    }

    /**
     * Maps error code to a string.
     *
     * @param  integer $code
     * @return string
     */
    public static function getErrorType($code)
    {
        switch ($code) {
            case 0:
                return 'Uncaught exception';
            case E_ERROR:
                return 'E_ERROR';
            case E_WARNING:
                return 'E_WARNING';
            case E_PARSE:
                return 'E_PARSE';
            case E_NOTICE:
                return 'E_NOTICE';
            case E_CORE_ERROR:
                return 'E_CORE_ERROR';
            case E_CORE_WARNING:
                return 'E_CORE_WARNING';
            case E_COMPILE_ERROR:
                return 'E_COMPILE_ERROR';
            case E_COMPILE_WARNING:
                return 'E_COMPILE_WARNING';
            case E_USER_ERROR:
                return 'E_USER_ERROR';
            case E_USER_WARNING:
                return 'E_USER_WARNING';
            case E_USER_NOTICE:
                return 'E_USER_NOTICE';
            case E_STRICT:
                return 'E_STRICT';
            case E_RECOVERABLE_ERROR:
                return 'E_RECOVERABLE_ERROR';
            case E_DEPRECATED:
                return 'E_DEPRECATED';
            case E_USER_DEPRECATED:
                return 'E_USER_DEPRECATED';
        }

        return $code;
    }
}
