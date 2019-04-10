<?php

namespace Phalcon\Test\Validation\Validator;

use Phalcon\Validation;
use Phalcon\Test\Codeception\UnitTestCase as Test;
use Phalcon\Validation\Validator\CardNumber;

/**
 * \Phalcon\Test\Validation\Validator\CardNumberTest
 * Tests for Phalcon\Validation\Validator\CardNumber component
 *
 * @copyright (c) 2011-2016 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Ilya Gusev <mail@igusev.ru>
 * @package   Phalcon\Test\Mvc\Model\Validator
 * @group     Validation
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class CardNumberTest extends Test
{
    /**
     * @dataProvider providerCards
     * @param mixed $type
     * @param mixed $cardnumber
     * @param boolean $willReturn
     */
    public function testShouldValidateCardNumberForModel($type, $cardnumber, $willReturn)
    {
        $validation = new Validation();

        if ($type) {
            $validation->add(
                'creditcard',
                new CardNumber(
                    [
                        'type' => $type,
                    ]
                )
            );
        } else {
            $validation->add(
                'creditcard',
                new CardNumber()
            );
        }

        $messages = $validation->validate(
            [
                'creditcard' => $cardnumber,
            ]
        );

        $this->assertNotEquals(
            $willReturn,
            $messages->valid()
        );
    }

    public function providerCards()
    {
        return include INCUBATOR_FIXTURES . 'Validation/card_number.php';
    }
}
