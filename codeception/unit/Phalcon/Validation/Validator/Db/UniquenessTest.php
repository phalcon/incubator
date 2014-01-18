<?php
namespace Phalcon\Validation\Validator\Db;

use Codeception\Util\Stub;
use Phalcon\Db\Adapter\Pdo;
use Phalcon\Validation\Validator\Db\Uniqueness;

class UniquenessTest extends \Codeception\TestCase\Test
{
   /**
    * @var \CodeGuy
    */
    protected $codeGuy;

    private function getDbStub()
    {
        return Stub::makeEmpty(
            'Phalcon\Db\Adapter\Pdo',
            array(
                'fetchOne' => function ($sql, $fetchMode, $params) {
                    if ($sql == 'SELECT COUNT(*) as count FROM users WHERE login = ?') {
                        if ($params[0] == 'login_taken') {
                            return array('count' => 1);
                        } else {
                            return array('count' => 0);
                        }
                    }

                    return null;
                }
            )
        );
    }

    /**
     * @expectedException Phalcon\Validation\Exception
     */
    public function testConstructWithoutDbAndDefaultDI()
    {
        $uniquenessOptions = array(
            'table' => 'users',
            'column' => 'login',
        );
        $uniqueness = new Uniqueness($uniquenessOptions);
    }

    /**
     * @expectedException Phalcon\Validation\Exception
     */
    public function testConstructWithoutColumnOption()
    {
        $uniqueness = new Uniqueness(array('table' => 'users'), $this->getDbStub());
    }

    public function testAvailableUniquenessWithDefaultDI()
    {
        $di = new \Phalcon\DI();
        $di->set('db', $this->getDbStub());

        $uniquenessOptions = array(
            'table' => 'users',
            'column' => 'login',
        );
        $uniqueness = new Uniqueness($uniquenessOptions);

        $validation = new \Phalcon\Validation();
        $validation->add('login', $uniqueness);

        $messages = $validation->validate(array('login' => 'login_free'));
        $this->assertCount(0, $messages);
    }

    public function testAvailableUniqueness()
    {
        $uniquenessOptions = array(
            'table' => 'users',
            'column' => 'login',
        );
        $uniqueness = new Uniqueness($uniquenessOptions, $this->getDbStub());

        $validation = new \Phalcon\Validation();
        $validation->add('login', $uniqueness);

        $messages = $validation->validate(array('login' => 'login_free'));
        $this->assertCount(0, $messages);
    }

    public function testAlreadyTakenUniquenessWithDefaultMessage()
    {
        $uniquenessOptions = array(
            'table' => 'users',
            'column' => 'login',
        );
        $uniqueness = new Uniqueness($uniquenessOptions, $this->getDbStub());

        $validation = new \Phalcon\Validation();
        $validation->add('login', $uniqueness);
        $messages = $validation->validate(array('login' => 'login_taken'));

        $this->assertCount(1, $messages);
        $this->assertEquals('Already taken. Choose another!', $messages[0]);
    }

    public function testAlreadyTakenUniquenessWithCustomMessage()
    {
        $validation = new \Phalcon\Validation();
        $uniquenessOptions = array(
            'table' => 'users',
            'column' => 'login',
            'message' => 'Login already taken.'
        );
        $uniqueness = new Uniqueness($uniquenessOptions, $this->getDbStub());
        $validation->add('login', $uniqueness);
        $messages = $validation->validate(array('login' => 'login_taken'));

        $this->assertCount(1, $messages);
        $this->assertEquals('Login already taken.', $messages[0]);
    }
}
