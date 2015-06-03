<?php namespace Phalcon\Test\Mvc\Model\EagerLoading;

use ReflectionClass;
use EagerLoadingTestModel\Robot;
use EagerLoadingTestModel\Manufacturer;
use EagerLoadingTestModel\Bug;
use EagerLoadingTestModel\NotSupportedRelation;
use EagerLoadingTestModel\Part;
use EagerLoadingTestModel\Purpose;
use EagerLoadingTestModel\RobotPart;
use Phalcon\DI;
use Phalcon\Mvc\Model\Metadata\Memory as MemoryMetadata;
use Phalcon\Mvc\Model\Manager as ModelsManager;
use Phalcon\Db\Adapter\Pdo\Mysql as MysqlAdapter;
use Phalcon\Mvc\Model\Resultset\Simple as SimpleResultset;
use Phalcon\Mvc\Model\EagerLoading\Loader;

EagerLoadingTest::setUpBeforeClassAndDataProviders();

class EagerLoadingTest extends \PHPUnit_Framework_TestCase
{
    protected static $previousDependencyInjector;

    public static function setUpBeforeClassAndDataProviders()
    {
        self::$previousDependencyInjector = DI::getDefault();

        $di = new DI;

        $di->set('modelsMetadata', function () {
            return new MemoryMetadata;
        }, true);

        $di->set('modelsManager', function () {
            return new ModelsManager;
        }, true);

        $di->set('db', function () {
            return new MysqlAdapter(array (
                'host'     => 'localhost',
                'port'     => '3306',
                'username' => 'root',
                'password' => '',
                'dbname'   => 'eager_loading_tests',
                'charset'  => 'utf8mb4',
            ));
        }, true);

        if (self::$previousDependencyInjector instanceof DI) {
            DI::setDefault($di);
        }

        spl_autoload_register(array (__CLASS__, 'autoloadModels'), true, true);
    }

    public static function tearDownAfterClass()
    {
        if (self::$previousDependencyInjector instanceof DI) {
            DI::setDefault(self::$previousDependencyInjector);
        }

        spl_autoload_unregister(array (__CLASS__, 'autoloadModels'));
    }

    public static function autoloadModels($class)
    {
        $len = strlen($prefix);
        
        if (strpos($class, 'EagerLoadingTestModel\\') === 0) {
            $class = substr($class, strlen('EagerLoadingTestModel\\'));

            if (ctype_alpha($class)) {
                $file = __DIR__ . "/resources/Models/{$class}.php";

                if (file_exists($file)) {
                    require $file;
                }
            }
        }
    }

    public function testBelongsTo()
    {
        $rawly = Bug::findFirstById(1);
        $rawly->robot;

        $eagerly = Loader::fromModel(Bug::findFirstById(1), 'Robot');

        $this->assertTrue(property_exists($eagerly, 'robot'));
        $this->assertInstanceOf('EagerLoadingTestModel\Robot', $eagerly->robot);
        $this->assertEquals($rawly->robot->readAttribute('id'), $eagerly->robot->readAttribute('id'));

        // Reverse
        $rawly = Robot::findFirstById(2);
        $rawly->bugs = $this->resultSetToEagerLoadingEquivalent($rawly->bugs);

        $eagerly = Loader::fromModel(Robot::findFirstById(2), 'Bugs');

        $this->assertTrue(property_exists($eagerly, 'bugs'));
        $this->assertContainsOnlyInstancesOf('EagerLoadingTestModel\Bug', $eagerly->bugs);

        $getIds = function ($obj) {
            return $obj->readAttribute('id');
        };

        $this->assertEquals(array_map($getIds, $rawly->bugs), array_map($getIds, $eagerly->bugs));
        $this->assertEmpty(Loader::fromModel(Robot::findFirstById(1), 'Bugs')->bugs);

        // Test from multiple
        $rawly = $this->resultSetToEagerLoadingEquivalent(Bug::find(array ('limit' => 10)));
        foreach ($rawly as $bug) {
            $bug->robot;
        }

        $eagerly = Loader::fromResultset(Bug::find(array ('limit' => 10)), 'Robot');

        $this->assertTrue(is_array($eagerly));
        $this->assertTrue(array_reduce($eagerly, function ($res, $bug) {
            return $res && property_exists($bug, 'robot');
        }, true));

        $getIds = function ($obj) {
            return property_exists($obj, 'robot') && isset ($obj->robot) ? $obj->robot->readAttribute('id') : null;
        };

        $this->assertEquals(array_map($getIds, $rawly), array_map($getIds, $eagerly));
    }

