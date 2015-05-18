<?php

error_reporting(E_ALL | E_STRICT);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if (!is_readable(__DIR__ . '/../vendor/autoload.php')) {
    throw new \RuntimeException('Unable to locate autoloader. Run `composer install` from the project root directory.');
}

require __DIR__ . '/../vendor/autoload.php';
