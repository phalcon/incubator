<?php

error_reporting(E_ALL | E_STRICT);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if (extension_loaded('xdebug')) {
    ini_set('xdebug.collect_params', 4);
}

if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    throw new \RuntimeException('Unable to locate autoloader. Install dependencies from the project root directory to run test suite: `composer install`.');
}

require __DIR__ . '/../vendor/autoload.php';
