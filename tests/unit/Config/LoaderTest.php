<?php

namespace Phalcon\Test\Config;

use Phalcon\Config\Loader as ConfigLoader;
use Codeception\TestCase\Test;
use UnitTester;

/**
 * \Phalcon\Test\Config\LoaderTest
 * Tests for Phalcon\Config\Loader component
 *
 * @copyright (c) 2011-2016 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Anton Kornilov <kachit@yandex.ru>
 * @package   Phalcon\Test\Config
 * @group     Config
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class LoaderTest extends Test
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

    public function testLoadPhpFileConfig()
    {
        $file = INCUBATOR_FIXTURES . 'Config/config.php';
        $config = ConfigLoader::load($file);

        $this->assertTrue(is_object($config));
        $this->assertInstanceOf('Phalcon\Config\Adapter\Php', $config);
        $this->assertInstanceOf('Phalcon\Config', $config);
        $this->assertEquals('bar', $config->phalcon->foo);
    }

    public function testLoadPhp5FileConfig()
    {
        $file = INCUBATOR_FIXTURES . 'Config/config.php5';
        $config = ConfigLoader::load($file);

        $this->assertTrue(is_object($config));
        $this->assertInstanceOf('Phalcon\Config\Adapter\Php', $config);
        $this->assertInstanceOf('Phalcon\Config', $config);
        $this->assertEquals('bar', $config->phalcon->foo);
    }

    public function testLoadIncFileConfig()
    {
        $file = INCUBATOR_FIXTURES . 'Config/config.inc';
        $config = ConfigLoader::load($file);

        $this->assertTrue(is_object($config));
        $this->assertInstanceOf('Phalcon\Config\Adapter\Php', $config);
        $this->assertInstanceOf('Phalcon\Config', $config);
        $this->assertEquals('bar', $config->phalcon->foo);
    }

    public function testLoadIniFileConfig()
    {
        $file = INCUBATOR_FIXTURES . 'Config/config.ini';
        $config = ConfigLoader::load($file);

        $this->assertTrue(is_object($config));
        $this->assertInstanceOf('Phalcon\Config\Adapter\Ini', $config);
        $this->assertInstanceOf('Phalcon\Config', $config);
        $this->assertEquals('bar', $config->phalcon->foo);
    }

    /**
     * @requires extension json
     */
    public function testLoadJsonFileConfig()
    {
        $file = INCUBATOR_FIXTURES . 'Config/config.json';
        $config = ConfigLoader::load($file);

        $this->assertTrue(is_object($config));
        $this->assertInstanceOf('Phalcon\Config\Adapter\Json', $config);
        $this->assertInstanceOf('Phalcon\Config', $config);
        $this->assertEquals('bar', $config->phalcon->foo);
    }

    /**
     * @requires extension yaml
     */
    public function testLoadYamlFileConfig()
    {
        $file = INCUBATOR_FIXTURES . 'Config/config.yaml';
        $config = ConfigLoader::load($file);

        $this->assertTrue(is_object($config));
        $this->assertInstanceOf('Phalcon\Config\Adapter\Yaml', $config);
        $this->assertInstanceOf('Phalcon\Config', $config);
        $this->assertEquals('bar', $config->phalcon->foo);
    }

    /**
     * @requires extension yaml
     */
    public function testLoadYmlFileConfig()
    {
        $file = INCUBATOR_FIXTURES . 'Config/config.yml';
        $config = ConfigLoader::load($file);

        $this->assertTrue(is_object($config));
        $this->assertInstanceOf('Phalcon\Config\Adapter\Yaml', $config);
        $this->assertInstanceOf('Phalcon\Config', $config);
        $this->assertEquals('bar', $config->phalcon->foo);
    }

    /**
     * @expectedException \Phalcon\Config\Exception
     * @expectedExceptionMessage Config file not found
     */
    public function testLoadWrongFilePath()
    {
        $file = INCUBATOR_FIXTURES . 'Config/config.jason';
        ConfigLoader::load($file);
    }

    /**
     * @expectedException \Phalcon\Config\Exception
     * @expectedExceptionMessage Config adapter for .txt files is not support
     */
    public function testLoadUnsupportedConfigFile()
    {
        $file = INCUBATOR_FIXTURES . 'Config/config.txt';
        ConfigLoader::load($file);
    }

    public function testLoadDirValid()
    {
        $dir = INCUBATOR_FIXTURES . 'Config/cfg';
        $config = ConfigLoader::loadDir($dir);
        $this->assertTrue(is_object($config));
        $this->assertInstanceOf('Phalcon\Config', $config);
        $this->assertEquals('bar', $config->phalcon->foo);
        $this->assertEquals('bar1', $config->phalcon->foo1);
        $this->assertEquals('bar2', $config->phalcon->foo2);
        $this->assertEquals('bar3', $config->phalcon->foo3);
    }

    /**
     * @expectedException \Phalcon\Config\Exception
     * @expectedExceptionMessage Config directory not found
     */
    public function testLoadDirInValidDir()
    {
        $dir = INCUBATOR_FIXTURES . 'Config/cfg1';
        ConfigLoader::loadDir($dir);
    }

    /**
     * @expectedException \Phalcon\Config\Exception
     * @expectedExceptionMessage Config adapter for .txt files is not support
     */
    public function testLoadDirInValidConfigFiles()
    {
        $dir = INCUBATOR_FIXTURES . 'Config';
        ConfigLoader::loadDir($dir);
    }
}
