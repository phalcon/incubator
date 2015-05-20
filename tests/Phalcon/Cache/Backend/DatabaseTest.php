<?php
namespace Phalcon\Test\Cache\Backend;

use Phalcon\Cache\Backend\Database as CacheBackend;
use Phalcon\Cache\Frontend\Data as CacheFrontend;
use Phalcon\Db\Adapter\Pdo\Sqlite as DbAdapter;

class DatabaseTest extends \PHPUnit_Framework_TestCase
{

    protected $key = 'DB_key';
    protected $data = 'DB_data';

    public function testPrefixed()
    {
        $backend = $this->getBackend('pre_');

        $this->runTests($backend);
        $this->runTests($backend, 1);
    }

    public function testNotPrefixed()
    {
        $backend = $this->getBackend();

        $this->runTests($backend);
        $this->runTests($backend, 1);
    }

    protected function getBackend($prefix = '')
    {
        $frontend   = new CacheFrontend(array('lifetime' => 10));
        $connection = new DbAdapter(array('dbname' => ':memory:'));

        // Make table structure
        $connection->getInternalHandler()->exec(
            'CREATE TABLE "cache_data" ("key_name" TEXT PRIMARY KEY, "data" TEXT, "lifetime" INTEGER)'
        );

        return new CacheBackend($frontend, array(
            'db'     => $connection,
            'table'  => 'cache_data',
            'prefix' => $prefix,
        ));
    }

    protected function runTests(CacheBackend $backend, $lifetime = null)
    {
        $backend->save($this->key, $this->data, $lifetime);

        $this->assertTrue($backend->exists($this->key));
        $this->assertEquals($this->data, $backend->get($this->key));
        $this->assertNotEmpty($backend->queryKeys());
        $this->assertNotEmpty($backend->queryKeys('DB_'));
        $this->assertTrue($backend->delete($this->key));
        $this->assertFalse($backend->delete($this->key));

        if (null !== $lifetime) {
            $backend->save($this->key, $this->data, $lifetime);

            $this->assertTrue($backend->exists($this->key, $lifetime));
            $this->assertEquals($this->data, $backend->get($this->key, $lifetime));

            $backend->save($this->key, $this->data, -$lifetime);
            $this->assertFalse($backend->exists($this->key, -$lifetime));
        }
    }
}
