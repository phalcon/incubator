<?php

require_once realpath(dirname(__DIR__)) . '/vendor/autoload.php';

(Dotenv\Dotenv::create(realpath(__DIR__)))->load();

require_once '_support/functions.php';

error_reporting(-1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

setlocale(LC_ALL, 'en_US.utf-8');

if (extension_loaded('xdebug')) {
    ini_set('xdebug.cli_color', 1);
    ini_set('xdebug.collect_params', 0);
    ini_set('xdebug.dump_globals', 'on');
    ini_set('xdebug.show_local_vars', 'on');
    ini_set('xdebug.max_nesting_level', 100);
    ini_set('xdebug.var_display_max_depth', 4);
}

ini_set('apc.enable_cli', 'on');

clearstatcache();

$root = realpath(__DIR__) . DIRECTORY_SEPARATOR;

define('TESTS_PATH', $root);

define(
    'PROJECT_PATH',
    dirname(TESTS_PATH) . DIRECTORY_SEPARATOR
);

define(
    'PATH_DATA',
    $root . '_data' . DIRECTORY_SEPARATOR
);

define(
    'PATH_CACHE',
    $root . '_cache' . DIRECTORY_SEPARATOR
);

define(
    'PATH_OUTPUT',
    $root . '_output' . DIRECTORY_SEPARATOR
);

define(
    'INCUBATOR_FIXTURES',
    $root . '_fixtures' . DIRECTORY_SEPARATOR
);

unset($root);
