<?php

namespace Phalcon\Test\Annotations\Adapter;

use ReflectionProperty;
use Phalcon\Cache\Backend\Memory as CacheBackend;
use Phalcon\Cache\Frontend\Data as CacheFrontend;
use Codeception\TestCase\Test;
use UnitTester;

/**
 * \Phalcon\Annotations\Adapter\BaseTest
 * Tests for Phalcon\Annotations\Adapter\Base component
 *
 * @copyright (c) 2011-2015 Phalcon Team
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
class BaseTest extends Test
{
    const BASE_CLASS = '\Phalcon\Annotations\Adapter\Base';

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

    protected function getObject($options)
    {
        return $this->getMockForAbstractClass(
            self::BASE_CLASS,
            ['options' => $options],
            '',
            true,
            true,
            true,
            [],
            true
        );
    }

    /**
     * @dataProvider providerReadWrite
     * @param string $key
     * @param mixed $data
     */
    public function testWriteAnnotations($key, $data)
    {
        $mock = $this->getObject(null);
        $mock->expects($this->once())->method('prepareKey')->willReturn($key);

        $cacheBackend = new CacheBackend(new CacheFrontend(['lifetime' => 86400]));

        $mock->expects($this->once())->method('getCacheBackend')->willReturn($cacheBackend);
        $mock->write($key, $data, 86400);

        $this->assertEquals($data, $cacheBackend->get($key));
    }

    /**
     * @dataProvider providerReadWrite
     * @param string $key
     * @param mixed $data
     */
    public function testReadAnnotations($key, $data)
    {
        $mock = $this->getObject(null);
        $mock->expects($this->once())->method('prepareKey')->willReturn($key);

        $cacheBackend = new CacheBackend(new CacheFrontend(['lifetime' => 86400]));
        $cacheBackend->save($key, $data, 86400);

        $mock->expects($this->once())->method('getCacheBackend')->willReturn($cacheBackend);

        $this->assertEquals($data, $mock->read($key));
    }

    /**
     * @dataProvider providerConstructor
     * @param mixed $options
     * @param array $expected
     */
    public function testConstructor($options, $expected)
    {
        $mock = $this->getObject($options);
        $reflectedProperty = new ReflectionProperty(get_class($mock), 'options');
        $reflectedProperty->setAccessible(true);
        $this->assertEquals($expected, $reflectedProperty->getValue($mock));
    }

    public function testHasDefaultLifetime()
    {
        $this->assertClassHasStaticAttribute('defaultLifetime', self::BASE_CLASS);
    }

    public function testHasDefaultPrefix()
    {
        $this->assertClassHasStaticAttribute('defaultPrefix', self::BASE_CLASS);
    }

    public function testHasOptions()
    {
        $this->assertClassHasAttribute('options', self::BASE_CLASS);
    }

    public function providerReadWrite()
    {
        return [
            ['test1', 'data1'],
            ['test1', (object) ['key' => 'value']],
            ['test1', ['key' => 'value']],
            ['test1', null]
        ];
    }

    public function providerConstructor()
    {
        return [
            [['lifetime' => 23],                      ['lifetime' => 23, 'prefix' => '']],
            [['prefix' => 'test_'],                   ['lifetime' => 8600, 'prefix' => 'test_']],
            [['randomValue' => 'test_'],              ['randomValue' => 'test_', 'lifetime' => 8600, 'prefix' => '']],
            [[123 => 'test_'],                        [123 => 'test_', 'lifetime' => 8600, 'prefix' => '']],
            [['lifetime' => 24, 'prefix' => 'test_'], ['lifetime' => 24, 'prefix' => 'test_']],
            [[],                                      ['lifetime' => 8600, 'prefix' => '']],
            [null,                                    ['lifetime' => 8600, 'prefix' => '']]
        ];
    }
}
