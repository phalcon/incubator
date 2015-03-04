<?php

namespace Phalcon\Annotations\Adapter;

/**
 * \Phalcon\Annotations\Adapter\MemcachedTest
 * Tests for class \Phalcon\Annotations\Adapter\Memcached
 *
 * @category Phalcon
 * @package  Phalcon\Annotations\Adapter
 * @author   Ilya Gusev <mail@igusev.ru>
 * @license  New BSD License
 * @link     http://phalconphp.com/
 */
class MemcachedTest extends \PHPUnit_Framework_TestCase
{

    public function dataConstructor()
    {
        return array(
            array(
                array(
                    'host' => '127.0.0.1',
                    'port' => 11211,
                    'weight' => 1,
                    'lifetime' => 23
                ),
                array(
                    'host' => '127.0.0.1',
                    'port' => 11211,
                    'weight' => 1,
                    'lifetime' => 23,
                    'prefix' => ''
                )
            ),
            array(
                array(
                    'host' => '127.0.0.1',
                    'port' => 11211,
                    'weight' => 1,
                    'prefix' => 'test_'
                ),
                array(
                    'host' => '127.0.0.1',
                    'port' => 11211,
                    'weight' => 1,
                    'lifetime' => 8600,
                    'prefix' => 'test_'
                )
            ),
            array(
                array(
                    'host' => '127.0.0.1',
                    'port' => 11211,
                    'weight' => 1,
                    'randomValue' => 'test_'
                ),
                array(
                    'host' => '127.0.0.1',
                    'port' => 11211,
                    'weight' => 1,
                    'randomValue' => 'test_',
                    'lifetime' => 8600,
                    'prefix' => ''
                )
            ),
            array(
                array(
                    'host' => '127.0.0.1',
                    'port' => 11211,
                    'weight' => 1,
                    123 => 'test_'
                ),
                array(
                    'host' => '127.0.0.1',
                    'port' => 11211,
                    'weight' => 1,
                    123 => 'test_',
                    'lifetime' => 8600,
                    'prefix' => ''
                )
            ),
            array(
                array(
                    'host' => '127.0.0.1',
                    'port' => 11211,
                    'weight' => 1,
                    'lifetime' => 24,
                    'prefix' => 'test_'
                ),
                array(
                    'host' => '127.0.0.1',
                    'port' => 11211,
                    'weight' => 1,
                    'lifetime' => 24,
                    'prefix' => 'test_'
                )
            ),
            array(
                array(
                    'host' => '127.0.0.1',
                    'port' => 11211,
                    'weight' => 1
                ),
                array(
                    'host' => '127.0.0.1',
                    'port' => 11211,
                    'weight' => 1,
                    'lifetime' => 8600,
                    'prefix' => ''
                )
            ),
            array(
                array(
                    'host' => '127.0.0.1',
                    'weight' => 1
                ),
                array(
                    'host' => '127.0.0.1',
                    'port' => 11211,
                    'weight' => 1,
                    'lifetime' => 8600,
                    'prefix' => ''
                )
            ),
            array(
                array(
                    'host' => '127.0.0.1',
                    'port' => 11211,
                ),
                array(
                    'host' => '127.0.0.1',
                    'port' => 11211,
                    'weight' => 1,
                    'lifetime' => 8600,
                    'prefix' => ''
                )
            ),
            array(
                array(
                    'host' => '127.0.0.1',
                ),
                array(
                    'host' => '127.0.0.1',
                    'port' => 11211,
                    'weight' => 1,
                    'lifetime' => 8600,
                    'prefix' => ''
                )
            ),
        );
    }

    public function dataKey()
    {
        return array(
            array(
                'key1'
            ),
            array(
                1
            ),
            array(
                '_key1'
            )
        );
    }

    /**
     * @expectedException           \Phalcon\Mvc\Model\Exception
     * @expectedExceptionMessage    No host given in options
     */
    public function testConstructorException()
    {
        $object = new Memcached(array('lifetime' => 23, 'prefix' => ''));
    }

    /**
     * @expectedException           \Phalcon\Mvc\Model\Exception
     * @expectedExceptionMessage    No configuration given
     */
    public function testConstructorException2()
    {
        $object = new \Phalcon\Annotations\Adapter\Memcached(1);
    }

    /**
     * @dataProvider dataConstructor
     */
    public function testConstructor($options, $expected)
    {
        $object = new \Phalcon\Annotations\Adapter\Memcached($options);
        $reflectedProperty = new \ReflectionProperty(get_class($object), 'options');
        $reflectedProperty->setAccessible(true);
        $this->assertEquals($expected, $reflectedProperty->getValue($object));
    }

    /**
     * @dataProvider dataKey
     */
    public function testPrepareKey($key)
    {
        $object = new \Phalcon\Annotations\Adapter\Memcached(array('host' => '127.0.0.1'));
        $reflectedMethod = new \ReflectionMethod(get_class($object), 'prepareKey');
        $reflectedMethod->setAccessible(true);
        $this->assertEquals($key, $reflectedMethod->invoke($object, $key));
    }

    public function testGetCacheBackend()
    {
        $object = new \Phalcon\Annotations\Adapter\Memcached(array('host' => '127.0.0.1'));
        $mock = $this->getMock('\Phalcon\Cache\Backend\Libmemcached', array(), array(), '', false);

        $reflectedProperty = new \ReflectionProperty(get_class($object), 'memcached');
        $reflectedProperty->setAccessible(true);
        $reflectedProperty->setValue($object, $mock);

        $reflectedMethod = new \ReflectionMethod(get_class($object), 'getCacheBackend');
        $reflectedMethod->setAccessible(true);
        $this->assertInstanceOf('\Phalcon\Cache\Backend\Libmemcached', $reflectedMethod->invoke($object));
    }


    /**
     *
     * @requires extension memcached
     */
    public function testGetCacheBackend2()
    {
        $object = new \Phalcon\Annotations\Adapter\Memcached(array('host' => '127.0.0.1'));

        $reflectedMethod = new \ReflectionMethod(get_class($object), 'getCacheBackend');
        $reflectedMethod->setAccessible(true);
        $this->assertInstanceOf('\Phalcon\Cache\Backend\Libmemcached', $reflectedMethod->invoke($object));
    }

    public function testHasDefaultPort()
    {
        $this->assertClassHasStaticAttribute('defaultPort', '\Phalcon\Annotations\Adapter\Memcached');
    }

    public function testHasDefaultWeight()
    {
        $this->assertClassHasStaticAttribute('defaultWeight', '\Phalcon\Annotations\Adapter\Memcached');
    }

    public function testHasMemcached()
    {
        $this->assertClassHasAttribute('memcached', '\Phalcon\Annotations\Adapter\Memcached');
    }
}
