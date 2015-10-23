<?php

define('INCUBATOR_FIXTURES', __DIR__ . '/_fixtures/');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

setlocale(LC_ALL, 'en_US.utf-8');

if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('utf-8');
}

if (function_exists('mb_substitute_character')) {
    mb_substitute_character('none');
}

if (extension_loaded('xdebug')) {
    ini_set('xdebug.collect_vars', 'on');
    ini_set('xdebug.collect_params', 4);
    ini_set('xdebug.dump_globals', 'on');
    ini_set('xdebug.show_local_vars', 'on');
    ini_set('xdebug.max_nesting_level', 100);
    ini_set('xdebug.var_display_max_depth', 4);
}

if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    throw new \RuntimeException(
        'Unable to locate autoloader. ' .
        'Install dependencies from the project root directory to run test suite: `composer install`.'
    );
}

require __DIR__ . '/../vendor/autoload.php';

$host = getenv('TEST_BT_HOST');
$port = getenv('TEST_BT_HOST');

// Beanstalk
defined('TEST_BT_HOST') || define('TEST_BT_HOST', $host ?: '127.0.0.1');
defined('TEST_BT_PORT') || define('TEST_BT_PORT', $port ?: 11300);

$host = getenv('TEST_MC_HOST');
$port = getenv('TEST_MC_PORT');

// Memcached
defined('TEST_MC_HOST') || define('TEST_MC_HOST', $host ?: '127.0.0.1');
defined('TEST_MC_PORT') || define('TEST_MC_PORT', $port ?: 11211);

unset($host, $port);
