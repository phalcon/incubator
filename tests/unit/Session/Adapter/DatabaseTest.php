<?php

namespace Phalcon\Test\Session\Adapter;

use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Session\Adapter\Database;
use Phalcon\Db;
use Phalcon\Db\Column;
use Phalcon\Session\AdapterInterface;
use Helper\Session\Dialect\DatabaseTrait;
use Phalcon\Test\Module\UnitTest;
use Helper\Session\Dialect\ModelSession;

/**
 * \Phalcon\Test\Session\Adapter
 * Tests for Phalcon\Session\Adapter components
 *
 * @copyright (c) 2011-2017 Phalcon Team
 * @link      https://www.phalconphp.com
 * @author    Sergii Svyrydenko <sergey.v.sviridenko@gmail.com>
 * @package   Phalcon\Test\Session\Adapter
 * @group     Db
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class DatabaseTest extends UnitTest
{
    use DatabaseTrait;

    /**
     * @var DbAdapter
     */
    protected $connection;

    /**
     * @var SessionAdapter
     */
    protected $session;

    /**
     * executed before each test
     */
    protected function _before()
    {
        $this->connection = new Mysql(
            [
                'host'     => env('TEST_DB_HOST', '127.0.0.1'),
                'username' => env('TEST_DB_USER', 'incubator'),
                'password' => env('TEST_DB_PASSWD', 'secret'),
                'dbname'   => env('TEST_DB_NAME', 'incubator'),
                'charset'  => env('TEST_DB_CHARSET', 'utf8'),
                'port'     => env('TEST_DB_PORT', 3306)
            ]
        );

        $this->session = new Database([
            'db' => $this->connection,
            'table' => 'sessions',
            'lifetime' => 3600
        ]);
    }

    /**
     * Tests Database::write
     *
     * @test
     * @author Sergii Svyrydenko <sergey.v.sviridenko@gmail.com>
     * @since  2017-07-24
     */
    public function shouldWorkSessionAdapter()
    {
        $this->session->start();
        $sessionID = $this->session->getId();
        $this->session->set('customer_id', 'somekey');
        $this->specify(
            "Method set() hasn't worked",
            function ($data, $expected) {
                expect($data)->equals($expected);
            },
            [
                'examples' => [
                    [$this->session->get('customer_id'), 'somekey']
                ]
            ]
        );
        
        session_start();
        $this->session->write($sessionID, session_encode());
        $this->tester->seeInDatabase(ModelSession::$table, ['session_id' => $sessionID]);
        $this->tester->seeInDatabase(ModelSession::$table, ['data' => 'customer_id|s:7:"somekey";']);
        $this->session->remove('customer_id');

        $sessionData = $this->session->read($sessionID);
        session_decode($sessionData);
        
        $this->specify(
            "Method read() hasn't worked",
            function ($data, $expected) {
                expect($data)->equals($expected);
            },
            [
                'examples' => [
                    [$this->session->get('customer_id'), 'somekey']
                ]
            ]
        );

        $this->session->set('customer_id', 'somekey');
        $this->session->set('customer_id2', 'somekey2');
        $this->session->write($sessionID, session_encode());
        $this->tester->seeInDatabase(ModelSession::$table, ['session_id' => $sessionID]);
        $this->tester->seeInDatabase(ModelSession::$table, ['data' => 'customer_id|s:7:"somekey";customer_id2|s:8:"somekey2";']);
        $this->session->remove('customer_id');
        $this->session->remove('customer_id2');

        $sessionData = $this->session->read($sessionID);
        session_start();
        session_decode($sessionData);
        
        $session = $this->session;
        $this->specify(
            "Method read() hasn't worked",
            function ($data, $expected) use ($session) {
                expect($data)->equals($expected);
            },
            [
                'examples' => [
                    [$this->session->get('customer_id'), 'somekey']
                ]
            ]
        );
        
        $this->specify(
            "Method update() hasn't worked",
            function ($data, $expected) {
                expect($data)->equals($expected);
            },
            [
                'examples' => [
                    [$this->session->get('customer_id'), 'somekey'],
                    [$this->session->get('customer_id2'), 'somekey2']
                ]
            ]
        );

        $this->connection->execute($this->getWrittenSessionData($sessionID));
        $this->tester->dontSeeInDatabase(ModelSession::$table, ['session_id' => $sessionID]);
    }
}
