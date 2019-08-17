<?php

namespace Phalcon\Test\Validation\Validator;

use Phalcon\Validation;
use Phalcon\Test\Codeception\UnitTestCase;
use Phalcon\Validation\Validator\AlphaCompleteValidator;

/**
 * \Phalcon\Test\Validation\Validator\AlphaCompleteValidatorTest
 * Tests for Phalcon\Validation\Validator\AlphaCompleteValidator component
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
class AlphaCompleteValidatorTest extends UnitTestCase
{

    public function testAlphaCompleteValidatorOk()
    {
        $data['text'] = "0123456789 abc ñ () [] ' \" _ !? .,:;";

        $validation = new Validation();

        $validation->add(
            'text',
            new AlphaCompleteValidator(
                [
                    'min'            => 5,                                               // Optional
                    'max'            => 100,                                             // Optional
                    'message'        => 'Validation failed.',                            // Optional
                    'messageMinimum' => 'The value must contain at least 5 characters.', // Optional
                    'messageMaximum' => 'The value can contain maximum 100 characters.', // Optional
                ]
            )
        );

        $messages = $validation->validate($data);

        $this->assertCount(
            0,
            $messages
        );
    }

    public function testAlphaCompleteValidatorOkWithPipe()
    {
        $data['text'] = "0123456789 abc ñ () [] ' \" _ !? .,:;|";

        $validation = new Validation();

        $validation->add(
            'text',
            new AlphaCompleteValidator(
                [
                    'allowPipes'     => true,                                            // Optional
                    'min'            => 5,                                               // Optional
                    'max'            => 100,                                             // Optional
                    'message'        => 'Validation failed.',                            // Optional
                    'messageMinimum' => 'The value must contain at least 5 characters.', // Optional
                    'messageMaximum' => 'The value can contain maximum 100 characters.', // Optional
                ]
            )
        );

        $messages = $validation->validate($data);

        $this->assertCount(
            0,
            $messages
        );
    }

    public function testAlphaCompleteValidatorOkWithBackslackesh()
    {
        $data['text'] = "0123456789 abc ñ () [] ' \" _ !? .,:;" . '\_';

        $validation = new Validation();

        $validation->add(
            'text',
            new AlphaCompleteValidator(
                [
                    'allowBackslashes' => true,                                            // Optional
                    'min'              => 5,                                               // Optional
                    'max'              => 100,                                             // Optional
                    'message'          => 'Validation failed.',                            // Optional
                    'messageMinimum'   => 'The value must contain at least 5 characters.', // Optional
                    'messageMaximum'   => 'The value can contain maximum 100 characters.', // Optional
                ]
            )
        );

        $messages = $validation->validate($data);

        $this->assertCount(
            0,
            $messages
        );
    }

    public function testAlphaCompleteValidatorOkWithUrlChars()
    {
        $data['text'] = "0123456789 abc ñ () [] ' \" _ !? .,:;| =?";

        $validation = new Validation();

        $validation->add(
            'text',
            new AlphaCompleteValidator(
                [
                    'allowPipes'     => true,                                            // Optional
                    'allowUrlChars'  => true,                                            // Optional
                    'min'            => 5,                                               // Optional
                    'max'            => 100,                                             // Optional
                    'message'        => 'Validation failed.',                            // Optional
                    'messageMinimum' => 'The value must contain at least 5 characters.', // Optional
                    'messageMaximum' => 'The value can contain maximum 100 characters.', // Optional
                ]
            )
        );

        $messages = $validation->validate($data);

        $this->assertCount(
            0,
            $messages
        );
    }

    public function testAlphaCompleteValidatorFailingSymbols()
    {
        $data['text'] = "0123456789 abc ñ () [] ' \" _ !? .,:; <";

        $validation = new Validation();

        $validation->add(
            'text',
            new AlphaCompleteValidator(
                [
                    'min'            => 5,                                               // Optional
                    'max'            => 100,                                             // Optional
                    'message'        => 'Validation failed.',                            // Optional
                    'messageMinimum' => 'The value must contain at least 5 characters.', // Optional
                    'messageMaximum' => 'The value can contain maximum 100 characters.'  // Optional
                ]
            )
        );

        $messages = $validation->validate($data);

        $this->assertCount(
            1,
            $messages
        );
    }

    public function testAlphaCompleteValidatorFailingUrlCharsEquals()
    {
        $data['text'] = "0123456789 =";

        $validation = new Validation();

        $validation->add(
            'text',
            new AlphaCompleteValidator(
                [
                    'min'            => 5,                                               // Optional
                    'max'            => 100,                                             // Optional
                    'message'        => 'Validation failed.',                            // Optional
                    'messageMinimum' => 'The value must contain at least 5 characters.', // Optional
                    'messageMaximum' => 'The value can contain maximum 100 characters.', // Optional
                ]
            )
        );

        $messages = $validation->validate($data);

        $this->assertCount(
            1,
            $messages
        );
    }

    public function testAlphaCompleteValidatorFailingUrlCharsHashtag()
    {
        $data['text'] = "0123456789 #";

        $validation = new Validation();

        $validation->add(
            'text',
            new AlphaCompleteValidator(
                [
                    'min'            => 5,                                               // Optional
                    'max'            => 100,                                             // Optional
                    'message'        => 'Validation failed.',                            // Optional
                    'messageMinimum' => 'The value must contain at least 5 characters.', // Optional
                    'messageMaximum' => 'The value can contain maximum 100 characters.', // Optional
                ]
            )
        );

        $messages = $validation->validate($data);

        $this->assertCount(
            1,
            $messages
        );
    }

    public function testAlphaCompleteValidatorFailingPipe()
    {
        $data['text'] = "0123456789 abc ñ () [] ' \" _ !? .,:;|";

        $validation = new Validation();

        $validation->add(
            'text',
            new AlphaCompleteValidator(
                [
                    'min'            => 5,                                               // Optional
                    'max'            => 100,                                             // Optional
                    'message'        => 'Validation failed.',                            // Optional
                    'messageMinimum' => 'The value must contain at least 5 characters.', // Optional
                    'messageMaximum' => 'The value can contain maximum 100 characters.', // Optional
                ]
            )
        );

        $messages = $validation->validate($data);

        $this->assertCount(
            1,
            $messages
        );
    }

    public function testAlphaCompleteValidatorFailingLength()
    {
        $data['text'] = "0123456789 abc ñ () [] ' \" _ !? .,:; ";

        $validation = new Validation();

        $validation->add(
            'text',
            new AlphaCompleteValidator(
                [
                    'min'            => 5,                                               // Optional
                    'max'            => 10,                                              // Optional
                    'message'        => 'Validation failed.',                            // Optional
                    'messageMinimum' => 'The value must contain at least 5 characters.', // Optional
                    'messageMaximum' => 'The value can contain maximum 10 characters.',  // Optional
                ]
            )
        );

        $messages = $validation->validate($data);

        $this->assertCount(
            1,
            $messages
        );
    }
}
