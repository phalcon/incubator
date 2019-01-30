<?php

namespace Phalcon\Test\Mvc;

use Phalcon\Di;
use MongoDB\BSON\ObjectID;
use MongoDB\Driver\Cursor;
use Phalcon\Test\Codeception\UnitTestCase as Test;
use Phalcon\Mvc\MongoCollection;
use Phalcon\Test\Collections\Cars;
use Phalcon\Mvc\Collection\Manager;
use Phalcon\Test\Collections\Heroes;
use Phalcon\Db\Adapter\MongoDB\Client;

/**
 * \Phalcon\Test\Mvc\CollectionsTest
 * Tests for Phalcon\Mvc\MongoCollection component
 *
 * @copyright (c) 2011-2016 Phalcon Team
 * @link      https://www.phalconphp.com
 * @author    Ben Casey <bcasey@tigerstrikemedia.com>
 * @package   Phalcon\Test\Mvc
 * @group     Db
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class CollectionsTest extends Test
{
    /**
     * Executed before each test
     */
    protected function _before()
    {
        parent::_before();

        if (!extension_loaded('mongodb')) {
            $this->markTestSkipped('mongodb extension not loaded');
        }

        $this->di->set('mongo', function() {
            $dsn = 'mongodb://' . env('TEST_MONGODB_HOST', '127.0.0.1') . ':' . env('TEST_MONGODB_PORT', 27017);
            $mongo = new Client($dsn);

            return $mongo->selectDatabase(env('TEST_MONGODB_NAME', 'incubator'));
        });

        $this->di->set('collectionManager', Manager::class);
    }

    public function testCollectionsSave()
    {
       $car = new Cars();
        $car->manufacturer = 'Mclaren';
        $car->model = '650S';
        $car->rank = 1;
        $success = $car->save();

        $this->assertTrue($success);

        $this->clearData();
    }

    /**
     * @depends testCollectionsSave
     */
    public function testCollectionsDelete()
    {
        $this->loadData();

        /** @var Cars[] $cars */
        $cars = Cars::find();

        foreach ($cars as $car) {
            $this->assertTrue($car->delete());
        }
    }

    /**
     * @depends testCollectionsSave
     * @depends testCollectionsDelete
     */
    public function testCollectionsFind()
    {
        $this->loadData();

        // Without Params
        $cars = Cars::find();

        $this->assertTrue(is_array($cars));

        $this->assertInstanceOf(MongoCollection::class, $cars[0]);

        $this->assertCount(5, $cars);

        // With Params
        $ferraris = Cars::find([
            ['manufacturer' => 'Ferrari']
        ]);

        $this->assertCount(2, $ferraris);

        // Limit
        $ferraris = Cars::find([
            ['manufacturer' => 'Ferrari'],
            'limit' => 1
        ]);
        $this->assertCount(1, $ferraris);
        $this->assertEquals('488 GTB', $ferraris[0]->model);

        // Skip
        $ferraris = Cars::find([
            ['manufacturer' => 'Ferrari'],
            'limit' => 1,
            'skip' => 1
        ]);
        $this->assertCount(1, $ferraris);
        $this->assertEquals('LaFerrari', $ferraris[0]->model);

        // Sort ASC
        $ferraris = Cars::find([
            ['manufacturer' => 'Ferrari'],
            'sort' => [
                'rank' => 1
            ],
        ]);
        $this->assertCount(2, $ferraris);
        $this->assertEquals('488 GTB', $ferraris[0]->model);

        // Sort DESC
        $ferraris = Cars::find([
            ['manufacturer' => 'Ferrari'],
            'sort' => [
                'rank' => -1
            ],
        ]);
        $this->assertCount(2, $ferraris);
        $this->assertEquals('LaFerrari', $ferraris[0]->model);

        // Fields
        $cars = Cars::find([
            'fields' => [
                'manufacturer' => true,
                'model' => true,
            ]
        ]);
        $this->assertCount(5, $cars);
        $this->assertObjectNotHasAttribute('rank', $cars[0]);

        // $gt
        $cars = Cars::find([
            [
                'rank' => [ '$gt' => 2 ]
            ]
        ]);
        $this->assertCount(3, $cars);
        $this->assertEquals('488 GTB', $cars[0]->model);

        // $lt
        $cars = Cars::find([
            [
                'rank' => [ '$lt' => 3 ]
            ]
        ]);
        $this->assertCount(2, $cars);
        $this->assertEquals('650S', $cars[0]->model);

        // More Complex Query
        $cars = Cars::find([
            [
                'rank' => [ '$lt' => 3 ]
            ],
            'fields' => [
                'model' => true,
                'rank' => true,
            ],
            'sort' => [
                'rank' => -1
            ]
        ]);
        $this->assertCount(2, $cars);
        $this->assertEquals('911 GT3', $cars[0]->model);
        $this->assertObjectNotHasAttribute('manufacturer', $cars[0]);

        $this->clearData();
    }

    /**
     * @depends testCollectionsSave
     * @depends testCollectionsDelete
     */
    public function testCollectionsFindFirst()
    {
        $this->loadData();

        $car = Cars::findFirst();
        $this->assertInstanceOf(MongoCollection::class, $car);
        $this->assertEquals('650S', $car->model);

        $car = Cars::findFirst([
            [ 'manufacturer' => 'Ferrari' ]
        ]);
        $this->assertEquals('488 GTB', $car->model);

        return $car;
    }

    /**
     * @depends testCollectionsFindFirst
     * @depends testCollectionsSave
     * @depends testCollectionsDelete
     * @param Cars $car
     */
    public function testCollectionsFindById($car)
    {
        $id = $car->getId();
        $this->assertInstanceOf(ObjectID::class, $id);

        /** @var Cars $car */
        $car = Cars::findById($id);
        $this->assertEquals('488 GTB', $car->model);

        $this->clearData();
    }

    /**
     * @depends testCollectionsSave
     * @depends testCollectionsDelete
     */
    public function testCollectionsCount()
    {
        $this->loadData();

        $count = Cars::count();

        $this->assertEquals(5, $count, "Found $count cars, Expecting 5");

        $count2 = Cars::count([
            ['manufacturer' => 'Ferrari']
        ]);

        $this->assertEquals(2, $count2);

        $this->clearData();
    }

    public function testCollectionsAggregate()
    {
        $this->loadData();

        /** @var Cursor $data */
        $data = Cars::aggregate([
            [
                '$match' => [ 'manufacturer' => 'Ferrari' ]
            ],
            [
                '$group' => [
                    '_id' => '$manufacturer',
                    'total' => [ '$sum' =>'$value' ]
                ]
            ]
        ]);

        $this->assertInstanceOf(Cursor::class, $data);

        $results = $data->toArray();

        $this->assertEquals('Ferrari', $results[0]['_id']);
        $this->assertEquals(700000, $results[0]['total']);

        $this->clearData();
    }

    /**
     * @test
     * @issue 696
     */
    public function shouldInsertNewDocument()
    {
        $hero = new Heroes();
        $hero->name = 'Phalcon contributor';

        $this->assertTrue($hero->create());
        $this->assertInstanceOf(MongoCollection::class, $hero);
        $this->assertInstanceOf(ObjectID::class, $hero->getId());
        $this->assertSame('Phalcon contributor', $hero->name);
    }

    public function testSaveOnFound()
    {
        $this->loadData();
        $car = Cars::findFirst();
        $car->model = 'Other Model';
        $this->assertTrue($car->save());
        $car = Cars::findFirst();
        $this->assertEquals('Other Model', $car->model);
        $this->clearData();
    }

    protected function loadData()
    {
        $car = new Cars();
        $car->manufacturer = 'Mclaren';
        $car->model = '650S';
        $car->rank = 1;
        $car->value = 500000;
        $car->save();

        $car = new Cars();
        $car->manufacturer = 'Porsche';
        $car->model = '911 GT3';
        $car->rank = 2;
        $car->value = 450000;
        $car->save();

        $car = new Cars();
        $car->manufacturer = 'Ferrari';
        $car->model = '488 GTB';
        $car->rank = 3;
        $car->value = 400000;
        $car->save();

        $car = new Cars();
        $car->manufacturer = 'Porsche';
        $car->model = '918 Spyder';
        $car->rank = 4;
        $car->value = 350000;
        $car->save();

        $car = new Cars();
        $car->manufacturer = 'Ferrari';
        $car->model = 'LaFerrari';
        $car->rank = 5;
        $car->value = 300000;
        $car->save();
    }

    protected function clearData()
    {
        $count = Cars::count();

        if ($count > 0) {
            /** @var Cars[] $cars */
            $cars = Cars::find();
            foreach ($cars as $car) {
                $car->delete();
            }
        }
    }
}
