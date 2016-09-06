<?php

namespace Phalcon\Test\Annotations\Adapter;

use stdClass;
use UnitTester;
use ReflectionMethod;
use Codeception\TestCase\Test;
use Phalcon\Cache\Backend\Libmemcached;
use Phalcon\Annotations\Adapter\Memcached;

/**
 * \Phalcon\Test\Annotations\Adapter\MemcachedTest
 * Tests for Phalcon\Annotations\Adapter\Memcached component
 *
 * @copyright (c) 2011-2016 Phalcon Team
 * @author    Ilya Gusev <mail@igusev.ru>
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
class MemcachedTest extends Test
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
        if (!extension_loaded('memcached')) {
            $this->markTestSkipped('memcached extension not loaded');
        }
    }

    /**
     * @expectedException        \Phalcon\Annotations\Exception
     * @expectedExceptionMessage No host given in options
     */
    public function testShouldCatchExceptionWhenNoHostGivenInOptions()
    {
        new Memcached(['lifetime' => 23, 'prefix' => '']);
    }

    public function testHasDefaultPort()
    {
        $this->assertClassHasStaticAttribute('defaultPort', Memcached::class);
    }

    public function testHasDefaultWeight()
    {
        $this->assertClassHasStaticAttribute('defaultWeight', Memcached::class);
    }

    public function testHasMemcached()
    {
        $this->assertClassHasAttribute('memcached', Memcached::class);
    }

    /**
     * @dataProvider providerReadWrite
     * @param string $key
     * @param mixed $data
     */
    public function testShouldReadAndWriteToMemcachedWithoutPrefix($key, $data)
    {
        $object = new Memcached(['host' => TEST_MC_HOST]);
        $object->write($key, $data);

        $this->assertEquals($data, $object->read($key));
    }

    /**
     * @dataProvider providerReadWrite
     * @param string $key
     * @param mixed $data
     */
    public function testShouldReadAndWriteToMemcachedWithPrefix($key, $data)
    {
        $object = new Memcached(['host' => TEST_MC_HOST, 'prefix' => 'test_']);
        $object->write($key, $data);

        $this->assertEquals($data, $object->read($key));
    }

    public function testShouldGetCacheBackendThroughGetter()
    {
        $object = new Memcached(['host' => TEST_MC_HOST]);

        $reflectedMethod = new ReflectionMethod(get_class($object), 'getCacheBackend');
        $reflectedMethod->setAccessible(true);
        $this->assertInstanceOf(Libmemcached::class, $reflectedMethod->invoke($object));
    }

    public function testShouldGetCacheBackendThroughReflectionSetter()
    {
        $object = new Memcached(['host' => TEST_MC_HOST]);
        $mock = $this->getMockBuilder(Libmemcached::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->tester->setProtectedProperty($object, 'memcached', $mock);

        $reflectedMethod = new ReflectionMethod(get_class($object), 'getCacheBackend');
        $reflectedMethod->setAccessible(true);
        $this->assertInstanceOf(Libmemcached::class, $reflectedMethod->invoke($object));
    }

    /**
     * @dataProvider providerKey
     * @param mixed $key
     */
    public function testShouldPrepareKey($key)
    {
        $object = new Memcached(['host' => TEST_MC_HOST]);
        $reflectedMethod = new ReflectionMethod(get_class($object), 'prepareKey');
        $reflectedMethod->setAccessible(true);

        $this->assertEquals($key, $reflectedMethod->invoke($object, $key));
    }

    /**
     * @dataProvider providerConstructor
     * @param array $options
     * @param array $expected
     */
    public function testShouldCreateMemcachedAdapterInstanceAndSetOptions($options, $expected)
    {
        $object = new Memcached($options);

        $this->assertEquals($expected, $this->tester->getProtectedProperty($object, 'options'));
    }

    public function providerReadWrite()
    {
        // This key is needed in order not to break your real data
        $key = hash('sha256', json_encode([__CLASS__, __METHOD__, __FILE__, __LINE__]));

        return [
            [$key . '_test1', 'data1'],
            [$key . '_test1', (object) ['key' => 'value']],
            [$key . '_test1', ['key' => 'value']],
            [$key . '_test1', null],
            [$key . '_test1', new stdClass()],
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
                    'host' => TEST_MC_HOST,
                    'port' => TEST_MC_PORT,
                    'weight' => 1,
                    'lifetime' => 23
                ],
                [
                    'host' => TEST_MC_HOST,
                    'port' => TEST_MC_PORT,
                    'weight' => 1,
                    'lifetime' => 23,
                    'prefix' => ''
                ]
            ],
            [
                [
                    'host' => TEST_MC_HOST,
                    'port' => TEST_MC_PORT,
                    'weight' => 1,
                    'prefix' => 'test_'
                ],
                [
                    'host' => TEST_MC_HOST,
                    'port' => TEST_MC_PORT,
                    'weight' => 1,
                    'lifetime' => 8600,
                    'prefix' => 'test_'
                ]
            ],
            [
                [
                    'host' => TEST_MC_HOST,
                    'port' => TEST_MC_PORT,
                    'weight' => 1,
                    'randomValue' => 'test_'
                ],
                [
                    'host' => TEST_MC_HOST,
                    'port' => TEST_MC_PORT,
                    'weight' => 1,
                    'randomValue' => 'test_',
                    'lifetime' => 8600,
                    'prefix' => ''
                ]
            ],
            [
                [
                    'host' => TEST_MC_HOST,
                    'port' => TEST_MC_PORT,
                    'weight' => 1,
                    123 => 'test_'
                ],
                [
                    'host' => TEST_MC_HOST,
                    'port' => TEST_MC_PORT,
                    'weight' => 1,
                    123 => 'test_',
                    'lifetime' => 8600,
                    'prefix' => ''
                ]
            ],
            [
                [
                    'host' => TEST_MC_HOST,
                    'port' => TEST_MC_PORT,
                    'weight' => 1,
                    'lifetime' => 24,
                    'prefix' => 'test_'
                ],
                [
                    'host' => TEST_MC_HOST,
                    'port' => TEST_MC_PORT,
                    'weight' => 1,
                    'lifetime' => 24,
                    'prefix' => 'test_'
                ]
            ],
            [
                [
                    'host' => TEST_MC_HOST,
                    'port' => TEST_MC_PORT,
                    'weight' => 1
                ],
                [
                    'host' => TEST_MC_HOST,
                    'port' => TEST_MC_PORT,
                    'weight' => 1,
                    'lifetime' => 8600,
                    'prefix' => ''
                ]
            ],
            [
                [
                    'host' => TEST_MC_HOST,
                    'weight' => 1
                ],
                [
                    'host' => TEST_MC_HOST,
                    'port' => TEST_MC_PORT,
                    'weight' => 1,
                    'lifetime' => 8600,
                    'prefix' => ''
                ]
            ],
            [
                [
                    'host' => TEST_MC_HOST,
                    'port' => TEST_MC_PORT,
                ],
                [
                    'host' => TEST_MC_HOST,
                    'port' => TEST_MC_PORT,
                    'weight' => 1,
                    'lifetime' => 8600,
                    'prefix' => ''
                ]
            ],
            [
                [
                    'host' => TEST_MC_HOST,
                ],
                [
                    'host' => TEST_MC_HOST,
                    'port' => TEST_MC_PORT,
                    'weight' => 1,
                    'lifetime' => 8600,
                    'prefix' => ''
                ]
            ],
        ];
    }
}
