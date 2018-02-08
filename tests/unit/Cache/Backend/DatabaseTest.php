<?php

namespace Phalcon\Test\Cache\Backend;

use Phalcon\Cache\Backend\Database as CacheBackend;
use Phalcon\Cache\Frontend\Data as CacheFrontend;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Test\Codeception\UnitTestCase as Test;

/**
 * \Phalcon\Test\Cache\Backend\DatabaseTest
 * Tests for Phalcon\Cache\Backend\Database component
 *
 * @copyright (c) 2011-2016 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Nikita Vershinin <endeveit@gmail.com>
 * @package   Phalcon\Test\Cache\Backend
 * @group     db
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class DatabaseTest extends Test
{
    protected $key = 'DB_key';
    protected $data = 'DB_data';

    /**
     * @dataProvider incorrectDbProvider
     * @expectedException \Phalcon\Cache\Exception
     * @expectedExceptionMessage Parameter "db" is required and it must be an instance of Phalcon\Acl\AdapterInterface
     * @param array $options
     */
    public function testShouldThrowExceptionIfDbIsMissingOrInvalid($options)
    {
        new CacheBackend(new CacheFrontend, $options);
    }

    public function incorrectDbProvider()
    {
        return [
            [['abc' => '']],
            [['db'  => null]],
            [['db'  => true]],
            [['db'  => __CLASS__]],
            [['db'  => new \stdClass()]],
            [['db'  => []]],
            [['db'  => microtime(true)]],
            [['db'  => PHP_INT_MAX]],
        ];
    }

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
        $frontend   = new CacheFrontend(['lifetime' => 10]);
        $connection = new DbAdapter(
            [
                'host'     => env('TEST_DB_HOST', '127.0.0.1'),
                'username' => env('TEST_DB_USER', 'incubator'),
                'password' => env('TEST_DB_PASSWD', 'secret'),
                'dbname'   => env('TEST_DB_NAME', 'incubator'),
                'charset'  => env('TEST_DB_CHARSET', 'utf8'),
                'port'     => env('TEST_DB_PORT', 3306),
            ]
        );

        return new CacheBackend($frontend, [
            'db'     => $connection,
            'table'  => 'cache_data',
            'prefix' => $prefix,
        ]);
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
