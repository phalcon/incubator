<?php

namespace Phalcon\Test\Mvc\Model\EagerLoading;

use ReflectionClass;
use stdClass;
use Phalcon\Mvc\Model\Resultset\Simple as SimpleResultset;
use Phalcon\Test\Mvc\Model\EagerLoading\Stubs\Bug;
use Phalcon\Test\Mvc\Model\EagerLoading\Stubs\Robot;
use Phalcon\Test\Mvc\Model\EagerLoading\Stubs\Manufacturer;
use Phalcon\Mvc\Model\EagerLoading\Loader;
use Phalcon\Di;
use Phalcon\DiInterface;
use Phalcon\Mvc\Model\Metadata;
use Phalcon\Mvc\Model\Manager;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Test\Codeception\UnitTestCase as Test;
use Phalcon\Version;

/**
 * \Phalcon\Test\Mvc\Model\EagerLoading\EagerLoadingTest
 * Tests for Phalcon\Mvc\Model\EagerLoading\Loader component
 *
 * @copyright (c) 2011-2017 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Óscar Enríquez
 * @package   Phalcon\Test\Mvc\Model\EagerLoading
 * @group     EagerLoading
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class EagerLoadingTest extends Test
{
    /**
     * @var DiInterface
     */
    protected $previousDependencyInjector;

    /**
     * executed before each test
     */
    protected function _before()
    {
        $this->previousDependencyInjector = Di::getDefault();

        $di = new Di();

        $di->setShared('modelsMetadata', new Metadata\Memory());
        $di->setShared('modelsManager', new Manager());
        $di->setShared('db', function () {
            return new Mysql([
                'host'     => env('TEST_DB_HOST', '127.0.0.1'),
                'username' => env('TEST_DB_USER', 'incubator'),
                'password' => env('TEST_DB_PASSWD', 'secret'),
                'dbname'   => env('TEST_DB_NAME', 'incubator'),
                'charset'  => env('TEST_DB_CHARSET', 'utf8'),
                'port'     => env('TEST_DB_PORT', 3306),
            ]);
        });

        if ($this->previousDependencyInjector instanceof DiInterface) {
            Di::setDefault($di);
        }
    }

    /**
     * executed after each test
     */
    protected function _after()
    {
        if ($this->previousDependencyInjector instanceof DiInterface) {
            Di::setDefault($this->previousDependencyInjector);
        } else {
            Di::reset();
        }
    }

    /**
     * https://github.com/stibiumz/phalcon.eager-loading/issues/4
     */
    public function testShouldLoadChildOfEmptyParentWithoutException()
    {
        // Has many -> Belongs to
        // Should be the same for Has many -> Has one
        $loader = new Loader(Robot::findFirstById(1), 'Bugs.Robot');
        $this->assertEquals($loader->execute()->get()->bugs, []);
    }

    public function testManyEagerLoadsAndConstraints()
    {
        $manufacturers = Manufacturer::with(
            [
                'Robots' => function ($builder) {
                    $builder->where('id < 25');
                },
                'Robots.Bugs' => function ($builder) {
                    $builder->limit(2);
                },
                'Robots.Parts'
            ],
            ['id < 50']
        );

        $this->assertEquals(
            array_sum(array_map(function ($o) {
                return count($o->robots);
            }, $manufacturers)),
            Robot::count(['id < 25 AND manufacturer_id < 50'])
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
            [
                'Robots.Bugs' => function ($builder) {
                    $builder->where('id > 10000');
                }
            ],
            [
                'limit' => 5,
                'order' => 'id ASC'
            ]
        );

        $this->assertEquals(
            array_sum(array_map(function ($o) {
                return count($o->robots);
            }, $manufacturers)),
            Robot::count(['manufacturer_id < 6'])
        );

        $robots = [];

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

    /**
     * @dataProvider      providerRelationIsNotDefinedOrSupported
     * @expectedException \RuntimeException
     * @param             array $args
     */
    public function testShouldThrowRuntimeExceptionIfTheRelationIsNotDefinedOrSupported($args)
    {
        $reflection = new ReflectionClass('Phalcon\Mvc\Model\EagerLoading\Loader');
        $reflection->newInstanceArgs([Robot::findFirst(), $args])->execute();
    }

    /**
     * @dataProvider             providerSubjectIsNotValid
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Expected value of `subject` is either a ModelInterface object, a Simple object or an array of ModelInterface objects
     * @param                    array $args
     */
    public function testShouldThrowInvalidArgumentExceptionIfLoaderSubjectIsNotValid($args)
    {
        $reflection = new ReflectionClass('Phalcon\Mvc\Model\EagerLoading\Loader');
        $reflection->newInstance($args);
    }

    /**
     * @dataProvider      providerIncompleteEntity
     * @expectedException \LogicException
     * @param             string $method
     * @param             array  $args
     */
    public function testShouldThrowLogicExceptionIfTheEntityWillBeIncomplete($method, $args)
    {
        call_user_func_array(['Phalcon\Test\Mvc\Model\EagerLoading\Stubs\Robot', $method], $args);
    }

    /**
     * @dataProvider providerWithoutArguments
     * @expectedException \BadMethodCallException
     * @param string $method
     */
    public function testShouldThrowBadMethodCallExceptionIfArgumentsWereNotProvided($method)
    {
        call_user_func(['Phalcon\Test\Mvc\Model\EagerLoading\Stubs\Robot', $method]);
    }

    public function testShouldUseEagerLoadingAndGetModelByUsingMethods()
    {
        $this->assertTrue(is_array(Robot::with('Parts')));
        $this->assertTrue(is_object(Robot::findFirstById(1)->load('Parts')));
        $this->assertTrue(is_object(Robot::findFirstWith('Parts', ['id = 1'])));
    }

    public function testShouldUseEagerLoadingAndDetectHasManyToMany()
    {
        $rawly = Robot::findFirstById(1);
        $rawly->parts;

        $eagerly = Loader::fromModel(Robot::findFirstById(1), 'Parts');

        $this->assertTrue(property_exists($eagerly, 'parts'));
        $this->assertTrue(is_array($eagerly->parts));
        $this->assertSame(count($eagerly->parts), $rawly->parts->count());

        $getIds = function ($arr) {
            $ret = [];

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

    public function testShouldUseEagerLoadingAndDetectHasMany()
    {
        $rawly = Manufacturer::findFirstById(1);
        $rawly->robots;

        $eagerly = Loader::fromModel(Manufacturer::findFirstById(1), 'Robots');

        $this->assertTrue(property_exists($eagerly, 'robots'));
        $this->assertTrue(is_array($eagerly->robots));
        $this->assertSame(count($eagerly->robots), $rawly->robots->count());

        $getIds = function ($arr) {
            $ret = [];

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

    public function testShouldUseEagerLoadingAndDetectHasOne()
    {
        $rawly = Robot::findFirstById(1);
        $rawly->purpose;

        $eagerly = Loader::fromModel(Robot::findFirstById(1), 'Purpose');

        $this->assertTrue(property_exists($eagerly, 'purpose'));
        $this->assertInstanceOf('Phalcon\Test\Mvc\Model\EagerLoading\Stubs\Purpose', $eagerly->purpose);
        $this->assertEquals($rawly->purpose->readAttribute('id'), $eagerly->purpose->readAttribute('id'));
    }

    public function testShouldUseEagerLoadingAndDetectBelongsToDeep()
    {
        $rawly = Manufacturer::findFirstById(1);
        $rawly->robots = $this->resultSetToEagerLoadingEquivalent($rawly->robots);

        foreach ($rawly->robots as $robot) {
            $robot->parent;
        }

        $eagerly = Loader::fromModel(Manufacturer::findFirstById(1), 'Robots.Parent');

        $this->assertTrue(property_exists($eagerly->robots[0], 'parent'));
        $this->assertNull($eagerly->robots[0]->parent);
        $this->assertInstanceOf('Phalcon\Test\Mvc\Model\EagerLoading\Stubs\Robot', $eagerly->robots[2]->parent);

        $getIds = function ($obj) {
            return property_exists($obj, 'parent') && isset($obj->parent) ? $obj->parent->readAttribute('id') : null;
        };

        $this->assertEquals(array_map($getIds, $eagerly->robots), array_map($getIds, $rawly->robots));
    }

    public function testBelongsTo()
    {
        $rawly = Bug::findFirstById(1);
        $rawly->robot;

        $eagerly = Loader::fromModel(Bug::findFirstById(1), 'Robot');

        $this->assertTrue(property_exists($eagerly, 'robot'));
        $this->assertInstanceOf('Phalcon\Test\Mvc\Model\EagerLoading\Stubs\Robot', $eagerly->robot);
        $this->assertEquals($rawly->robot->readAttribute('id'), $eagerly->robot->readAttribute('id'));

        // Reverse
        $rawly = Robot::findFirstById(2);
        $rawly->bugs = $this->resultSetToEagerLoadingEquivalent($rawly->bugs);

        $eagerly = Loader::fromModel(Robot::findFirstById(2), 'Bugs');

        $this->assertTrue(property_exists($eagerly, 'bugs'));
        $this->assertContainsOnlyInstancesOf('Phalcon\Test\Mvc\Model\EagerLoading\Stubs\Bug', $eagerly->bugs);

        $getIds = function ($obj) {
            return $obj->readAttribute('id');
        };

        $this->assertEquals(array_map($getIds, $rawly->bugs), array_map($getIds, $eagerly->bugs));
        $this->assertEmpty(Loader::fromModel(Robot::findFirstById(1), 'Bugs')->bugs);

        // Test from multiple
        $rawly = $this->resultSetToEagerLoadingEquivalent(Bug::find(['limit' => 10]));
        foreach ($rawly as $bug) {
            $bug->robot;
        }

        $eagerly = Loader::fromResultset(Bug::find(array ('limit' => 10)), 'Robot');

        $this->assertTrue(is_array($eagerly));
        $this->assertTrue(array_reduce($eagerly, function ($res, $bug) {
            return $res && property_exists($bug, 'robot');
        }, true));

        $getIds = function ($obj) {
            return property_exists($obj, 'robot') && isset($obj->robot) ? $obj->robot->readAttribute('id') : null;
        };

        $this->assertEquals(array_map($getIds, $rawly), array_map($getIds, $eagerly));
    }

    public function providerWithoutArguments()
    {
        return [
            ['with'],
            ['findFirstWith']
        ];
    }

    public function providerIncompleteEntity()
    {
        return [
            ['with', ['Parts', ['columns' => 'id']]],
            ['findFirstWith', ['Parts', ['columns' => 'id']]],
            ['with', [
                    [
                        'Parts' => function ($builder) {
                            $builder->columns(['id']);
                        }
                    ]
                ]
            ],
        ];
    }

    public function providerRelationIsNotDefinedOrSupported()
    {
        return [
            ['NotSupportedRelations'],
            ['NonexistentRelation'],
        ];
    }

    public function providerSubjectIsNotValid()
    {
        return [
            [range(0, 5)],
            [[new stdClass(), null]]
        ];
    }

    protected function resultSetToEagerLoadingEquivalent($val)
    {
        $ret = $val;

        if ($val instanceof SimpleResultset) {
            $ret = [];

            if ($val->count() > 0) {
                foreach ($val as $model) {
                    $ret[] = $model;
                }
            }
        }

        return $ret;
    }
}
