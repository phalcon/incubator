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
    ini_set('xdebug.cli_color', 1);
    ini_set('xdebug.collect_params', 0);
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

// Memcached
define('TEST_MC_HOST', getenv('TEST_MC_HOST') ?: '127.0.0.1');
define('TEST_MC_PORT', getenv('TEST_MC_PORT') ?: 11211);

// Beanstalk
define('TEST_BT_HOST', getenv('TEST_BT_HOST') ?: '127.0.0.1');
define('TEST_BT_PORT', getenv('TEST_BT_PORT') ?: 11300);

// Aerospike
define('TEST_AS_HOST', getenv('TEST_AS_HOST') ?: '127.0.0.1');
define('TEST_AS_PORT', getenv('TEST_AS_PORT') ?: 3000);

// Database
define('TEST_DB_HOST', getenv('TEST_DB_HOST') ?: '127.0.0.1');
define('TEST_DB_PORT', getenv('TEST_DB_PORT') ?: 3306);
define('TEST_DB_USER', getenv('TEST_DB_USER') ?: 'root');
define('TEST_DB_PASSWD', getenv('TEST_DB_PASSWD') ?: '');
define('TEST_DB_NAME', getenv('TEST_DB_NAME') ?: 'incubator_tests');
define('TEST_DB_CHARSET', getenv('TEST_DB_CHARSET') ?: 'utf8');