    public function testBelongsToDeep()
    {
        $rawly = Manufacturer::findFirstById(1);
        $rawly->robots = $this->resultSetToEagerLoadingEquivalent($rawly->robots);

        foreach ($rawly->robots as $robot) {
            $robot->parent;
        }

        $eagerly = Loader::fromModel(Manufacturer::findFirstById(1), 'Robots.Parent');

        $this->assertTrue(property_exists($eagerly->robots[0], 'parent'));
        $this->assertNull($eagerly->robots[0]->parent);
        $this->assertInstanceOf('EagerLoadingTestModel\Robot', $eagerly->robots[2]->parent);

        $getIds = function ($obj) {
            return property_exists($obj, 'parent') && isset ($obj->parent) ? $obj->parent->readAttribute('id') : null;
        };

        $this->assertEquals(array_map($getIds, $eagerly->robots), array_map($getIds, $rawly->robots));
    }

    public function testHasOne()
    {
        $rawly = Robot::findFirstById(1);
        $rawly->purpose;

        $eagerly = Loader::fromModel(Robot::findFirstById(1), 'Purpose');

        $this->assertTrue(property_exists($eagerly, 'purpose'));
        $this->assertInstanceOf('EagerLoadingTestModel\Purpose', $eagerly->purpose);
        $this->assertEquals($rawly->purpose->readAttribute('id'), $eagerly->purpose->readAttribute('id'));
    }

    public function testHasMany()
    {
        $rawly = Manufacturer::findFirstById(1);
        $rawly->robots;

        $eagerly = Loader::fromModel(Manufacturer::findFirstById(1), 'Robots');

        $this->assertTrue(property_exists($eagerly, 'robots'));
        $this->assertTrue(is_array($eagerly->robots));
        $this->assertSame(count($eagerly->robots), $rawly->robots->count());

        $getIds = function ($arr) {
            $ret = array ();

            foreach ($arr as $r) {
                if (is_object($r)) {
                    $ret[] = $r->readAttribute('id');
                }
            }

            return $ret;
        };

        $this->assertEquals(
            $getIds($this->resultSetToEagerLoadingEquivalent($rawly->robots)),
            $getIds($eagerly->robots)
        );
    }

    public function testHasManyToMany()
    {
        $rawly = Robot::findFirstById(1);
        $rawly->parts;

        $eagerly = Loader::fromModel(Robot::findFirstById(1), 'Parts');

        $this->assertTrue(property_exists($eagerly, 'parts'));
        $this->assertTrue(is_array($eagerly->parts));
        $this->assertSame(count($eagerly->parts), $rawly->parts->count());

        $getIds = function ($arr) {
            $ret = array ();

            foreach ($arr as $r) {
                if (is_object($r)) {
                    $ret[] = $r->readAttribute('id');
                }
            }

            return $ret;
        };

        $this->assertEquals(
            $getIds($this->resultSetToEagerLoadingEquivalent($rawly->parts)),
            $getIds($eagerly->parts)
        );
    }

    /**
     * @requires PHP 5.4
     */
    public function testModelMethods()
    {
        $this->assertTrue(is_array(Robot::with('Parts')));
        $this->assertTrue(is_object(Robot::findFirstById(1)->load('Parts')));
        $this->assertTrue(is_object(Robot::findFirstWith('Parts', array ('id = 1'))));
    }

