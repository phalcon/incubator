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
            )
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
        if ($type) {
            $obj->type = $type;
        }
        $this->assertEquals($willReturn, $obj->validation());
    }
}
