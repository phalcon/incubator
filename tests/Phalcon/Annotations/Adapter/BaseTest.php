<?php

namespace Phalcon\Annotations\Adapter;

use Phalcon\Cache\Backend\Memory as CacheBackend;
use Phalcon\Cache\Frontend\Data as CacheFrontend;

/**
 * \Phalcon\Annotations\Adapter\BaseTest
 * Tests for class \Phalcon\Annotations\Adapter\Base
 *
 * @copyright (c) 2011-2015 Phalcon Team
 * @package   Phalcon\Annotations\Adapter
 * @author    Ilya Gusev <mail@igusev.ru>
 * @link      http://phalconphp.com/
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class BaseTest extends \PHPUnit_Framework_TestCase
{

    protected $classname = '\Phalcon\Annotations\Adapter\Base';

    public function dataConstructor()
    {
        return array(
            array(array('lifetime' => 23), array('lifetime' => 23, 'prefix' => '')),
            array(array('prefix' => 'test_'), array('lifetime' => 8600, 'prefix' => 'test_')),
            array(array('randomValue' => 'test_'), array('randomValue' => 'test_', 'lifetime' => 8600, 'prefix' => '')),
            array(array(123 => 'test_'), array(123 => 'test_', 'lifetime' => 8600, 'prefix' => '')),
            array(array('lifetime' => 24, 'prefix' => 'test_'), array('lifetime' => 24, 'prefix' => 'test_')),
            array(array(), array('lifetime' => 8600, 'prefix' => '')),
            array(null, array('lifetime' => 8600, 'prefix' => ''))
        );
    }

    public function dataReadWrite()
    {
        return array(
            array('test1', 'data1'),
            array('test1', (object) array('key' => 'value')),
            array('test1', array('key' => 'value')),
            array('test1', null)
        );
    }

    protected function getObject($options)
    {
        return $this->getMockForAbstractClass($this->classname, array('options' => $options), '', true,
            true, true, array(), true);
    }

    /**
     * @dataProvider dataConstructor
     */
    public function testConstructor($options, $expected)
    {
        $mock = $this->getObject($options);
        $reflectedProperty = new \ReflectionProperty(get_class($mock), 'options');
        $reflectedProperty->setAccessible(true);
        $this->assertEquals($expected, $reflectedProperty->getValue($mock));
    }

    /**
     * @dataProvider dataReadWrite
     */
    public function testRead($key, $data)
    {
        $mock = $this->getObject(null);
        $mock->expects($this->once())->method('prepareKey')->willReturn($key);

        $cacheBackend = new CacheBackend(new CacheFrontend(array('lifetime' => 86400)));
        $cacheBackend->save($key, $data, 86400);

        $mock->expects($this->once())->method('getCacheBackend')->willReturn($cacheBackend);

        $this->assertEquals($data, $mock->read($key));
    }

    /**
     * @dataProvider dataReadWrite
     */
    public function testWrite($key, $data)
    {
        $mock = $this->getObject(null);
        $mock->expects($this->once())->method('prepareKey')->willReturn($key);

        $cacheBackend = new CacheBackend(new CacheFrontend(array('lifetime' => 86400)));

        $mock->expects($this->once())->method('getCacheBackend')->willReturn($cacheBackend);
        $mock->write($key, $data, 86400);

        $this->assertEquals($data, $cacheBackend->get($key));
    }

    public function testHasDefaultLifetime()
    {
        $this->assertClassHasStaticAttribute('defaultLifetime', $this->classname);
    }

    public function testHasDefaultPrefix()
    {
        $this->assertClassHasStaticAttribute('defaultPrefix', $this->classname);
    }

    public function testHasOptions()
    {
        $this->assertClassHasAttribute('options', $this->classname);
    }

}
