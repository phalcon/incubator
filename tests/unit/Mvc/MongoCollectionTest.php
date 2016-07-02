<?php
namespace Phalcon\Test\Mvc;

use Phalcon\Di;
use Phalcon\Mvc\Collection\Manager;
use Phalcon\Mvc\Model\Message as ModelMessage;
use Phalcon\Mvc\MongoCollection;
use Phalcon\Db\Adapter\MongoDB\Client as MongoClient;
use Codeception\TestCase\Test;
use Phalcon\Test\Collections\Cars;

class CollectionsTest extends Test
{

    protected function _before()
    {

        if (!extension_loaded('MongoDB')) {
            $this->markTestSkipped("MongoDB extension not loaded, test skipped");
            return;
        }

        Di::reset();
        $di = new DI();
        $di->set('mongo', function(){
            $mongo = new MongoClient( 'mongodb://' . TEST_MONGODB_HOST . ':' . TEST_MONGODB_PORT );
            return $mongo->selectDatabase( 'phalcon_test' );
        });
        $di->set('collectionManager', function(){
            return new Manager();
        });

    }

    public function loadData()
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

    public function clearData()
    {
        $count = Cars::count();

        if( $count > 0 ){
            $cars = Cars::find();
            foreach( $cars as $car ){
                $car->delete();
            }
        }
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

        $cars = Cars::find();

        foreach($cars as $car){
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

        //Without Params
        $cars = Cars::find();

        $this->assertTrue(is_array($cars));

        $this->assertInstanceOf( 'Phalcon\Mvc\MongoCollection', $cars[0] );

        $this->assertCount(5, $cars);

        //With Params
        $ferraris = Cars::find([
            ['manufacturer' => 'Ferrari']
        ]);

        $this->assertCount(2, $ferraris);

        //Limit
        $ferraris = Cars::find([
            ['manufacturer' => 'Ferrari'],
            'limit' => 1
        ]);
        $this->assertCount(1, $ferraris);
        $this->assertEquals( '488 GTB', $ferraris[0]->model );

        //Skip
        $ferraris = Cars::find([
            ['manufacturer' => 'Ferrari'],
            'limit' => 1,
            'skip' => 1
        ]);
        $this->assertCount(1, $ferraris);
        $this->assertEquals( 'LaFerrari', $ferraris[0]->model );

        //Sort ASC
        $ferraris = Cars::find([
            ['manufacturer' => 'Ferrari'],
            'sort' => [
                'rank' => 1
            ],
        ]);
        $this->assertCount(2, $ferraris);
        $this->assertEquals( '488 GTB', $ferraris[0]->model );

        //Sort DESC
        $ferraris = Cars::find([
            ['manufacturer' => 'Ferrari'],
            'sort' => [
                'rank' => -1
            ],
        ]);
        $this->assertCount(2, $ferraris);
        $this->assertEquals( 'LaFerrari', $ferraris[0]->model );

        //Fields
        $cars = Cars::find([
            'fields' => [
                'manufacturer' => true,
                'model' => true,
            ]
        ]);
        $this->assertCount(5, $cars);
        $this->assertObjectNotHasAttribute( 'rank', $cars[0] );

        //$gt
        $cars = Cars::find([
            [
                'rank' => [ '$gt' => 2 ]
            ]
        ]);
        $this->assertCount(3, $cars);
        $this->assertEquals( '488 GTB', $cars[0]->model );

        //$lt
        $cars = Cars::find([
            [
                'rank' => [ '$lt' => 3 ]
            ]
        ]);
        $this->assertCount(2, $cars);
        $this->assertEquals( '650S', $cars[0]->model );

        //More Complex Query
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
        $this->assertEquals( '911 GT3', $cars[0]->model );
        $this->assertObjectNotHasAttribute( 'manufacturer', $cars[0] );

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
        $this->assertInstanceOf( '\Phalcon\Mvc\MongoCollection', $car );
        $this->assertEquals( '650S', $car->model );

        $car = Cars::findFirst([
            [ 'manufacturer' => 'Ferrari' ]
        ]);
        $this->assertEquals( '488 GTB', $car->model );

        return $car;

    }

    /**
     * @depends testCollectionsFindFirst
     * @depends testCollectionsSave
     * @depends testCollectionsDelete
     */
    public function testCollectionsFindById( $car )
    {

        $id = $car->getId();
        $this->assertInstanceOf( 'MongoDB\BSON\ObjectID', $id );

        $car = Cars::findById( $id );
        $this->assertEquals( '488 GTB', $car->model );

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

        $this->assertInstanceOf( 'MongoDB\Driver\Cursor', $data );

        $results = $data->toArray();
        
        $this->assertEquals( 'Ferrari', $results[0]['_id'] );
        $this->assertEquals( 700000, $results[0]['total'] );

        $this->clearData();

    }

}