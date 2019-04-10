<?php

namespace Phalcon\Test\Aerospike\Annotations\Adapter;

use UnitTester;
use ReflectionMethod;
use ReflectionProperty;
use Phalcon\Test\Codeception\UnitTestCase as Test;
use Phalcon\Annotations\Exception;
use Phalcon\Annotations\Adapter\Aerospike;
use Phalcon\Cache\Backend\Aerospike as CacheBackend;

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
        if (!extension_loaded('aerospike')) {
            $this->markTestSkipped('The Aerospike module is not available.');
        }
    }

    public function testHasAerospikeProperty()
    {
        $this->assertClassHasAttribute(
            'aerospike',
            Aerospike::class
        );
    }

    public function testHasNamespaceProperty()
    {
        $this->assertClassHasAttribute(
            'namespace',
            Aerospike::class
        );
    }

    public function testHasSetProperty()
    {
        $this->assertClassHasAttribute(
            'set',
            Aerospike::class
        );
    }

    /**
     * @dataProvider providerReadWrite
     * @param string $key
     * @param mixed $data
     */
    public function testShouldReadAndWriteToAerospikeWithoutPrefix($key, $data)
    {
        $object = new Aerospike(
            [
                'hosts' => $this->getHostConfig(),
            ]
        );

        $object->write($key, $data);

        $this->assertEquals(
            $data,
            $object->read($key)
        );
    }

    /**
     * @dataProvider providerReadWrite
     * @param string $key
     * @param mixed $data
     */
    public function testShouldReadAndWriteToAerospikeWithPrefix($key, $data)
    {
        $object = new Aerospike(
            [
                'hosts'  => $this->getHostConfig(),
                'prefix' => 'test_',
            ]
        );

        $object->write($key, $data);

        $this->assertEquals(
            $data,
            $object->read($key)
        );
    }

    public function testShouldGetCacheBackendThroughGetter()
    {
        $object = new Aerospike(
            [
                'hosts' => $this->getHostConfig(),
            ]
        );

        $reflectedMethod = new ReflectionMethod(
            get_class($object),
            'getCacheBackend'
        );

        $reflectedMethod->setAccessible(true);

        $this->assertInstanceOf(
            CacheBackend::class,
            $reflectedMethod->invoke($object)
        );
    }

    public function testShouldGetCacheBackendThroughReflectionSetter()
    {
        $object = new Aerospike(
            [
                'hosts' => $this->getHostConfig(),
            ]
        );

        $mock = $this->getMock(
            CacheBackend::class,
            [],
            [],
            '',
            false
        );

        $reflectedProperty = new ReflectionProperty(
            get_class($object),
            'aerospike'
        );

        $reflectedProperty->setAccessible(true);

        $reflectedProperty->setValue($object, $mock);

        $reflectedMethod = new ReflectionMethod(
            get_class($object),
            'getCacheBackend'
        );

        $reflectedMethod->setAccessible(true);

        $this->assertInstanceOf(
            CacheBackend::class,
            $reflectedMethod->invoke($object)
        );
    }

    /**
     * @dataProvider providerKey
     * @param mixed $key
     */
    public function testShouldPrepareKey($key)
    {
        $object = new Aerospike(
            [
                'hosts' => $this->getHostConfig(),
            ]
        );

        $reflectedMethod = new ReflectionMethod(
            get_class($object),
            'prepareKey'
        );

        $reflectedMethod->setAccessible(true);

        $this->assertEquals(
            $key,
            $reflectedMethod->invoke($object, $key)
        );
    }

    /**
     * @dataProvider providerConstructor
     * @param array $options
     * @param array $expected
     */
    public function testShouldCreateAerospikeAdapterInstanceAndSetOptions($options, $expected)
    {
        $object = new Aerospike($options);

        $reflectedProperty = new ReflectionProperty(
            get_class($object),
            'options'
        );

        $reflectedProperty->setAccessible(true);

        $this->assertEquals(
            $expected,
            $reflectedProperty->getValue($object)
        );
    }

    /**
     * @dataProvider providerInvalidConstructor
     * @param array $options
     */
    public function testShouldCatchExceptionWhenInvalidParamsPassed($options)
    {
        $this->setExpectedException(
            Exception::class,
            'No hosts given in options'
        );

        new Aerospike($options);
    }

    public function providerReadWrite()
    {
        // This key is needed in order not to break your real data
        $key = hash(
            'sha256',
            json_encode(
                [
                    __CLASS__,
                    __METHOD__,
                    __FILE__,
                    __LINE__,
                ]
            )
        );

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
            ['_key1'],
        ];
    }

    public function providerInvalidConstructor()
    {
        return [
            [
                [],
            ],
            [
                ['hosts' => null],
            ],
            [
                ['hosts' => []],
            ],
            [
                ['hosts' => [[]]],
            ]
        ];
    }

    public function providerConstructor()
    {
        return [ //$this->getConfig()
            [
                [
                    'hosts'    => $this->getHostConfig(),
                    'lifetime' => 23,
                ],
                [
                    'hosts'      => $this->getHostConfig(),
                    'lifetime'   => 23,
                    'prefix'     => '',
                    'persistent' => false,
                    'options'    => [],
                ],
            ],

            [
                [
                    'hosts'    => $this->getHostConfig(),
                    'lifetime' => 23,
                    'options'  => [
                        1 => 1250,
                        3  => 1500
                    ]
                ],
                [
                    'hosts'      => $this->getHostConfig(),
                    'lifetime'   => 23,
                    'prefix'     => '',
                    'persistent' => false,
                    'options'    => [
                        1 => 1250,
                        3 => 1500,
                    ],
                ],
            ],

            [
                [
                    'hosts'      => $this->getHostConfig(),
                    'lifetime'   => 23,
                    'persistent' => true,
                ],
                [
                    'hosts'      => $this->getHostConfig(),
                    'lifetime'   => 23,
                    'prefix'     => '',
                    'persistent' => true,
                    'options'    => [],
                ],
            ],

            [
                [
                    'hosts'  => $this->getHostConfig(),
                    'prefix' => 'test_',
                ],
                [
                    'hosts'      => $this->getHostConfig(),
                    'lifetime'   => 8600,
                    'prefix'     => 'test_',
                    'persistent' => false,
                    'options'    => [],
                ],
            ],

            [
                [
                    'hosts'       => $this->getHostConfig(),
                    'randomValue' => 'test_',
                ],
                [
                    'hosts'       => $this->getHostConfig(),
                    'randomValue' => 'test_',
                    'lifetime'    => 8600,
                    'prefix'      => '',
                    'persistent'  => false,
                    'options'     => [],
                ],
            ],

            [
                [
                    'hosts' => $this->getHostConfig(),
                    123     => 'test_'
                ],
                [
                    'hosts'      => $this->getHostConfig(),
                    123          => 'test_',
                    'lifetime'   => 8600,
                    'prefix'     => '',
                    'persistent' => false,
                    'options'    => [],
                ],
            ],

            [
                [
                    'hosts'    => $this->getHostConfig(),
                    'lifetime' => 24,
                    'prefix'   => 'test_',
                ],
                [
                    'hosts'      => $this->getHostConfig(),
                    'lifetime'   => 24,
                    'prefix'     => 'test_',
                    'persistent' => false,
                    'options'    => [],
                ],
            ],

            [
                [
                    'hosts' => $this->getHostConfig(),
                ],
                [
                    'hosts'      => $this->getHostConfig(),
                    'lifetime'   => 8600,
                    'prefix'     => '',
                    'persistent' => false,
                    'options'    => [],
                ],
            ],
        ];
    }

    private function getHostConfig()
    {
        return [
            [
                'addr' => env('TEST_AS_HOST', '127.0.0.1'),
                'port' => (int) env('TEST_AS_PORT', 3000),
            ]
        ];
    }
}
