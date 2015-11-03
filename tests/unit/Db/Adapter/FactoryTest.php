<?php
namespace Phalcon\Test\Db\Adapter;

use Phalcon\Db\Adapter\Factory as AdaptersFactory;
use Codeception\TestCase\Test;
/**
 * \Phalcon\Test\Db\Adapter\Factory
 * Tests for Phalcon\Db\Adapter\Factory component
 *
 * @copyright (c) 2011-2015 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Anton Kornilov <kachit@yandex.ru>
 * @package   Phalcon\Test\Db\Adapter
 * @group     Db
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class FactoryTest extends Test {

    /**
     * @var array
     */
    protected $testable = [];

    /**
     * executed before each test
     */
    protected function _before()
    {
        $this->testable = [
            'adapter'  => null,
            'host'     => 'localhost',
            'username' => 'root',
            'password' => '',
            'dbname'   => 'incubator_tests',
            'charset'  => 'utf8',
        ];
    }

    /**
     * executed after each test
     */
    protected function _after()
    {
    }

    public function testLoadMysqlAdapter()
    {
        $this->testable['adapter'] = 'mysql';
        $adapter = AdaptersFactory::load($this->testable);
        $this->assertTrue(is_object($adapter));
        $this->assertInstanceOf('Phalcon\Db\Adapter\Pdo\Mysql', $adapter);
        $this->assertInstanceOf('Phalcon\Db\Adapter\Pdo', $adapter);
        $this->assertInstanceOf('Phalcon\Db\Adapter', $adapter);
    }

    public function testLoadSqliteAdapter()
    {
        $this->testable['adapter'] = 'sqlite';
        $this->testable['dbname'] = INCUBATOR_FIXTURES . 'Db/sqlite.db';
        $adapter = AdaptersFactory::load($this->testable);
        $this->assertTrue(is_object($adapter));
        $this->assertInstanceOf('Phalcon\Db\Adapter\Pdo\Sqlite', $adapter);
        $this->assertInstanceOf('Phalcon\Db\Adapter\Pdo', $adapter);
        $this->assertInstanceOf('Phalcon\Db\Adapter', $adapter);
    }

    /**
     * @expectedException \Phalcon\Db\Exception
     * @expectedExceptionMessage Adapter option must be required
     */
    public function testMissingConfigKeyAdapter()
    {
        unset($this->testable['adapter']);
        AdaptersFactory::load($this->testable);
    }

    /**
     * @expectedException \Phalcon\Db\Exception
     * @expectedExceptionMessage Adapter option must be required
     */
    public function testEmptyConfigKeyAdapter()
    {
        AdaptersFactory::load($this->testable);
    }

    /**
     * @expectedException \Phalcon\Db\Exception
     * @expectedExceptionMessage Database adapter Drizzle is not supported
     */
    public function testLoadUnsupportedAdapter()
    {
        $this->testable['adapter'] = 'drizzle';
        AdaptersFactory::load($this->testable);
    }
}