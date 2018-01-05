<?php

/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-present Phalcon Team (https://www.phalconphp.com)   |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file LICENSE.txt.                             |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Wojciech Ślawski <jurigag@gmail.com>                          |
  +------------------------------------------------------------------------+
*/

namespace Helper;

use Codeception\Actor;
use Phalcon\Mvc\Collection\Manager;
use Phalcon\Db\Adapter\MongoDB\Client;
use Phalcon\Di;

/**
 * Collection Initializer
 *
 * @package Helper
 */
trait CollectionTrait
{
    /**
     * Executed before each test
     */
    protected function setupMongo(Actor $I)
    {
        if (!extension_loaded('mongodb')) {
            throw new \PHPUnit_Framework_SkippedTestError('mongodb extension not loaded');
        }

        Di::reset();

        $di = new Di();
        $di->set('mongo', function() {
            $dsn = 'mongodb://' . env('TEST_MONGODB_HOST', '127.0.0.1') . ':' . env('TEST_MONGODB_PORT', 27017);
            $mongo = new Client($dsn);

            return $mongo->selectDatabase(env('TEST_MONGODB_NAME', 'incubator'));
        });

        $di->set('collectionManager', Manager::class);
    }
}
