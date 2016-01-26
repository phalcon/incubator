<?php

namespace Phalcon\Test\Cache\Backend;

use Phalcon\Cache\Frontend\Data as CacheData;
use Phalcon\Cache\Frontend\Output as CacheOutput;
use Phalcon\Cache\Backend\Aerospike as CacheAerospike;
use Codeception\TestCase\Test;
use UnitTester;
use Aerospike;

/**
 * \Phalcon\Test\Cache\Backend\AerospikeTest
 * Tests for Phalcon\Cache\Backend\Aerospike component
 *
 * @copyright (c) 2011-2016 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Serghei Iakovlev <serghei@phalconphp.com>
 * @package   Phalcon\Test\Cache\Backend
 * @group     aerospike
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class AerospikeTest extends Test
{
    /**
     * UnitTester Object
     * @var UnitTester
     */
    protected $tester;

    protected $keys = [];

    /**
     * executed before each test
     */
    protected function _before()
    {
        if (!extension_loaded('aerospike')) {
            $this->markTestSkipped(
                'The aerospike module is not available.'
            );
        } else {
            $this->getModule('Aerospike')->_reconfigure(['set' => 'cache']);
        }
    }

    /**
     * executed after each test
     */
    protected function _after()
    {
        $this->cleanup();
    }

    public function testShouldIncrementValue()
    {
        $cache = $this->getAdapter();
        $this->tester->haveInAerospike('increment', 1);

        $this->assertEquals(2, $cache->increment('increment'));
        $this->assertEquals(4, $cache->increment('increment', 2));
        $this->assertEquals(14, $cache->increment('increment', 10));
    }

    public function testShouldDecrementValue()
    {
        $cache = $this->getAdapter();
        $this->tester->haveInAerospike('decrement', 100);

        $this->assertEquals(99, $cache->decrement('decrement'));
        $this->assertEquals(97, $cache->decrement('decrement', 2));
        $this->assertEquals(87, $cache->decrement('decrement', 10));
    }

    public function testShouldGetKeys()
    {
        $cache = $this->getAdapter(null);
        $this->assertEquals(0, count($cache->queryKeys()));

        $cache->save('a', 1, 10);
        $cache->save('long-key', 'long-val', 10);
        $cache->save('bcd', 3, 10);

        $this->keys[] = 'a';
        $this->keys[] = 'long-key';
        $this->keys[] = 'bcd';

        $keys = $cache->queryKeys();
        sort($keys);

        $this->assertEquals(['a', 'bcd', 'long-key'], $keys);
        $this->assertEquals(['long-key'], $cache->queryKeys('long'));
    }

    public function testShouldSaveData()
    {
        $cache = $this->getAdapter();
        $this->keys[] = 'test-data';

        $data = [1, 2, 3, 4, 5];
        $cache->save('test-data', $data);
        $this->tester->seeInAerospike('test-data', serialize($data));

        $data = "sure, nothing interesting";
        $cache->save('test-data', $data);
        $this->tester->seeInAerospike('test-data', serialize($data));
    }

    public function testShouldDeleteData()
    {
        $cache = $this->getAdapter();
        $this->keys[] = 'test-data';

        $data = rand(0, 99);
        $this->tester->haveInAerospike('test-data', $data);

        $this->assertTrue($cache->delete('test-data'));
        $this->tester->dontSeeInAerospike('test-data');
    }

    public function testShouldUseOutputFrontend()
    {
        $time = date('H:i:s');

        $frontCache = new CacheOutput(['lifetime' => 10]);
        $cache = new CacheAerospike($frontCache, $this->getConfig());

        ob_start();

        $content = $cache->start('test-output');
        $this->keys[] = 'test-output';
        $this->assertNull($content);

        echo $time;

        $obContent = ob_get_contents();
        $cache->save(null, null, null, true);

        ob_end_clean();

        $this->assertEquals($time, $obContent);
        $this->assertEquals($time, $cache->get('test-output'));

        $content = $cache->start('test-output');

        $this->assertEquals($content, $obContent);
        $this->assertEquals($content, $cache->get('test-output'));

        $keys = $cache->queryKeys();
        $this->assertEquals([0 => 'test-output'], $keys);
    }

    private function getAdapter($lifetime = 20)
    {
        if ($lifetime) {
            $frontCache = new CacheData(['lifetime' => $lifetime]);
        } else {
            $frontCache = new CacheData;
        }

        $cache = new CacheAerospike($frontCache, $this->getConfig());

        return $cache;
    }

    private function getConfig()
    {
        return [
            'hosts' => [
                ['addr' => TEST_AS_HOST, 'port' => TEST_AS_PORT]
            ],
            'persistent' => false, // important
            'namespace'  => 'test',
            'prefix'     => ''
        ];
    }

    private function cleanup()
    {
        $aerospike = new Aerospike(['hosts' => [['addr' => TEST_AS_HOST, 'port' => TEST_AS_PORT]]], false);

        foreach ($this->keys as $i => $key) {
            $aerospike->remove($this->buildKey($aerospike, $key));
            unset($this->keys[$i]);
        }
    }

    private function buildKey(Aerospike $aerospike, $key)
    {
        return $aerospike->initKey(
            'test',
            'cache',
            $key
        );
    }
}
