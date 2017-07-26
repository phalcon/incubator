<?php

namespace Phalcon\Test\Validation\Validator;

use UnitTester;
use Phalcon\Validation;
use Codeception\TestCase\Test;
use Phalcon\Validation\Validator\CardNumber;

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
class AlphaCompleteValidatorTest extends Test
{
    /**
     * UnitTester Object
     * @var UnitTester
     */
    protected $tester;

    public function testAlphaCompleteValidatorOk()
    {
        $data['text'] = "0123456789 abc ñ () [] ' \" _ !? .,:;";

        $validation = new Validation();

        $validation->add(
            'text',
            new \Phalcon\Validation\Validator\AlphaCompleteValidator (
                [
                    'min' => 5,                                                                 // Optional
                    'max' => 100,                                                               // Optional
                    'message' => 'Validation failed.',                                          // Optional
                    'messageMinimum' => 'The value must contain at least 5 characters.',        // Optional
                    'messageMaximum' => 'The value can contain maximum 100 characters.'         // Optional
                ]
            )
        );

        $messages = $validation->validate($data);
        $this->assertEquals(0, count($messages));
    }

    public function testAlphaCompleteValidatorOkWithPipe()
    {
        $data['text'] = "0123456789 abc ñ () [] ' \" _ !? .,:;|";

        $validation = new Validation();

        $validation->add(
            'text',
            new \Phalcon\Validation\Validator\AlphaCompleteValidator (
                [
                    'allowPipes' => true,                                                       // Optional
                    'min' => 5,                                                                 // Optional
                    'max' => 100,                                                               // Optional
                    'message' => 'Validation failed.',                                          // Optional
                    'messageMinimum' => 'The value must contain at least 5 characters.',        // Optional
                    'messageMaximum' => 'The value can contain maximum 100 characters.'         // Optional
                ]
            )
        );

        $messages = $validation->validate($data);
        $this->assertEquals(0, count($messages));
    }

    public function testAlphaCompleteValidatorOkWithBackslackesh()
    {
        $data['text'] = "0123456789 abc ñ () [] ' \" _ !? .,:;" . '\_';

        $validation = new Validation();

        $validation->add(
            'text',
            new \Phalcon\Validation\Validator\AlphaCompleteValidator (
                [
                    'allowBackslashes' => true,                                                       // Optional
                    'min' => 5,                                                                 // Optional
                    'max' => 100,                                                               // Optional
                    'message' => 'Validation failed.',                                          // Optional
                    'messageMinimum' => 'The value must contain at least 5 characters.',        // Optional
                    'messageMaximum' => 'The value can contain maximum 100 characters.'         // Optional
                ]
            )
        );

        $messages = $validation->validate($data);
        $this->assertEquals(0, count($messages));
    }

    public function testAlphaCompleteValidatorOkWithUrlChars()
    {
        $data['text'] = "0123456789 abc ñ () [] ' \" _ !? .,:;| =?";

        $validation = new Validation();

        $validation->add(
            'text',
            new \Phalcon\Validation\Validator\AlphaCompleteValidator (
                [
                    'allowPipes' => true,                                                       // Optional
                    'allowUrlChars' => true,                                                       // Optional
                    'min' => 5,                                                                 // Optional
                    'max' => 100,                                                               // Optional
                    'message' => 'Validation failed.',                                          // Optional
                    'messageMinimum' => 'The value must contain at least 5 characters.',        // Optional
                    'messageMaximum' => 'The value can contain maximum 100 characters.'         // Optional
                ]
            )
        );

        $messages = $validation->validate($data);
        $this->assertEquals(0, count($messages));
    }

    public function testAlphaCompleteValidatorFailingSymbols()
    {
        $data['text'] = "0123456789 abc ñ () [] ' \" _ !? .,:; <";

        $validation = new Validation();

        $validation->add(
            'text',
            new \Phalcon\Validation\Validator\AlphaCompleteValidator (
                [
                    'min' => 5,                                                                 // Optional
                    'max' => 100,                                                               // Optional
                    'message' => 'Validation failed.',                                          // Optional
                    'messageMinimum' => 'The value must contain at least 5 characters.',        // Optional
                    'messageMaximum' => 'The value can contain maximum 100 characters.'         // Optional
                ]
            )
        );

        $messages = $validation->validate($data);
        $this->assertEquals(1, count($messages));
    }

    public function testAlphaCompleteValidatorFailingUrlCharsEquals()
    {
        $data['text'] = "0123456789 =";

        $validation = new Validation();

        $validation->add(
            'text',
            new \Phalcon\Validation\Validator\AlphaCompleteValidator (
                [
                    'min' => 5,                                                                 // Optional
                    'max' => 100,                                                               // Optional
                    'message' => 'Validation failed.',                                          // Optional
                    'messageMinimum' => 'The value must contain at least 5 characters.',        // Optional
                    'messageMaximum' => 'The value can contain maximum 100 characters.'         // Optional
                ]
            )
        );

        $messages = $validation->validate($data);
        $this->assertEquals(1, count($messages));
    }

    public function testAlphaCompleteValidatorFailingUrlCharsHashtag()
    {
        $data['text'] = "0123456789 #";

        $validation = new Validation();

        $validation->add(
            'text',
            new \Phalcon\Validation\Validator\AlphaCompleteValidator (
                [
                    'min' => 5,                                                                 // Optional
                    'max' => 100,                                                               // Optional
                    'message' => 'Validation failed.',                                          // Optional
                    'messageMinimum' => 'The value must contain at least 5 characters.',        // Optional
                    'messageMaximum' => 'The value can contain maximum 100 characters.'         // Optional
                ]
            )
        );

        $messages = $validation->validate($data);
        $this->assertEquals(1, count($messages));
    }

    public function testAlphaCompleteValidatorFailingPipe()
    {
        $data['text'] = "0123456789 abc ñ () [] ' \" _ !? .,:;|";

        $validation = new Validation();

        $validation->add(
            'text',
            new \Phalcon\Validation\Validator\AlphaCompleteValidator (
                [
                    'min' => 5,                                                                 // Optional
                    'max' => 100,                                                               // Optional
                    'message' => 'Validation failed.',                                          // Optional
                    'messageMinimum' => 'The value must contain at least 5 characters.',        // Optional
                    'messageMaximum' => 'The value can contain maximum 100 characters.'         // Optional
                ]
            )
        );

        $messages = $validation->validate($data);
        $this->assertEquals(1, count($messages));
    }

    public function testAlphaCompleteValidatorFailingLength()
    {
        $data['text'] = "0123456789 abc ñ () [] ' \" _ !? .,:; ";

        $validation = new Validation();

        $validation->add(
            'text',
            new \Phalcon\Validation\Validator\AlphaCompleteValidator (
                [
                    'min' => 5,                                                                 // Optional
                    'max' => 10,                                                                // Optional
                    'message' => 'Validation failed.',                                          // Optional
                    'messageMinimum' => 'The value must contain at least 5 characters.',        // Optional
                    'messageMaximum' => 'The value can contain maximum 10 characters.'          // Optional
                ]
            )
        );

        $messages = $validation->validate($data);
        $this->assertEquals(1, count($messages));
    }
}
