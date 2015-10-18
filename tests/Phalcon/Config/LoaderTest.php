<?php
namespace Phalcon\Test\Config;

use Phalcon\Config\Loader as ConfigLoader;
/**
 * Loader Test
 *
 * @author Kachit
 */
class LoaderTest extends \PHPUnit_Framework_TestCase {

    public function testLoadPhpFileConfig() {
        $file = __DIR__ . '/_fixtures/config.php';
        $config = ConfigLoader::load($file);
        $this->assertTrue(is_object($config));
        $this->assertInstanceOf('Phalcon\Config\Adapter\Php', $config);
        $this->assertInstanceOf('Phalcon\Config', $config);
        $this->assertEquals('bar', $config->phalcon->foo);
    }

    public function testLoadIniFileConfig() {
        $file = __DIR__ . '/_fixtures/config.ini';
        $config = ConfigLoader::load($file);
        $this->assertTrue(is_object($config));
        $this->assertInstanceOf('Phalcon\Config\Adapter\Ini', $config);
        $this->assertInstanceOf('Phalcon\Config', $config);
        $this->assertEquals('bar', $config->phalcon->foo);
    }

    public function testLoadJsonFileConfig() {
        $file = __DIR__ . '/_fixtures/config.json';
        $config = ConfigLoader::load($file);
        $this->assertTrue(is_object($config));
        $this->assertInstanceOf('Phalcon\Config\Adapter\Json', $config);
        $this->assertInstanceOf('Phalcon\Config', $config);
        $this->assertEquals('bar', $config->phalcon->foo);
    }

    /**
     * @expectedException \Phalcon\Config\Exception
     * @expectedExceptionMessage Config file not found
     */
    public function testLoadWrongFilePath() {
        $file = __DIR__ . '/_fixtures/config.jason';
        ConfigLoader::load($file);
    }

    /**
     * @expectedException \Phalcon\Config\Exception
     * @expectedExceptionMessage Config adapter for .txt files is not support
     */
    public function testLoadUnsupportedConfigFile() {
        $file = __DIR__ . '/_fixtures/config.txt';
        ConfigLoader::load($file);
    }
}
