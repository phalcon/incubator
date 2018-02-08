<?php

namespace Phalcon\Test\Config;

use Phalcon\Config\Loader as ConfigLoader;
use Phalcon\Test\Codeception\UnitTestCase as Test;

/**
 * \Phalcon\Test\Config\LoaderTest
 * Tests for Phalcon\Config\Loader component
 *
 * @copyright (c) 2011-2016 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Anton Kornilov <kachit@yandex.ru>
 * @package   Phalcon\Test\Config
 * @group     config
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
}