    /**
     * @requires PHP 5.4
     * @dataProvider dp1
     */
    public function testShouldThrowBadMethodCallExceptionIfArgumentsWereNotProvided($method)
    {
        $this->setExpectedException('BadMethodCallException');
        call_user_func(array ('Robot', $method));
    }

    public function dp1()
    {
        return array (array ('with'), array ('findFirstWith'));
    }

    /**
     * @requires PHP 5.4
     * @dataProvider dp2
     */
    public function testShouldThrowLogicExceptionIfTheEntityWillBeIncomplete($method, $args)
    {
        $this->setExpectedException('LogicException');
        call_user_func_array(array ('Robot', $method), $args);
    }

    public function dp2()
    {
        return array (
            array ('with', array ('Parts', array ('columns' => 'id'))),
            array ('findFirstWith', array ('Parts', array ('columns' => 'id'))),
            array ('with', array (array ('Parts' => function ($builder) {
                $builder->columns(array ('id'));
            }))),
        );
    }

    /**
     * @dataProvider dp3
     */
    public function testShouldThrowInvalidArgumentExceptionIfLoaderSubjectIsNotValid($args)
    {
        $this->setExpectedException('InvalidArgumentException');
        $reflection = new ReflectionClass('Phalcon\Mvc\Model\EagerLoading\Loader');
        $reflection->newInstance($args);
    }

    public function dp3()
    {
        return array (
            array (null),
            array (array ()),
            array (range(0, 5)),
            array (array (Robot::findFirstById(1), Bug::findFirstById(1))),
            array (Robot::find('id > 1000'))
        );
    }

    /**
     * @dataProvider dp4
     */
    public function testShouldThrowRuntimeExceptionIfTheRelationIsNotDefinedOrSupported($args)
    {
        $this->setExpectedException('RuntimeException');
        $reflection = new ReflectionClass('Phalcon\Mvc\Model\EagerLoading\Loader');
        $reflection->newInstanceArgs($args)->execute();
    }

    public function dp4()
    {
        return array (
            array (array (Robot::findFirst(), 'NotSupportedRelations')),
            array (array (Robot::findFirst(), 'NonexistentRelation')),
        );
    }
    
    /**
     * @requires PHP 5.4
     */
    public function testManyEagerLoadsAndConstraints()
    {
        $manufacturers = Manufacturer::with(
            array (
                'Robots' => function ($builder) {
                    $builder->where('id < 25');
                },
                'Robots.Bugs' => function ($builder) {
                    $builder->limit(2);
                },
                'Robots.Parts'
            ),
            array ('id < 50')
        );

        $this->assertEquals(
            array_sum(array_map(function ($o) {
                return count($o->robots);
            }, $manufacturers)),
            Robot::count(array ('id < 25 AND manufacturer_id < 50'))
        );

        $this->assertEquals(
            array_sum(array_map(function ($o) {
                $c = 0;

                foreach ($o->robots as $r) {
                    $c += count($r->bugs);
                }

                return $c;
            }, $manufacturers)),
            2
        );

        $manufacturers = Manufacturer::with(
            array (
                'Robots.Bugs' => function ($builder) {
                    $builder->where('id > 10000');
                }
            ),
            array (
                'limit' => 5,
                'order' => 'id ASC'
            )
        );

        $this->assertEquals(
            array_sum(array_map(function ($o) {
                return count($o->robots);
            }, $manufacturers)),
            Robot::count(array ('manufacturer_id < 6'))
        );

        $robots = array ();
        
        foreach ($manufacturers as $m) {
            $robots = array_merge($robots, $m->robots);
        }

        $this->assertEquals(
            array_sum(array_map(function ($o) {
                return count($o->bugs);
            }, $robots)),
            0
        );
    }

    protected function resultSetToEagerLoadingEquivalent($val)
    {
        $ret = $val;

        if ($val instanceof SimpleResultset) {
            $ret = array ();

            if ($val->count() > 0) {
                foreach ($val as $model) {
                    $ret[] = $model;
                }
            }
        }

        return $ret;
    }
}
