<?php

namespace Phalcon\Test\Mvc\Model\Validator;

use Phalcon\Mvc\Model\Manager;
use Phalcon\Di;

class BetweenTest extends \PHPUnit_Framework_TestCase
{
    public function dataBetween()
    {
        return [
            [1, 10, 3, true],
            [1, 10, 4, true],
            [1, 10, -1, false],
            [1, 10, 0, false],
            [-1, 1, 0, true],
        ];
    }

    /**
     * @dataProvider dataBetween
     */
    public function testValidate($min, $max, $position, $willReturn)
    {
        $di = new Di();
        $di->set('modelsManager', new Manager());

        require_once(__DIR__ . '/resources/TestBetweenModel.php');

        $obj = new \TestBetweenModel();

        $obj->position = $position;
        $obj->min = $min;
        $obj->max = $max;
        $this->assertEquals($willReturn, $obj->validation());
    }


    public function testValidateInstanceOf()
    {
        require_once(__DIR__ . '/resources/TestBetweenModel.php');
        $obj = new \TestBetweenModel();

        $this->assertInstanceOf('Phalcon\Mvc\ModelInterface', $obj);
    }

    /**
     * @expectedException           \Phalcon\Mvc\Model\Exception
     * @expectedExceptionMessage    Field name must be a string
     */
    public function testValidateIncorrectFieldType()
    {
        $di = new Di();
        $di->set('modelsManager', new Manager());

        require_once(__DIR__ . '/resources/TestBetweenIncorrectField.php');

        $obj = new \TestBetweenIncorrectField();

        $obj->validation();
    }

    public function testValidateIsEmpty()
    {
        $di = new Di();
        $di->set('modelsManager', new Manager());

        require_once(__DIR__ . '/resources/TestBetweenModel.php');

        $obj = new \TestBetweenModel();

        $obj->min = 1;
        $obj->max = 3;
        $this->assertEquals(false, $obj->validation());
    }

    public function testValidateIsEmptyWithFlag()
    {
        $di = new Di();
        $di->set('modelsManager', new Manager());

        require_once(__DIR__ . '/resources/TestBetweenModel.php');

        $obj = new \TestBetweenModel();

        $obj->min = 1;
        $obj->max = 3;
        $obj->allowEmpty = true;
        $this->assertEquals(true, $obj->validation());
    }

    /**
     * @expectedException           \Phalcon\Mvc\Model\Exception
     * @expectedExceptionMessage    A minimum and maximum must be set
     */
    public function testValidateWithoutMin()
    {
        $di = new Di();
        $di->set('modelsManager', new Manager());

        require_once(__DIR__ . '/resources/TestBetweenModel.php');

        $obj = new \TestBetweenModel();

        $obj->max = 3;
        $obj->validation();
    }

    /**
     * @expectedException           \Phalcon\Mvc\Model\Exception
     * @expectedExceptionMessage    A minimum and maximum must be set
     */
    public function testValidateWithoutMaxAndMin()
    {
        $di = new Di();
        $di->set('modelsManager', new Manager());

        require_once(__DIR__ . '/resources/TestBetweenModel.php');

        $obj = new \TestBetweenModel();

        $obj->validation();
    }

    /**
     * @expectedException           \Phalcon\Mvc\Model\Exception
     * @expectedExceptionMessage    A minimum and maximum must be set
     */
    public function testValidateWithoutMax()
    {
        $di = new Di();
        $di->set('modelsManager', new Manager());

        require_once(__DIR__ . '/resources/TestBetweenModel.php');

        $obj = new \TestBetweenModel();

        $obj->min = 1;

        $obj->validation();
    }

    public function testValidateDefaultMessage()
    {
        $di = new Di();
        $di->set('modelsManager', new Manager());

        require_once(__DIR__ . '/resources/TestBetweenModel.php');

        $obj = new \TestBetweenModel();

        $obj->min = 1;
        $obj->max = 2;
        $obj->position = 3;

        $obj->validation();
        $messages = $obj->getMessages();
        $this->assertEquals(
            $messages[0]->getMessage(),
            'position is not between a valid range'
        );
    }

    public function testValidateCustomMessage()
    {
        $di = new Di();
        $di->set('modelsManager', new Manager());

        require_once(__DIR__ . '/resources/TestBetweenModel.php');

        $obj = new \TestBetweenModel();

        $obj->min = 1;
        $obj->max = 2;
        $obj->position = 3;
        $obj->message = 'test 123';

        $obj->validation();
        $messages = $obj->getMessages();
        $this->assertEquals(
            $messages[0]->getMessage(),
            'test 123'
        );
    }
}
