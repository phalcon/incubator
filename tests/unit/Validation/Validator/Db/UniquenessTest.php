<?php

namespace Phalcon\Test\Validation\Validator\Db;

use Phalcon\Di;
use Phalcon\Validation;
use Codeception\Util\Stub;
use Phalcon\Validation\Validator\Db\Uniqueness;
use Codeception\TestCase\Test;
use UnitTester;

/**
 * \Phalcon\Test\Validation\Validator\Db\UniquenessTest
 * Tests for Phalcon\Validation\Validator\Db\Uniqueness component
 *
 * @copyright (c) 2011-2015 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Tomasz Ślązok <tomek@landingi.com>
 * @package   Phalcon\Test\Validation\Validator\Db
 * @group     DbValidation
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class UniquenessTest extends Test
{
    /**
     * UnitTester Object
     * @var UnitTester
     */
    protected $tester;

    /**
     * @var Validation
     */
    protected $validation;

    /**
     * executed before each test
     */
    protected function _before()
    {
        $this->validation = new Validation();
    }

    /**
     * executed after each test
     */
    protected function _after()
    {
    }

    private function getDbStub()
    {
        return Stub::makeEmpty(
            'Phalcon\Db\Adapter\Pdo',
            array(
                'fetchOne' => function ($sql, $fetchMode, $params) {
                    if ($sql == 'SELECT COUNT(*) as count FROM users WHERE login = ?') {
                        if ($params[0] == 'login_taken') {
                            return ['count' => 1];
                        } else {
                            return ['count' => 0];
                        }
                    }

                    return null;
                }
            )
        );
    }

    /**
     * @expectedException        \Phalcon\Validation\Exception
     * @expectedExceptionMessage Validator Uniquness require connection to database
     */
    public function testShouldCatchExceptionWhenValidateUniqunessWithoutDbAndDefaultDI()
    {
        $uniquenessOptions = [
            'table' => 'users',
            'column' => 'login',
        ];

         new Uniqueness($uniquenessOptions);
    }

    /**
     * @expectedException        \Phalcon\Validation\Exception
     * @expectedExceptionMessage Validator require column option to be set
     */
    public function testShouldCatchExceptionWhenValidateUniqunessWithoutColumnOption()
    {
        new Uniqueness(['table' => 'users'], $this->getDbStub());
    }

    public function testAvailableUniquenessWithDefaultDI()
    {
        $di = new Di();
        $di->set('db', $this->getDbStub());

        $uniquenessOptions = [
            'table' => 'users',
            'column' => 'login',
        ];

        $uniqueness = new Uniqueness($uniquenessOptions);

        $this->validation->add('login', $uniqueness);

        $messages = $this->validation->validate(['login' => 'login_free']);
        $this->assertCount(0, $messages);
    }

    public function testShouldValidateAvailableUniqueness()
    {
        $uniquenessOptions = [
            'table' => 'users',
            'column' => 'login',
        ];

        $uniqueness = new Uniqueness($uniquenessOptions, $this->getDbStub());

        $this->validation->add('login', $uniqueness);

        $messages = $this->validation->validate(['login' => 'login_free']);
        $this->assertCount(0, $messages);
    }

    public function testAlreadyTakenUniquenessWithDefaultMessage()
    {
        $uniquenessOptions = [
            'table' => 'users',
            'column' => 'login',
        ];

        $uniqueness = new Uniqueness($uniquenessOptions, $this->getDbStub());

        $this->validation->add('login', $uniqueness);
        $messages = $this->validation->validate(['login' => 'login_taken']);

        $this->assertCount(1, $messages);
        $this->assertEquals('Already taken. Choose another!', $messages[0]);
    }

    public function testAlreadyTakenUniquenessWithCustomMessage()
    {
        $uniquenessOptions = [
            'table' => 'users',
            'column' => 'login',
            'message' => 'Login already taken.'
        ];

        $uniqueness = new Uniqueness($uniquenessOptions, $this->getDbStub());
        $this->validation->add('login', $uniqueness);
        $messages = $this->validation->validate(['login' => 'login_taken']);

        $this->assertCount(1, $messages);
        $this->assertEquals('Login already taken.', $messages[0]);
    }
}
