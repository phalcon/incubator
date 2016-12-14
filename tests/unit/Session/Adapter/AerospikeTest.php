<?php

namespace Phalcon\Test\Session\Adapter;

use Phalcon\Session\Adapter\Aerospike as SessionHandler;
use Codeception\TestCase\Test;
use UnitTester;
use Aerospike;

/**
 * \Phalcon\Test\Session\Adapter\AerospikeTest
 * Tests for Phalcon\Session\Adapter\Aerospike component
 *
 * @copyright (c) 2011-2016 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Serghei Iakovlev <serghei@phalconphp.com>
 * @package   Phalcon\Test\Session\Adapter
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

    protected $ns = 'test';
    protected $set = 'session';
    protected $keys = [];

    /**
     * executed before each test
     */
    protected function _before()
    {
        if (!extension_loaded('aerospike')) {
            $this->markTestSkipped('The Aerospike module is not available.');
        }

        $this->getModule('Aerospike')->_reconfigure([
            'set'  => $this->set,
            'addr' => env('TEST_AS_HOST', '127.0.0.1'),
            'port' => env('TEST_AS_PORT', 3000)
        ]);
    }

    /**
     * executed after each test
     */
    protected function _after()
    {
        if (extension_loaded('aerospike')) {
            $this->cleanup();
        }
    }

    public function testShouldWriteSession()
    {
        $sessionId = 'abcdef123458';
        $session = new SessionHandler($this->getConfig());

        $data = [
                321   => microtime(true),
                'def' => '678',
                'xyz' => 'zyx'
            ];

        $this->assertTrue($session->write($sessionId, $data));
        $this->tester->seeInAerospike($sessionId, $data);
    }

    public function testShouldReadSession()
    {
        $sessionId = 'some_session_key';
        $session = new SessionHandler($this->getConfig());

        $data = [
                321   => microtime(true),
                'def' => '678',
                'xyz' => 'zyx'
            ];

        $this->tester->haveInAerospike($sessionId, $data);
        $this->keys[] = $sessionId;

        $this->assertEquals($data, $session->read($sessionId));
    }

    public function testShouldDestroySession()
    {
        $sessionId = 'abcdef123457';
        $session = new SessionHandler($this->getConfig());

        $data = [
                'abc' => 345,
                'def' => ['foo' => 'bar'],
                'zyx' => 'xyz'
            ];

        $this->tester->haveInAerospike($sessionId, $data);
        $session->destroy($sessionId);
        $this->tester->dontSeeInAerospike($sessionId);
    }

    private function cleanup()
    {
        $aerospike = new Aerospike(
            [
                'hosts' => [
                    ['addr' => env('TEST_AS_HOST', '127.0.0.1'), 'port' => env('TEST_AS_PORT', 3000)]
                ]
            ],
            false
        );

        foreach ($this->keys as $i => $key) {
            $aerospike->remove($this->buildKey($aerospike, $key));
            unset($this->keys[$i]);
        }
    }

    private function buildKey(Aerospike $aerospike, $key)
    {
        return $aerospike->initKey(
            $this->ns,
            $this->set,
            $key
        );
    }

    private function getConfig()
    {
        return [
            'hosts' => [
                ['addr' => env('TEST_AS_HOST', '127.0.0.1'), 'port' => env('TEST_AS_PORT', 3000)]
            ],
            'persistent' => false,
            'namespace'  => $this->ns,
            'set'        => $this->set,
            'prefix'     => '',
            'lifetime'   => 10,
            'uniqueId'   => 'some-unique-id',
            'options' => [
                \Aerospike::OPT_CONNECT_TIMEOUT => 1250,
                \Aerospike::OPT_WRITE_TIMEOUT => 1500
            ]
        ];
    }
}
