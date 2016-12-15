<?php

namespace Phalcon\Test\Annotations\Adapter;

use UnitTester;
use ReflectionMethod;
use ReflectionProperty;
use Codeception\TestCase\Test;
use Phalcon\Annotations\Adapter\Redis;
use Phalcon\Cache\Backend\Redis as CacheBackend;

/**
 * \Phalcon\Test\Annotations\Adapter\RedisTest
 * Tests for Phalcon\Annotations\Adapter\Redis component
 *
 * @copyright (c) 2011-2016 Phalcon Team
 * @author    Serghei Iakovlev <serghei@phalconphp.com>
 * @link      http://phalconphp.com/
 * @package   Phalcon\Test\Annotations\Adapter
 * @group     Annotation
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class RedisTest extends Test
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
        if (!extension_loaded('redis')) {
            $this->markTestSkipped('redis extension not loaded');
        }
    }

    /**
     * executed after each test
     */
    protected function _after()
    {
    }

    public function testHasRedis()
    {
        $this->assertClassHasAttribute('redis', Redis::class);
    }

    /**
     * @dataProvider providerReadWrite
     * @param string $key
     * @param mixed $data
     */
    public function testShouldReadAndWriteToRedisWithoutPrefix($key, $data)
    {
        $object = new Redis(['host' => env('TEST_RS_HOST', 11211)]);
        $object->write($key, $data);

        $this->assertEquals($data, $object->read($key));
    }

    /**
     * @dataProvider providerReadWrite
     * @param string $key
     * @param mixed $data
     */
    public function testShouldReadAndWriteToRedisWithPrefix($key, $data)
    {
        $object = new Redis(['host' => env('TEST_RS_HOST', 11211), 'prefix' => 'test_']);
        $object->write($key, $data);

        $this->assertEquals($data, $object->read($key));
    }

    public function testShouldGetCacheBackendThroughGetter()
    {
        $object = new Redis(['host' => env('TEST_RS_HOST', 11211)]);

        $reflectedMethod = new ReflectionMethod(get_class($object), 'getCacheBackend');
        $reflectedMethod->setAccessible(true);
        $this->assertInstanceOf(CacheBackend::class, $reflectedMethod->invoke($object));
    }

    public function testShouldGetCacheBackendThroughReflectionSetter()
    {
        $object = new Redis(['host' => env('TEST_RS_HOST', 11211)]);
        $mock = $this->getMock(CacheBackend::class, [], [], '', false);

        $reflectedProperty = new ReflectionProperty(get_class($object), 'redis');
        $reflectedProperty->setAccessible(true);
        $reflectedProperty->setValue($object, $mock);

        $reflectedMethod = new ReflectionMethod(get_class($object), 'getCacheBackend');
        $reflectedMethod->setAccessible(true);
        $this->assertInstanceOf(CacheBackend::class, $reflectedMethod->invoke($object));
    }

    /**
     * @dataProvider providerKey
     * @param mixed $key
     */
    public function testShouldPrepareKey($key)
    {
        $object = new Redis(['host' => env('TEST_RS_HOST', 11211)]);
        $reflectedMethod = new ReflectionMethod(get_class($object), 'prepareKey');
        $reflectedMethod->setAccessible(true);

        $this->assertEquals($key, $reflectedMethod->invoke($object, $key));
    }

    /**
     * @dataProvider providerConstructor
     * @param array $options
     * @param array $expected
     */
    public function testShouldCreateRedisAdapterInstanceAndSetOptions($options, $expected)
    {
        $object = new Redis($options);
        $reflectedProperty = new ReflectionProperty(get_class($object), 'options');
        $reflectedProperty->setAccessible(true);

        $this->assertEquals($expected, $reflectedProperty->getValue($object));
    }

    public function providerReadWrite()
    {
        // This key is needed in order not to break your real data
        $key = hash('sha256', json_encode([__CLASS__, __METHOD__, __FILE__, __LINE__]));

        return [
            'string' => [$key . '_test1', 'data1'],
            'object' => [$key . '_test1', (object) ['key' => 'value']],
            'array'  => [$key . '_test1', ['key' => 'value']],
            'null'   => [$key . '_test1', null],
            'int'    => [$key . '_test1', PHP_INT_MAX],
            'float'  => [$key . '_test1', 3.14],
            'class'  => [$key . '_test1', new \stdClass()],
        ];
    }

    public function providerKey()
    {
        return [
            ['key1'],
            [1],
            ['_key1']
        ];
    }

    public function providerConstructor()
    {
        return [
            [
                [
                    'host' => env('TEST_RS_HOST', 11211),
                    'port' => env('TEST_RS_PORT', 6379),
                    'lifetime' => 23
                ],
                [
                    'host' => env('TEST_RS_HOST', 11211),
                    'port' => env('TEST_RS_PORT', 6379),
                    'lifetime' => 23,
                    'prefix' => '',
                    'persistent' => false
                ]
            ],
            [
                [
                    'host' => env('TEST_RS_HOST', 11211),
                    'port' => env('TEST_RS_PORT', 6379),
                    'lifetime' => 23,
                    'persistent' => true
                ],
                [
                    'host' => env('TEST_RS_HOST', 11211),
                    'port' => env('TEST_RS_PORT', 6379),
                    'lifetime' => 23,
                    'prefix' => '',
                    'persistent' => true
                ]
            ],
            [
                [
                    'host' => env('TEST_RS_HOST', 11211),
                    'port' => env('TEST_RS_PORT', 6379),
                    'prefix' => 'test_'
                ],
                [
                    'host' => env('TEST_RS_HOST', 11211),
                    'port' => env('TEST_RS_PORT', 6379),
                    'lifetime' => 8600,
                    'prefix' => 'test_',
                    'persistent' => false
                ]
            ],
            [
                [
                    'host' => env('TEST_RS_HOST', 11211),
                    'port' => env('TEST_RS_PORT', 6379),
                    'randomValue' => 'test_'
                ],
                [
                    'host' => env('TEST_RS_HOST', 11211),
                    'port' => env('TEST_RS_PORT', 6379),
                    'randomValue' => 'test_',
                    'lifetime' => 8600,
                    'prefix' => '',
                    'persistent' => false
                ]
            ],
            [
                [
                    'host' => env('TEST_RS_HOST', 11211),
                    'port' => env('TEST_RS_PORT', 6379),
                    123 => 'test_'
                ],
                [
                    'host' => env('TEST_RS_HOST', 11211),
                    'port' => env('TEST_RS_PORT', 6379),
                    123 => 'test_',
                    'lifetime' => 8600,
                    'prefix' => '',
                    'persistent' => false
                ]
            ],
            [
                [
                    'host' => env('TEST_RS_HOST', 11211),
                    'port' => env('TEST_RS_PORT', 6379),
                    'lifetime' => 24,
                    'prefix' => 'test_',
                ],
                [
                    'host' => env('TEST_RS_HOST', 11211),
                    'port' => env('TEST_RS_PORT', 6379),
                    'lifetime' => 24,
                    'prefix' => 'test_',
                    'persistent' => false
                ]
            ],
            [
                [
                    'host' => env('TEST_RS_HOST', 11211),
                    'port' => env('TEST_RS_PORT', 6379),
                ],
                [
                    'host' => env('TEST_RS_HOST', 11211),
                    'port' => env('TEST_RS_PORT', 6379),
                    'lifetime' => 8600,
                    'prefix' => '',
                    'persistent' => false
                ]
            ],
            [
                [
                    'host' => env('TEST_RS_HOST', 11211),
                ],
                [
                    'host' => env('TEST_RS_HOST', 11211),
                    'port' => env('TEST_RS_PORT', 6379),
                    'lifetime' => 8600,
                    'prefix' => '',
                    'persistent' => false
                ]
            ],
            [
                [
                ],
                [
                    'host' => '127.0.0.1',
                    'port' => env('TEST_RS_PORT', 6379),
                    'lifetime' => 8600,
                    'prefix' => '',
                    'persistent' => false
                ]
            ],
        ];
    }
}
