<?php

namespace Phalcon\Test\Mvc\Model\Validator;

use Phalcon\Mvc\Model\Validator\CardNumber;
use Phalcon\Mvc\Model\Manager;
use Phalcon\Di;

class CardNumberTest extends \PHPUnit_Framework_TestCase
{
    public function dataCards()
    {
        return [
            [
                CardNumber::VISA,
                4929351569693804,
                true
            ],
            [
                CardNumber::VISA,
                4485767360254767,
                true
            ],
            [
                CardNumber::VISA,
                4830875747689951,
                true
            ],
            [
                CardNumber::VISA,
                4916181688876351,
                true
            ],
            [
                CardNumber::VISA,
                1539277838295521,
                false
            ],
            [
                CardNumber::MASTERCARD,
                5489650355340390,
                true
            ],
            [
                CardNumber::MASTERCARD,
                5252467588261052,
                true
            ],
            [
                CardNumber::MASTERCARD,
                5320263975322138,
                true
            ],
            [
                CardNumber::MASTERCARD,
                5177135503698847,
                true
            ],
            [
                null,
                5177135503698847,
                true
            ],
            [
                CardNumber::MASTERCARD,
                1270338206812535,
                false
            ],
            [
                CardNumber::MASTERCARD,
                12,
                false
            ],
            [
                CardNumber::MASTERCARD,
                null,
                false
            ],
            [
                null,
                1270338206812535,
                false
            ],
            [
                CardNumber::AMERICAN_EXPRESS,
                370676121989775,
                true
            ],
            [
                CardNumber::AMERICAN_EXPRESS,
                340136100802926,
                true
            ],
            [
                CardNumber::AMERICAN_EXPRESS,
                344922644454845,
                true
            ],
            [
                CardNumber::AMERICAN_EXPRESS,
                370282036294748,
                true
            ],
            [
                CardNumber::AMERICAN_EXPRESS,
                370676121989775,
                true
            ],
        ];
    }

    /**
     * @dataProvider dataCards
     */
    public function testValidate($type, $cardnumber, $willReturn)
    {
        $di = new Di();
        $di->set('modelsManager', new Manager());

        require_once(__DIR__ . '/resources/TestCardNumberModel.php');

        $obj = new \TestCardNumberModel();

        $obj->cardnumber = $cardnumber;
        if ($type || $type === 0) {
            $obj->type = $type;
        }
        $this->assertEquals($willReturn, $obj->validation());
    }

    public function testValidateInstanceOfModel()
    {
        $di = new Di();
        $di->set('modelsManager', new Manager());

        require_once(__DIR__ . '/resources/TestCardNumberModel.php');

        $obj = new \TestCardNumberModel();

        $this->assertInstanceOf('Phalcon\Mvc\ModelInterface', $obj);
    }


    /**
     * @expectedException           \Phalcon\Mvc\Model\Exception
     * @expectedExceptionMessage    Incorrect type specifier
     */
    public function testValidateIncorrectType()
    {
        $di = new Di();
        $di->set('modelsManager', new Manager());

        require_once(__DIR__ . '/resources/TestCardNumberModel.php');

        $obj = new \TestCardNumberModel();

        $obj->type = 500;
        $obj->cardnumber = 1270338206812535;

        $obj->validation();
    }

    /**
     * @expectedException           \Phalcon\Mvc\Model\Exception
     * @expectedExceptionMessage    Field name must be a string
     */
    public function testValidateIncorrectFieldType()
    {
        $di = new Di();
        $di->set('modelsManager', new Manager());

        require_once(__DIR__ . '/resources/TestCardNumberIncorrectField.php');

        $obj = new \TestCardNumberIncorrectField();

        $obj->validation();
    }
}
