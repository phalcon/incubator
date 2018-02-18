<?php
/**
 * Created by PhpStorm.
 * User: fenikkusu
 * Date: 2/11/18
 * Time: 12:10 AM
 */

namespace Phalcon\Test\Test\Traits;

use Phalcon\Config;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Di\FactoryDefault;
use Phalcon\Test\Codeception\ModelTestCase as ModelTest;
use Phalcon\Test\Traits\ModelTestCase;

class ModelTestCaseTest extends ModelTest
{
    /** @var ModelTestCase */
    protected $testSubject = null;

    public function _before()
    {
        $this->testSubject = $this->di->get(ModelTest::class);
    }

    public function testDbWithDb()
    {
        $mockDb = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->di = $this->getMockBuilder(FactoryDefault::class)
            ->disableOriginalConstructor()
            ->setMethods(['has', 'get'])
            ->getMock();

        $this->di->expects($this->once())
            ->method('has')
            ->with('db')
            ->willReturn(true);

        $this->di->expects($this->once())
            ->method('get')
            ->willReturn($mockDb);

        $this->testSubject->setDI($this->di);

        $reflectionMethod = new \ReflectionMethod(ModelTest::class, 'setDb');
        $reflectionMethod->setAccessible(true);

        $this->assertSame($mockDb, $reflectionMethod->invoke($this->testSubject));
    }

    public function testDbWithoutConfig()
    {
        $this->testSubject = $this->getMockBuilder(ModelTest::class)
            ->setMethods(['getConfig'])
            ->getMock();

        $this->testSubject->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->testSubject->expects($this->never())
            ->method('getConfig');

        $this->di = $this->getMockBuilder(FactoryDefault::class)
                         ->disableOriginalConstructor()
                         ->setMethods(['has', 'set'])
                         ->getMock();

        $this->di->expects($this->once())
                 ->method('has')
                 ->with('db')
                 ->willReturn(false);

        $this->di->expects($this->once())
            ->method('set')
            ->with('db', $this->isInstanceOf(\Closure::class));

        $this->testSubject->setDI($this->di);

        $reflectionMethod = new \ReflectionMethod(ModelTest::class, 'setDb');
        $reflectionMethod->setAccessible(true);

        $reflectionMethod->invoke($this->testSubject);
    }
}