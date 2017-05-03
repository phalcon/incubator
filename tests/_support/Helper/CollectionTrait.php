<?php

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
            $I->markTestSkipped('mongodb extension not loaded');
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
