<?php

/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2017 Phalcon Team (https://www.phalconphp.com)      |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file LICENSE.txt.                             |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Sergii Svyrydenko <sergey.v.sviridenko@gmail.com>             |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Test\Validation\Validator;

use Phalcon\Test\Codeception\UnitTestCase as Test;
use Phalcon\Validation\Validator\Iban;
use Phalcon\Validation;

class IbanTest extends Test
{
    /**
     * Tests Iban::validate. When country code set after add to validation.
     *
     * @dataProvider providerValidCode
     * @param string $countryCode
     * @param string $code
     *
     * @test
     * @issue  809
     * @author Sergii Svyrydenko <sergey.v.sviridenko@gmail.com>
     * @since  2017-09-26
     */
    public function shouldValidateIbanCodeWithSetCountryCode($countryCode, $code)
    {
        $validation = new Validation();
        $validation->add(
            'test',
            new Iban()
        );

        $validators = $validation->getValidators();
        $validator = $validators[0];
        $validator = $validator[1];

        $validator->setCountryCode($countryCode);

        $messages = $validation->validate(['test' => $code]);

        $this->assertCount(
            0,
            $messages,
            'The Iban number isn\'t valid'
        );
    }

    /**
     * Tests Iban::validate. When country code didn't set ever.
     *
     * @dataProvider providerValidCode
     * @param string $countryCode
     * @param string $code
     *
     * @test
     * @issue  809
     * @author Sergii Svyrydenko <sergey.v.sviridenko@gmail.com>
     * @since  2017-09-26
     */
    public function shouldValidateIbanCodeWithoutCountryCode($countryCode, $code)
    {
        $validation = new Validation();
        $iban = new Iban();

        $validation->add(
            'test',
            $iban
        );

        $messages = $validation->validate(['test' => $code]);

        $this->assertCount(
            0,
            $messages,
            'The Iban number isn\'t valid'
        );
    }

    /**
     * Tests Iban::validate. When country code set in construct.
     *
     * @dataProvider providerValidCode
     * @param string $countryCode
     * @param string $code
     *
     * @test
     * @issue  809
     * @author Sergii Svyrydenko <sergey.v.sviridenko@gmail.com>
     * @since  2017-09-26
     */
    public function shouldValidateIbanCodeWithCountryCode($countryCode, $code)
    {
        $validation = new Validation();
        $validation->add(
            'test',
            new Iban([
                'country_code' => $countryCode,
            ])
        );

        $messages = $validation->validate(['test' => $code]);

        $this->assertCount(
            0,
            $messages,
            'The Iban number isn\'t valid'
        );
    }

    /**
     * Tests Iban::validate. Generate error message.
     *
     * @dataProvider providerInvalidCode
     * @param string $countryCode
     * @param string $code
     * @param string $message
     * @param string $messageType
     *
     * @test
     * @issue  809
     * @author Sergii Svyrydenko <sergey.v.sviridenko@gmail.com>
     * @since  2017-09-26
     */
    public function shouldCatchErrorMessage($countryCode, $code, $message, $messageType)
    {
        $validation = new Validation();
        $iban = new Iban([
            'country_code'        => $countryCode,
            $messageType          => $message,
            'allow_non_sepa'      => false,
        ]);

        $validation->add(
            'test',
            $iban
        );

        $messages = $validation->validate(['test' => $code]);

        foreach ($messages as $messageReturn) {
            $this->assertEquals(
                $message,
                $messageReturn->getMessage(),
                'Method validate() should return error message'
            );
        }
    }

    public function providerValidCode()
    {
        $data = require INCUBATOR_FIXTURES . 'Validation/iban_data.php';

        return $data['iban-codes'];
    }

    public function providerInvalidCode()
    {
        $data = require INCUBATOR_FIXTURES . 'Validation/iban_data.php';

        return $data['iban-error-code'];
    }
}
