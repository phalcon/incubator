<?php

namespace Phalcon\Loader;

use PHPUnit_Framework_TestCase as TestCase;
use ReflectionException;
use ReflectionClass;
use Phalcon\Loader\PSR as PsrLoader;

/**
 * \Phalcon\Loader\PSRTest
 * Tests the Phalcon\Loader\PSR component
 *
 * @copyright (c) 2011-2015 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Serghei Iakovlev <sadhooklay@gmail.com>
 * @package   Phalcon\Loader
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class PSRTest extends TestCase
{
    protected $loaders;
    protected $includePath;
    protected $currentDir;

    public function setUp()
    {
        // Store original autoloaders
        $this->loaders = spl_autoload_functions();

        if (!is_array($this->loaders)) {
            // spl_autoload_functions does not return empty array when no
            // autoloaders registered...
            $this->loaders = array();
        }

        // Store original include_path
        $this->includePath = get_include_path();

        $this->currentDir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
    }

    public function tearDown()
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
            array(
                'TestLib' => $this->currentDir . 'TestLib' . DIRECTORY_SEPARATOR
            )
        );
        $loader->register();

        $loaders = spl_autoload_functions();
        $this->assertTrue(count($this->loaders) < count($loaders));

        $testClass1 = new \TestLib\Unusable\NamespacedClass();

        list($actualObject, $actualMethod) = array_pop($loaders);
        $this->assertSame($loader,    $actualObject);
        $this->assertSame('autoLoad', $actualMethod);

        $loader->unregister();
        $loaders = spl_autoload_functions();
        $this->assertEquals($loaders, $this->loaders);
    }

    public function testShouldPassToParentIfClassNotFitToPSR0()
    {
        $loader = new PsrLoader();
        $loader->registerNamespaces(
            array(
                'TestLib\Parent' => $this->currentDir . 'TestLib' . DIRECTORY_SEPARATOR . 'Parent',
            )
        );
        $loader->register();

        $loaders = spl_autoload_functions();
        $this->assertTrue(count($this->loaders) < count($loaders));

        $testClass1 = new \TestLib\Parent\TestClass();
    }

    public function testShouldRegisterWithVariousExtensions()
    {
        $loader = new PsrLoader();
        $loader->registerNamespaces(
            array(
                'TestLib' => $this->currentDir . 'TestLib' . DIRECTORY_SEPARATOR
            )
        );
        $loader->setExtensions(array('php', 'php5', 'inc'));
        $loader->register();

        $loaders = spl_autoload_functions();
        $this->assertTrue(count($this->loaders) < count($loaders));

        $testClass1 = new \TestLib\Unusable\Underscored_TestClass1();
        $testClass2 = new \TestLib\Unusable\Underscored_TestClass2();
        $testClass3 = new \TestLib\Unusable\Underscored_TestClass3();
    }

    /**
     * @expectedException ReflectionException
     */
    public function testShouldFailsOnCreateNonexistentClass()
    {
        $loader = new PsrLoader();
        $loader->registerNamespaces(
            array(
                'TestLib' => $this->currentDir . 'TestLib' . DIRECTORY_SEPARATOR . 'Parent',
            )
        );
        $loader->register();

        $loaders = spl_autoload_functions();
        $this->assertTrue(count($this->loaders) < count($loaders));

        $reflection = new ReflectionClass('\TestLib\Some\Fake\Classname');
        $constructor = $reflection->getConstructor();
    }
}
