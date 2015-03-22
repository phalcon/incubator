<?php

namespace Phalcon\Mvc\Model\Validator;

use Phalcon\Mvc\Model\Manager;
use Phalcon\DI;

class BetweenTest extends \PHPUnit_Framework_TestCase
{
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


    /**
     * @expectedException           \Phalcon\Mvc\Model\Exception
     * @expectedExceptionMessage    Invalid parameter type.
     */
    public function testExceptionNotObject()
    {
        $obj = new Between(array('min' => 1, 'max' => 2, 'field' => 'position'));

        $obj->validate(1);
    }

    public function testValidateInstanceOf()
    {
        require_once(__DIR__ . '/resources/TestBetweenModel.php');
        $obj = new \TestBetweenModel();

        $this->assertInstanceOf('Phalcon\Mvc\ModelInterface', $obj);
    }

    /**
     * @expectedException           \Phalcon\Mvc\Model\Exception
     * @expectedExceptionMessage    Invalid parameter type.
     */
    public function testValidateOtherInstance()
    {
        require_once(__DIR__ . '/resources/TestBetweenFail.php');

        $obj = new \TestBetweenFail();

        $obj->min = 1;
        $obj->max = 3;
        $obj->position = 4;

        $this->assertNotInstanceOf('Phalcon\Mvc\ModelInterface', $obj);
        $obj->validation();
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
}
