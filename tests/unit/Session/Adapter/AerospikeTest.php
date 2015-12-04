<?php

namespace Phalcon\Test\Session\Adapter;

use Phalcon\Session\Adapter\Aerospike as SessionHandler;
use Codeception\TestCase\Test;
use UnitTester;

/**
 * \Phalcon\Test\Session\Adapter\AerospikeTest
 * Tests for Phalcon\Session\Adapter\Aerospike component
 *
 * @copyright (c) 2011-2015 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Serghei Iakovlev <serghei@phalconphp.com>
 * @package   Phalcon\Test\Session\Adapter
 * @group     Session
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

    /**
     * executed before each test
     */
    protected function _before()
    {
        if (!extension_loaded('aerospike')) {
            $this->markTestSkipped(
                'The aerospike module is not available.'
            );
        }
    }

    /**
     * executed after each test
     */
    protected function _after()
    {
    }

    private function getConfig()
    {
        return [
            'hosts' => [
                ['addr' => TEST_AS_HOST, 'port' => TEST_AS_PORT]
            ],
            'persistent' => false,
            'namespace'  => 'test',
            'prefix'     => 'session_',
            'lifetime'   => 10,
            'uniqueId'   => 'some-unique-id',
            'options' => [
                \Aerospike::OPT_CONNECT_TIMEOUT => 1250,
                \Aerospike::OPT_WRITE_TIMEOUT => 1500
            ]
        ];
    }

    public function testShouldReadAndWriteSession()
    {
        $sessionId = 'abcdef123458';
        $session = new SessionHandler($this->getConfig());

        $data = serialize(
            [
                321   => microtime(true),
                'def' => '678',
                'xyz' => 'zyx'
            ]
        );

        $session->write($sessionId, $data);
        $expected = $session->read($sessionId);

        $this->assertEquals($data, $expected);
    }

    public function testShouldDestroySession()
    {
        $sessionId = 'abcdef123457';
        $session = new SessionHandler($this->getConfig());

        $data = serialize(
            [
                'abc' => 345,
                'def' => ['foo' => 'bar'],
                'zyx' => 'xyz'
            ]
        );

        $session->write($sessionId, $data);
        $session->destroy($sessionId);

        $this->assertFalse($session->read($sessionId));
    }
}
