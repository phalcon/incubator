<?php
namespace Phalcon\Test\Db\Adapter;

use Phalcon\Db\Adapter\Factory as AdaptersFactory;
/**
 * FactoryTest
 *
 * @author Kachit
 */
class FactoryTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var array
     */
    protected $testable = [];

    protected function setUp() {
        $this->testable = [
            'adapter' => null,
            'host'     => '127.0.0.1',
            'username' => 'test',
            'password' => 'test',
            'dbname'   => 'test',
            'charset'  => 'utf8',
        ];
    }

    public function testLoadMysqlAdapter() {
        $this->testable['adapter'] = 'mysql';
//        $adapter = AdaptersFactory::load($this->testable);
//        $this->assertTrue(is_object($adapter));
//        $this->assertInstanceOf('Phalcon\Db\Adapter\Pdo\Mysql', $adapter);
//        $this->assertInstanceOf('Phalcon\Db\Adapter\Pdo', $adapter);
//        $this->assertInstanceOf('Phalcon\Db\Adapter', $adapter);
    }

    public function testLoadSqliteAdapter() {
        $this->testable['adapter'] = 'sqlite';
        $this->testable['dbname'] = __DIR__ . '/_fixtures/sqlite.db';
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
    public function testMissingConfigKeyAdapter() {
        unset($this->testable['adapter']);
        AdaptersFactory::load($this->testable);
    }

    /**
     * @expectedException \Phalcon\Db\Exception
     * @expectedExceptionMessage Database adapter Drizzle is not supported
     */
    public function testLoadUnsupportedAdapter() {
        $this->testable['adapter'] = 'drizzle';
        AdaptersFactory::load($this->testable);
    }
}