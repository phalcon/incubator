<?php

namespace Phalcon\Test\Mvc\Model\MetaData;

use Codeception\TestCase\Test;
use UnitTester;
use Mockery;

/**
 * \Phalcon\Test\Mvc\Model\MetaData\BaseTest
 * Tests for Phalcon\Mvc\Model\MetaData\Base component
 *
 * @copyright (c) 2011-2015 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Nemanja Ognjanovic <nemanja@ognjanovic.me>
 * @package   Phalcon\Test\Mvc\Model\MetaData
 * @group     MetaData
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class BaseTest extends Test
{
    /**
     * UnitTester Object
     * @var UnitTester
     */
    protected $tester;

    /**
     * executed before each test
     */
    protected function _before()
    {
    }

    /**
     * executed after each test
     */
    protected function _after()
    {
    }

    public function testBaseMetaDataAdapterImplementsMetaDataInterface()
    {
        $mock = Mockery::mock('Phalcon\Mvc\Model\MetaData\Base');

        $this->assertInstanceOf('Phalcon\Mvc\Model\MetaDataInterface', $mock);
    }

    public function testMemcacheMetaDataAdapterImplementsMetaDataInterface()
    {
        $mock = Mockery::mock('Phalcon\Mvc\Model\MetaData\Memcache');

        $this->assertInstanceOf('Phalcon\Mvc\Model\MetaDataInterface', $mock);
    }

    public function testMemcachedMetaDataAdapterImplementsMetaDataInterface()
    {
        $mock = Mockery::mock('Phalcon\Mvc\Model\MetaData\Memcached');

        $this->assertInstanceOf('Phalcon\Mvc\Model\MetaDataInterface', $mock);
    }

    public function testRedisMetaDataAdapterImplementsMetaDataInterface()
    {
        $mock = Mockery::mock('Phalcon\Mvc\Model\MetaData\Redis');

        $this->assertInstanceOf('Phalcon\Mvc\Model\MetaDataInterface', $mock);
    }

    public function testWincacheMetaDataAdapterImplementsMetaDataInterface()
    {
        $mock = Mockery::mock('Phalcon\Mvc\Model\MetaData\Wincache');

        $this->assertInstanceOf('Phalcon\Mvc\Model\MetaDataInterface', $mock);
    }
}
