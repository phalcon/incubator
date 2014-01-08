<?php
require __DIR__ . '/../vendor/autoload.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// loader
$loader = new \Phalcon\Loader();
$loader->registerNamespaces(array
(
    // stubs
    'Phalcon\Tests\Stubs' => __DIR__ . '/stubs/Phalcon/',
));
$loader->register();