<?php

error_reporting(-1);
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

clearstatcache();

$root = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR;

defined('TESTS_PATH')   || define('TESTS_PATH', $root);
defined('PROJECT_PATH') || define('PROJECT_PATH', dirname(TESTS_PATH) . DIRECTORY_SEPARATOR);
defined('PATH_DATA')    || define('PATH_DATA', $root .  '_data' . DIRECTORY_SEPARATOR);
defined('PATH_CACHE')   || define('PATH_CACHE', $root . '_cache' . DIRECTORY_SEPARATOR);
defined('PATH_OUTPUT')  || define('PATH_OUTPUT', $root .  '_output' . DIRECTORY_SEPARATOR);
defined('INCUBATOR_FIXTURES') || define('INCUBATOR_FIXTURES', $root .  '_fixtures' . DIRECTORY_SEPARATOR);

// Memcached
define('TEST_MC_HOST', getenv('TEST_MC_HOST') ?: 'memcached');
define('TEST_MC_PORT', getenv('TEST_MC_PORT') ?: 11211);

// Beanstalk
define('TEST_BT_HOST', getenv('TEST_BT_HOST') ?: 'queue');
define('TEST_BT_PORT', getenv('TEST_BT_PORT') ?: 11300);

// Aerospike
define('TEST_AS_HOST', getenv('TEST_AS_HOST') ?: 'aerospike');
define('TEST_AS_PORT', getenv('TEST_AS_PORT') ?: 3000);

// MySQL
define('TEST_DB_HOST', getenv('TEST_DB_HOST') ?: 'mysql');
define('TEST_DB_PORT', getenv('TEST_DB_PORT') ?: 3306);
define('TEST_DB_USER', getenv('TEST_DB_USER') ?: 'root');
define('TEST_DB_PASSWD', getenv('TEST_DB_PASSWD') ?: '');
define('TEST_DB_NAME', getenv('TEST_DB_NAME') ?: 'incubator_tests');
define('TEST_DB_CHARSET', getenv('TEST_DB_CHARSET') ?: 'utf8');
