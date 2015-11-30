<?php

namespace Phalcon\Test\Session\Adapter;

use Phalcon\Session\Adapter\Aerospike as SessionHandler;
use Codeception\Specify;

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
class AerospikeTest extends \PHPUnit_Framework_TestCase
{
    use Specify;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        if (!extension_loaded('aerospike')) {
            $this->markTestSkipped(
                'The aerospike module is not available.'
            );
        }

        parent::setUp();
    }

    public function testShouldReadAndWriteSession()
    {
        $this->specify(
            'The session cannot be read or written from',
            function () {
                $sessionId = 'abcdef123456';

                $session = new SessionHandler([
                    'hosts' => [
                        ['addr' => '127.0.0.1', 'port' => 3000]
                    ],
                    'persistent' => true,
                    'namespace'  => 'test',
                    'prefix'     => 'session_',
                    'lifetime'   => 8600,
                    'uniqueId'   => 'some-unique-id',
                    'options'    => [
                        \Aerospike::OPT_CONNECT_TIMEOUT => 1250,
                        \Aerospike::OPT_WRITE_TIMEOUT   => 1500
                    ]
                ]);

                $data = serialize(
                    [
                        'abc' => 123,
                        'def' => '678',
                        'xyz' => 'zyx'
                    ]
                );

                $session->write($sessionId, $data);
                expect($session->read($sessionId))->equals($data);
            }
        );
    }

    public function testShouldDestroySession()
    {
        $this->specify(
            'The session cannot be destroyed',
            function () {
                $sessionId = 'abcdef123456';

                $session = new SessionHandler([
                    'hosts' => [
                        ['addr' => '127.0.0.1', 'port' => 3000]
                    ],
                    'persistent' => true,
                    'namespace'  => 'test',
                    'prefix'     => 'session_',
                    'lifetime'   => 8600,
                    'uniqueId'   => 'some-unique-id',
                    'options'    => [
                        \Aerospike::OPT_CONNECT_TIMEOUT => 1250,
                        \Aerospike::OPT_WRITE_TIMEOUT   => 1500
                    ]
                ]);

                $data = serialize(
                    [
                        'abc' => 123,
                        'def' => '678',
                        'xyz' => 'zyx'
                    ]
                );

                $session->write($sessionId, $data);
                $session->destroy($sessionId);
                expect($session->read($sessionId))->equals(null);
            }
        );
    }
}
