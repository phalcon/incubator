<?php

namespace Phalcon\Test\Annotations\Adapter;

use UnitTester;
use ReflectionMethod;
use ReflectionProperty;
use Codeception\TestCase\Test;
use Phalcon\Annotations\Adapter\Aerospike;

/**
 * \Phalcon\Test\Annotations\Adapter\AerospikeTest
 * Tests for Phalcon\Annotations\Adapter\Aerospike component
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
class AerospikeTest extends Test
{
    const BASE_CLASS = '\Phalcon\Annotations\Adapter\Aerospike';
    const BACKEND_CLASS ='\Phalcon\Cache\Backend\Aerospike';

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
        if (PHP_MAJOR_VERSION == 7) {
            $this->markTestSkipped('The Aerospike module is not available for PHP 7 yet.');
        }

        if (!extension_loaded('aerospike')) {
            $this->markTestSkipped('The Aerospike module is not available.');
        }
    }

    /**
     * executed after each test
     */
    protected function _after()
    {
    }

    public function testHasAerospikeProperty()
    {
        $this->assertClassHasAttribute('aerospike', self::BASE_CLASS);
    }

    public function testHasNamespaceProperty()
    {
        $this->assertClassHasAttribute('namespace', self::BASE_CLASS);
    }

    public function testHasSetProperty()
    {
        $this->assertClassHasAttribute('set', self::BASE_CLASS);
    }

    /**
     * @dataProvider providerReadWrite
     * @param string $key
     * @param mixed $data
     */
    public function testShouldReadAndWriteToAerospikeWithoutPrefix($key, $data)
    {
        $object = new Aerospike(['hosts' => [['addr' => TEST_AS_HOST, 'port' => TEST_AS_PORT]]]);
        $object->write($key, $data);

        $this->assertEquals($data, $object->read($key));
    }

    /**
     * @dataProvider providerReadWrite
     * @param string $key
     * @param mixed $data
     */
    public function testShouldReadAndWriteToAerospikeWithPrefix($key, $data)
    {
        $object = new Aerospike(['hosts' => [['addr' => TEST_AS_HOST, 'port' => TEST_AS_PORT]], 'prefix' => 'test_']);
        $object->write($key, $data);

        $this->assertEquals($data, $object->read($key));
    }

    public function testShouldGetCacheBackendThroughGetter()
    {
        $object = new Aerospike(['hosts' => [['addr' => TEST_AS_HOST, 'port' => TEST_AS_PORT]]]);

        $reflectedMethod = new ReflectionMethod(get_class($object), 'getCacheBackend');
        $reflectedMethod->setAccessible(true);
        $this->assertInstanceOf(self::BACKEND_CLASS, $reflectedMethod->invoke($object));
    }

    public function testShouldGetCacheBackendThroughReflectionSetter()
    {
        $object = new Aerospike(['hosts' => [['addr' => TEST_AS_HOST, 'port' => TEST_AS_PORT]]]);
        $mock = $this->getMock(self::BACKEND_CLASS, [], [], '', false);

        $reflectedProperty = new ReflectionProperty(get_class($object), 'aerospike');
        $reflectedProperty->setAccessible(true);
        $reflectedProperty->setValue($object, $mock);

        $reflectedMethod = new ReflectionMethod(get_class($object), 'getCacheBackend');
        $reflectedMethod->setAccessible(true);
        $this->assertInstanceOf(self::BACKEND_CLASS, $reflectedMethod->invoke($object));
    }

    /**
     * @dataProvider providerKey
     * @param mixed $key
     */
    public function testShouldPrepareKey($key)
    {
        $object = new Aerospike(['hosts' => [['addr' => TEST_AS_HOST, 'port' => TEST_AS_PORT]]]);
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
        $object = new Aerospike($options);
        $reflectedProperty = new ReflectionProperty(get_class($object), 'options');
        $reflectedProperty->setAccessible(true);

        $this->assertEquals($expected, $reflectedProperty->getValue($object));
    }

    /**
     * @dataProvider providerInvalidConstructor
     * @param array $options
     * @param string  $exceptionName
     * @param string $exceptionMessage
     */
    public function testShouldCatchExceptionWhenInvalidParamsPassed($options, $exceptionName, $exceptionMessage)
    {
        $this->setExpectedException($exceptionName, $exceptionMessage);

        new Aerospike($options);
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
            'int'    => [$key . '_test1', 9223372036854775807],
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

    public function providerInvalidConstructor()
    {
        return [
            [
                [],
                'Phalcon\Annotations\Exception',
                'No hosts given in options'
            ],
            [
                ['hosts' => null],
                'Phalcon\Annotations\Exception',
                'No hosts given in options'
            ],
            [
                ['hosts' => []],
                'Phalcon\Annotations\Exception',
                'No hosts given in options'
            ],
            [
                ['hosts' => [[]]],
                'Phalcon\Cache\Exception',
                'Aerospike failed to connect [-2]: Unable to find host parameter'
            ]
        ];
    }

    public function providerConstructor()
    {
        return [
            [
                [
                    'hosts' => [['addr' => TEST_AS_HOST, 'port' => TEST_AS_PORT]],
                    'lifetime' => 23
                ],
                [
                    'hosts' => [['addr' => TEST_AS_HOST, 'port' => TEST_AS_PORT]],
                    'lifetime' => 23,
                    'prefix' => '',
                    'persistent' => false,
                    'options' => []
                ]
            ],
            [
                [
                    'hosts' => [['addr' => TEST_AS_HOST, 'port' => TEST_AS_PORT]],
                    'lifetime' => 23,
                    'options' => [
                        1 => 1250,
                        3  => 1500
                    ]
                ],
                [
                    'hosts' => [['addr' => TEST_AS_HOST, 'port' => TEST_AS_PORT]],
                    'lifetime' => 23,
                    'prefix' => '',
                    'persistent' => false,
                    'options' => [
                        1 => 1250,
                        3 => 1500
                    ]
                ]
            ],
            [
                [
                    'hosts' => [['addr' => TEST_AS_HOST, 'port' => TEST_AS_PORT]],
                    'lifetime' => 23,
                    'persistent' => true
                ],
                [
                    'hosts' => [['addr' => TEST_AS_HOST, 'port' => TEST_AS_PORT]],
                    'lifetime' => 23,
                    'prefix' => '',
                    'persistent' => true,
                    'options' => [],
                ]
            ],
            [
                [
                    'hosts' => [['addr' => TEST_AS_HOST, 'port' => TEST_AS_PORT]],
                    'prefix' => 'test_'
                ],
                [
                    'hosts' => [['addr' => TEST_AS_HOST, 'port' => TEST_AS_PORT]],
                    'lifetime' => 8600,
                    'prefix' => 'test_',
                    'persistent' => false,
                    'options' => [],
                ]
            ],
            [
                [
                    'hosts' => [['addr' => TEST_AS_HOST, 'port' => TEST_AS_PORT]],
                    'randomValue' => 'test_'
                ],
                [
                    'hosts' => [['addr' => TEST_AS_HOST, 'port' => TEST_AS_PORT]],
                    'randomValue' => 'test_',
                    'lifetime' => 8600,
                    'prefix' => '',
                    'persistent' => false,
                    'options' => [],
                ]
            ],
            [
                [
                    'hosts' => [['addr' => TEST_AS_HOST, 'port' => TEST_AS_PORT]],
                    123 => 'test_'
                ],
                [
                    'hosts' => [['addr' => TEST_AS_HOST, 'port' => TEST_AS_PORT]],
                    123 => 'test_',
                    'lifetime' => 8600,
                    'prefix' => '',
                    'persistent' => false,
                    'options' => [],
                ]
            ],
            [
                [
                    'hosts' => [['addr' => TEST_AS_HOST, 'port' => TEST_AS_PORT]],
                    'lifetime' => 24,
                    'prefix' => 'test_',
                ],
                [
                    'hosts' => [['addr' => TEST_AS_HOST, 'port' => TEST_AS_PORT]],
                    'lifetime' => 24,
                    'prefix' => 'test_',
                    'persistent' => false,
                    'options' => [],
                ]
            ],
            [
                [
                    'hosts' => [['addr' => TEST_AS_HOST, 'port' => TEST_AS_PORT]],
                ],
                [
                    'hosts' => [['addr' => TEST_AS_HOST, 'port' => TEST_AS_PORT]],
                    'lifetime' => 8600,
                    'prefix' => '',
                    'persistent' => false,
                    'options' => [],
                ]
            ],

        ];
    }
}
