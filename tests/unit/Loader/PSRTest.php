<?php

namespace Phalcon\Test\Loader;

use ReflectionClass;
use ReflectionException;
use Phalcon\Loader\PSR as PsrLoader;
use Codeception\TestCase\Test;
use UnitTester;

/**
 * \Phalcon\Test\Loader\PSRTest
 * Tests the Phalcon\Loader\PSR component
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
class PSRTest extends Test
{
    /**
     * UnitTester Object
     * @var UnitTester
     */
    protected $tester;

    protected $loaders;
    protected $includePath;

    protected $baseDir;

    /**
     * executed before each test
     */
    protected function _before()
    {
        $this->baseDir =  dirname(__FILE__) . DIRECTORY_SEPARATOR . 'TestLib' . DIRECTORY_SEPARATOR;

        // Store original autoloaders
        $this->loaders = spl_autoload_functions();

        if (!is_array($this->loaders)) {
            // spl_autoload_functions does not return empty array when no
            // autoloaders registered...
            $this->loaders = [];
        }

        // Store original include_path
        $this->includePath = get_include_path();
    }

    /**
     * executed after each test
     */
    protected function _after()
    {
        // Restore original autoloaders
        $loaders = spl_autoload_functions();

        if (is_array($loaders)) {
            foreach ($loaders as $loader) {
                spl_autoload_unregister($loader);
            }
        }

        foreach ($this->loaders as $loader) {
            spl_autoload_register($loader);
        }

        // Restore original include_path
        set_include_path($this->includePath);
    }

    public function testShouldRegisterAndUnregister()
    {
        $loader = new PsrLoader();
        $loader->registerNamespaces(
            [
                'TestLib' => $this->baseDir
            ]
        );
        $loader->register();

        $loaders = spl_autoload_functions();
        $this->assertTrue(count($this->loaders) < count($loaders));
        $this->assertTrue(class_exists('\TestLib\Unusable\NamespacedClass'));

        list($actualObject, $actualMethod) = array_pop($loaders);
        $this->assertSame($loader, $actualObject);
        $this->assertSame('autoLoad', $actualMethod);

        $loader->unregister();
        $loaders = spl_autoload_functions();
        $this->assertEquals($loaders, $this->loaders);
    }

    public function testShouldPassToParentIfClassNotFitToPSR0()
    {
        $loader = new PsrLoader();
        $loader->registerNamespaces(
            [
                'TestLib\Parent' => $this->baseDir . 'Parent',
            ]
        );
        $loader->register();

        $loaders = spl_autoload_functions();

        $this->assertTrue(count($this->loaders) < count($loaders));
        $this->assertTrue(class_exists('\TestLib\Parent\TestClass'));
    }

    public function testShouldRegisterWithVariousExtensions()
    {
        $loader = new PsrLoader();
        $loader->registerNamespaces(
            [
                'TestLib' => $this->baseDir
            ]
        );
        $loader->setExtensions(['php', 'php5', 'inc']);
        $loader->register();

        $loaders = spl_autoload_functions();

        $this->assertTrue(count($this->loaders) < count($loaders));
        $this->assertTrue(class_exists('\TestLib\Unusable\Underscored_TestClass1'));
        $this->assertTrue(class_exists('\TestLib\Unusable\Underscored_TestClass2'));
        $this->assertTrue(class_exists('\TestLib\Unusable\Underscored_TestClass3'));
    }

    public function testShouldRegisterWithUnderscoredNamespace()
    {
        $loader = new PsrLoader();
        $loader->registerNamespaces(
            [
                'TestLib' => $this->baseDir
            ]
        );
        $loader->register();

        $loaders = spl_autoload_functions();
        $this->assertTrue(count($this->loaders) < count($loaders));
        $this->assertTrue(class_exists('\TestLib\Underscored_Namespace\Underscored_Class'));
    }

    /**
     * @expectedException ReflectionException
     */
    public function testShouldFailsOnCreateNonexistentClass()
    {
        $loader = new PsrLoader();
        $loader->registerNamespaces(
            [
                'TestLib' => $this->baseDir . 'Parent',
            ]
        );
        $loader->register();

        $loaders = spl_autoload_functions();
        $this->assertTrue(count($this->loaders) < count($loaders));

        $reflection = new ReflectionClass('\TestLib\Some\Fake\Classname');
        $reflection->getConstructor();
    }
}
