<?php

namespace Phalcon\Test\Validation\Validator;

use Phalcon\Validation;
use Phalcon\Test\Codeception\UnitTestCase;
use Phalcon\Validation\Validator\AlphaNamesValidator;

/**
 * \Phalcon\Test\Validation\Validator\AlphaNamesValidatorTest
 * Tests for Phalcon\Validation\Validator\AlphaNamesValidator component
 *
 * @copyright (c) 2011-2017 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Michele Angioni <michele.angioni@gmail.com>
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
class AlphaNamesValidatorTest extends UnitTestCase
{
    public function testNamesValidatorOk()
    {
        $data['text'] = 'Richard Feynman';

        $validation = new Validation();

        $validation->add(
            'text',
            new AlphaNamesValidator (
                [
                    'numbers' => true,                                                   // Optional, default false
                    'min' => 5,                                                          // Optional
                    'max' => 100,                                                        // Optional
                    'message' => 'Validation failed.',                                   // Optional
                    'messageMinimum' => 'The value must contain at least 5 characters.', // Optional
                    'messageMaximum' => 'The value can contain maximum 100 characters.'  // Optional
                ]
            )
        );

        $messages = $validation->validate($data);
        $this->assertEquals(0, count($messages));
    }

    public function testNamesValidatorOkNumbers()
    {
        $data['text'] = 'R1ch4rd F3ynm4n';

        $validation = new Validation();

        $validation->add(
            'text',
            new AlphaNamesValidator (
                [
                    'numbers' => true,                                                   // Optional, default false
                    'min' => 5,                                                          // Optional
                    'max' => 100,                                                        // Optional
                    'message' => 'Validation failed.',                                   // Optional
                    'messageMinimum' => 'The value must contain at least 5 characters.', // Optional
                    'messageMaximum' => 'The value can contain maximum 100 characters.'  // Optional
                ]
            )
        );

        $messages = $validation->validate($data);
        $this->assertEquals(0, count($messages));
    }

    public function testNamesValidatorFailingNumbers()
    {
        $data['text'] = 'R1ch4rd F3ynm4n';

        $validation = new Validation();

        $validation->add(
            'text',
            new AlphaNamesValidator (
                [
                    'numbers' => false,                                                  // Optional, default false
                    'min' => 5,                                                          // Optional
                    'max' => 100,                                                        // Optional
                    'message' => 'Validation failed.',                                   // Optional
                    'messageMinimum' => 'The value must contain at least 5 characters.', // Optional
                    'messageMaximum' => 'The value can contain maximum 100 characters.'  // Optional
                ]
            )
        );

        $messages = $validation->validate($data);
        $this->assertEquals(1, count($messages));
    }

    public function testNamesValidatorFailingLengthAndNumbers()
    {
        $data['text'] = 'R1ch4rd F3ynm4n';

        $validation = new Validation();

        $validation->add(
            'text',
            new AlphaNamesValidator (
                [
                    'numbers' => false,                                                  // Optional, default false
                    'min' => 5,                                                          // Optional
                    'max' => 10,                                                         // Optional
                    'message' => 'Validation failed.',                                   // Optional
                    'messageMinimum' => 'The value must contain at least 5 characters.', // Optional
                    'messageMaximum' => 'The value can contain maximum 100 characters.'  // Optional
                ]
            )
        );

        $messages = $validation->validate($data);
        $this->assertEquals(2, count($messages));
    }

    public function testNamesValidatorFailingLengthAndBackslash()
    {
        $data['text'] = 'R1ch4rd F3ynm4n \!';

        $validation = new Validation();

        $validation->add(
            'text',
            new AlphaNamesValidator (
                [
                    'numbers' => true,                                                   // Optional, default false
                    'min' => 5,                                                          // Optional
                    'max' => 10,                                                         // Optional
                    'message' => 'Validation failed.',                                   // Optional
                    'messageMinimum' => 'The value must contain at least 5 characters.', // Optional
                    'messageMaximum' => 'The value can contain maximum 100 characters.'  // Optional
                ]
            )
        );

        $messages = $validation->validate($data);
        $this->assertEquals(2, count($messages));
    }

    public function testNamesValidatorFailingLenghtAndSymbols()
    {
        $data['text'] = 'R1ch4rd F3ynm4n !';

        $validation = new Validation();

        $validation->add(
            'text',
            new AlphaNamesValidator (
                [
                    'numbers' => true,                                                   // Optional, default false
                    'min' => 5,                                                          // Optional
                    'max' => 10,                                                         // Optional
                    'message' => 'Validation failed.',                                   // Optional
                    'messageMinimum' => 'The value must contain at least 5 characters.', // Optional
                    'messageMaximum' => 'The value can contain maximum 100 characters.'  // Optional
                ]
            )
        );

        $messages = $validation->validate($data);
        $this->assertEquals(2, count($messages));
    }
}
