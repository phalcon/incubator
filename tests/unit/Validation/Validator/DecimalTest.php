<?php

namespace Phalcon\Test\Mvc\Model\Validator;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Decimal;
use Phalcon\Test\Codeception\UnitTestCase as Test;

/**
 * \Phalcon\Test\Validation\Validator\DecimalTest
 * Tests for Phalcon\Validation\Validator\Decimal component
 *
 * @copyright (c) 2011-2016 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Nikita Vershinin <endeveit@gmail.com>
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
class DecimalTest extends Test
{
    /**
     * @expectedException        \Phalcon\Validation\Exception
     * @expectedExceptionMessage A number of decimal places must be set
     */
    public function testShouldCatchExceptionWhenMissedPlacesInDecimalValidation()
    {
        $validation = new Validation();

        $validation->add(
            'number',
            new Decimal(
                [
                    'digit'   => 3,
                    'point'   => ',',
                    'message' => 'Price must contain valid decimal value',
                ]
            )
        );

        $validation->validate(
            [
                'number' => '1233.22',
            ]
        );
    }

    public function testShouldValidateUsingPlacesInDecimalValidation()
    {
        $validation = new Validation();

        $validation->add(
            'number',
            new Decimal(
                [
                    'places'  => 2,
                    'message' => 'Price must contain valid decimal value',
                ]
            )
        );

        $messages = $validation->validate(
            [
                'number' => '2.1',
            ]
        );

        $this->assertEquals(
            1,
            $messages->count()
        );

        $this->assertEquals(
            'Price must contain valid decimal value',
            $messages[0]->getMessage()
        );

        $this->assertEquals(
            'Decimal',
            $messages[0]->getType()
        );

        $messages = $validation->validate(
            [
                'number' => '8.67',
            ]
        );

        $this->assertEquals(
            0,
            $messages->count()
        );
    }

    public function testShouldValidateUsingDigitsInDecimalValidation()
    {
        $validation = new Validation();

        $validation->add(
            'number1',
            new Decimal(
                [
                    'places'  => 2,
                    'digits'  => 2,
                    'label'   => 'Magic number #1',
                    'message' => ':field must contain valid decimal value',
                ]
            )
        );

        $validation->add(
            'number2',
            new Decimal(
                [
                    'places'  => 2,
                    'digits'  => 1,
                    'label'   => 'Magic number #2',
                    'message' => ':field must contain valid decimal value',
                ]
            )
        );

        $validation->validate(
            [
                'number1' => '9.99',
                'number2' => '6.99',
            ]
        );

        $messages = $validation->getMessages();

        $this->assertEquals(
            1,
            $messages->count()
        );

        $this->assertEquals(
            'Magic number #1 must contain valid decimal value',
            $messages[0]->getMessage()
        );

        $this->assertEquals(
            'Decimal',
            $messages[0]->getType()
        );
    }
}
