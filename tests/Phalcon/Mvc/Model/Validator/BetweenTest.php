<?php

namespace Phalcon\Test\Mvc\Model\Validator;

use Phalcon\Mvc\Model\Manager;
use Phalcon\Di;

class BetweenTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!class_exists('\Phalcon\Mvc\EntityInterface', false)) {
            $this->markTestSkipped('Current implementation of \Phalcon\Mvc\Model\Validator\Between is not compatible with Phalcon < 2.0.4');
        }
    }

    public function dataBetween()
    {
        return array(
            array(
                1,
                10,
                3,
                true
            ),
            array(
                1,
                10,
                4,
                true
            ),
            array(
                1,
                10,
                -1,
                false
            ),
            array(
                1,
                10,
                0,
                false
            ),
            array(
                -1,
                1,
                0,
                true
            ),
        );
    }

    /**
     * @dataProvider dataBetween
     */
    public function testValidate($min, $max, $position, $willReturn)
    {
        $di = New DI();
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
        $di = New DI();
        $di->set('modelsManager', new Manager());

        require_once(__DIR__ . '/resources/TestBetweenIncorrectField.php');

        $obj = new \TestBetweenIncorrectField();

        $obj->validation();
    }

    public function testValidateIsEmpty()
    {
        $di = New DI();
        $di->set('modelsManager', new Manager());

        require_once(__DIR__ . '/resources/TestBetweenModel.php');

        $obj = new \TestBetweenModel();

        $obj->min = 1;
        $obj->max = 3;
        $this->assertEquals(false, $obj->validation());
    }

    public function testValidateIsEmptyWithFlag()
    {
        $di = New DI();
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
        $di = New DI();
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
        $di = New DI();
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
        $di = New DI();
        $di->set('modelsManager', new Manager());

        require_once(__DIR__ . '/resources/TestBetweenModel.php');

        $obj = new \TestBetweenModel();

        $obj->min = 1;

        $obj->validation();
    }

    public function testValidateDefaultMessage()
    {
        $di = New DI();
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
        $di = New DI();
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
