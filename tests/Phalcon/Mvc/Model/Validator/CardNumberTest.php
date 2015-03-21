<?php

namespace Phalcon\Mvc\Model\Validator;

use Phalcon\Mvc\Model\Manager;
use Phalcon\Mvc\Model\Metadata\Files;
use Phalcon\DI;
use Phalcon\Mvc\Model\Validator\resources\TestCardNumberModel;

class CardNumberTest extends \PHPUnit_Framework_TestCase
{

    public function dataCards()
    {
        return array(
            array(
                CardNumber::VISA,
                4929351569693804,
                true
            ),
            array(
                CardNumber::VISA,
                4485767360254767,
                true
            ),
            array(
                CardNumber::VISA,
                4830875747689951,
                true
            ),
            array(
                CardNumber::VISA,
                4916181688876351,
                true
            ),
            array(
                CardNumber::VISA,
                1539277838295521,
                false
            ),
            array(
                CardNumber::MASTERCARD,
                5489650355340390,
                true
            ),
            array(
                CardNumber::MASTERCARD,
                5252467588261052,
                true
            ),
            array(
                CardNumber::MASTERCARD,
                5320263975322138,
                true
            ),
            array(
                CardNumber::MASTERCARD,
                5177135503698847,
                true
            ),
            array(
                null,
                5177135503698847,
                true
            ),
            array(
                CardNumber::MASTERCARD,
                1270338206812535,
                false
            ),
            array(
                CardNumber::MASTERCARD,
                12,
                false
            ),
            array(
                CardNumber::MASTERCARD,
                null,
                false
            ),
            array(
                null,
                1270338206812535,
                false
            ),
            array(
                CardNumber::AMERICAN_EXPRESS,
                370676121989775,
                true
            ),
            array(
                CardNumber::AMERICAN_EXPRESS,
                340136100802926,
                true
            ),
            array(
                CardNumber::AMERICAN_EXPRESS,
                344922644454845,
                true
            ),
            array(
                CardNumber::AMERICAN_EXPRESS,
                370282036294748,
                true
            ),
            array(
                CardNumber::AMERICAN_EXPRESS,
                370676121989775,
                true
            ),

        );
    }

    /**
     * @dataProvider dataCards
     */
    public function testValidate($type, $cardnumber, $willReturn)
    {
        $di = New DI();
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
        $di = New DI();
        $di->set('modelsManager', new Manager());

        require_once(__DIR__ . '/resources/TestCardNumberModel.php');

        $obj = new \TestCardNumberModel();

        $this->assertInstanceOf('Phalcon\Mvc\ModelInterface', $obj);
        $this->assertNotInstanceOf('Phalcon\Mvc\CollectionInterface', $obj);
    }


    public function testValidateInstanceOfCollection()
    {
        $di = New DI();
        $di->set('collectionManager', function () {
            return new \Phalcon\Mvc\Collection\Manager();
        });

        require_once(__DIR__ . '/resources/TestCardNumberCollection.php');

        $obj = new \TestCardNumberCollection($di);

        $obj->type = CardNumber::MASTERCARD;
        $obj->cardnumber = 1270338206812535;

        $this->assertInstanceOf('Phalcon\Mvc\CollectionInterface', $obj);
        $this->assertNotInstanceOf('Phalcon\Mvc\ModelInterface', $obj);
        $obj->validation();
    }

    /**
     * @expectedException           \Phalcon\Mvc\Model\Exception
     * @expectedExceptionMessage    Invalid parameter type.
     */
    public function testValidateOtherInstance()
    {
        require_once(__DIR__ . '/resources/TestCardNumberFail.php');

        $obj = new \TestCardNumberFail();

        $obj->type = CardNumber::MASTERCARD;
        $obj->cardnumber = 1270338206812535;

        $this->assertNotInstanceOf('Phalcon\Mvc\CollectionInterface', $obj);
        $this->assertNotInstanceOf('Phalcon\Mvc\ModelInterface', $obj);
        $obj->validation();
    }

    /**
     * @expectedException           \Phalcon\Mvc\Model\Exception
     * @expectedExceptionMessage    Incorrect type specifier
     */
    public function testValidateIncorrectType()
    {
        $di = New DI();
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
        $di = New DI();
        $di->set('modelsManager', new Manager());

        require_once(__DIR__ . '/resources/TestCardNumberIncorrectField.php');

        $obj = new \TestCardNumberIncorrectField();

        $obj->validation();
    }
}
