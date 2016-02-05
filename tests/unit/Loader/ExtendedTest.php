<?php

namespace Phalcon\Test\Loader;

use Phalcon\Loader\Extended;
use Codeception\TestCase\Test;
use UnitTester;

/**
 * \Phalcon\Test\Loader\ExtendedTest
 * Tests the Phalcon\Loader\Extended component
 *
 * @copyright (c) 2011-2016 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Serghei Iakovlev <serghei@phalconphp.com>
 * @package   Phalcon\Loader
 * @group     Loader
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class ExtendedTest extends Test
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

    public function testShouldAlwaysPreparePathsAndConvertToArray()
    {
        $loader = new Extended();

        $this->assertTrue($loader->getNamespaces() === null);

        $loader->registerNamespaces(
            [
                'Example\Base' => __DIR__ . '/TestLib/Extended/Example/Base/'
            ]
        );

        $this->assertEquals(
            $loader->getNamespaces(),
            [
                'Example\Base' => [__DIR__ . '/TestLib/Extended/Example/Base/']
            ]
        );
    }

    public function testShouldRegisterNamespacesForMultipleDirectories()
    {
        $loader = new Extended();

        $loader->registerNamespaces(
            [
                'Example\Adapter' => [
                    __DIR__ . '/TestLib/Extended/Example/Adapter/src/',
                    __DIR__ . '/TestLib/Extended/Example/Adapter/test/',
                ]
            ]
        );

        $this->assertEquals(
            $loader->getNamespaces(),
            [
                'Example\Adapter' => [
                    __DIR__ . '/TestLib/Extended/Example/Adapter/src/',
                    __DIR__ . '/TestLib/Extended/Example/Adapter/test/',
                ]
            ]
        );

        $loader->register();

        $this->assertTrue(class_exists('Example\Adapter\SrcClass'));
        $srcClass = new \Example\Adapter\SrcClass();
        $this->assertEquals(get_class($srcClass), 'Example\Adapter\SrcClass');

        $this->assertTrue(class_exists('Example\Adapter\TestClass'));
        $testClass = new \Example\Adapter\TestClass();
        $this->assertEquals(get_class($testClass), 'Example\Adapter\TestClass');
    }

    public function testShouldMergeNamespacesForMultipleDirectories()
    {
        $loader = new Extended();

        $loader->registerNamespaces(
            [
                'Example\Base' => __DIR__ . '/TestLib/Extended/Example/Base/'
            ]
        );

        $loader->registerNamespaces(
            [
                'Example\Adapter' => [
                    __DIR__ . '/TestLib/Extended/Example/Adapter/src/'
                ]
            ],
            true
        );

        $loader->registerNamespaces(
            [
                'Example\Adapter' => [
                    __DIR__ . '/TestLib/Extended/Example/Adapter/test/',
                ]
            ],
            true
        );

        $this->assertEquals(
            $loader->getNamespaces(),
            [
                'Example\Base' => [__DIR__ . '/TestLib/Extended/Example/Base/'],
                'Example\Adapter' => [
                    __DIR__ . '/TestLib/Extended/Example/Adapter/src/',
                    __DIR__ . '/TestLib/Extended/Example/Adapter/test/',
                ]
            ]
        );
    }

    public function testShouldMergeForEmptyNamespace()
    {
        $loader = new Extended();

        $loader->registerNamespaces(
            [
                'Example\Base' => __DIR__ . '/TestLib/Extended/Example/Base/'
            ],
            true
        );

        $this->assertEquals(
            $loader->getNamespaces(),
            [
                'Example\Base' => [__DIR__ . '/TestLib/Extended/Example/Base/'],
            ]
        );
    }

    /**
     * @dataProvider providerWrongClassName
     * @param        mixed $className
     */
    public function testShouldReceiveWhenClassNameIsWrong($className)
    {
        $loader = new Extended();

        $this->assertFalse($loader->autoLoad($className));
    }

    public function providerWrongClassName()
    {
        return [
            [1],
            [3.14],
            [null],
            [false],
            [true],
            [''],
            [new \stdClass()],
            [[]],
            [
                function () {
                    return 'foo';
                }
            ],
        ];
    }
}
