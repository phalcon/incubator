<?php

namespace Phalcon\Test\Validation\Validator;

use Phalcon\Validation;
use Phalcon\Test\Codeception\UnitTestCase as Test;

/**
 * \Phalcon\Test\Validation\Validator\NumericValidatorTest
 * Tests for Phalcon\Validation\Validator\NumericValidator component
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
class NumericValidatorTest extends Test
{
    public function testNumericValidatorOk()
    {
        $data['number'] = 1234567890;

        $validation = new Validation();

        $validation->add(
            'number',
            new \Phalcon\Validation\Validator\NumericValidator (
                [
                    'min' => 1,                                                     // Optional
                    'max' => 2000000000,                                            // Optional
                    'message' => 'Only numeric (0-9) characters are allowed.',      // Optional
                    'messageMinimum' => 'The value must be at least 1',             // Optional
                    'messageMaximum' => 'The value must be lower than 12345678900'  // Optional
                ]
            )
        );

        $messages = $validation->validate($data);
        $this->assertEquals(0, count($messages));
    }

    public function testNumericValidatorOkSign()
    {
        $data['number'] = -10;

        $validation = new Validation();

        $validation->add(
            'number',
            new \Phalcon\Validation\Validator\NumericValidator (
                [
                    'allowSign' => true,                                            // Optional, default false
                    'min' => -20,                                                   // Optional
                    'max' => 2000000000,                                            // Optional
                    'message' => 'Only numeric (0-9) characters are allowed.',      // Optional
                    'messageMinimum' => 'The value must be at least 1',             // Optional
                    'messageMaximum' => 'The value must be lower than 12345678900'  // Optional
                ]
            )
        );

        $messages = $validation->validate($data);
        $this->assertEquals(0, count($messages));
    }

    public function testNumericValidatorFailingSign()
    {
        $data['number'] = 1;

        $validation = new Validation();

        $validation->add(
            'number',
            new \Phalcon\Validation\Validator\NumericValidator (
                [
                    'min' => 2,                                                     // Optional
                    'max' => 10         ,                                           // Optional
                    'message' => 'Only numeric (0-9) characters are allowed.',      // Optional
                    'messageMinimum' => 'The value must be at least 2',             // Optional
                    'messageMaximum' => 'The value must be lower than 10'           // Optional
                ]
            )
        );

        $messages = $validation->validate($data);
        $this->assertEquals(1, count($messages));
    }

    public function testNumericValidatorFailingMax()
    {
        $data['number'] = 1234567890;

        $validation = new Validation();

        $validation->add(
            'number',
            new \Phalcon\Validation\Validator\NumericValidator (
                [
                    'min' => 2,                                                     // Optional
                    'max' => 10         ,                                           // Optional
                    'message' => 'Only numeric (0-9) characters are allowed.',      // Optional
                    'messageMinimum' => 'The value must be at least 2',             // Optional
                    'messageMaximum' => 'The value must be lower than 10'           // Optional
                ]
            )
        );

        $messages = $validation->validate($data);
        $this->assertEquals(1, count($messages));
    }

    public function testNumericValidatorFailingMin()
    {
        $data['number'] = 1;

        $validation = new Validation();

        $validation->add(
            'number',
            new \Phalcon\Validation\Validator\NumericValidator (
                [
                    'min' => 2,                                                     // Optional
                    'max' => 10         ,                                           // Optional
                    'message' => 'Only numeric (0-9) characters are allowed.',      // Optional
                    'messageMinimum' => 'The value must be at least 2',             // Optional
                    'messageMaximum' => 'The value must be lower than 10'           // Optional
                ]
            )
        );

        $messages = $validation->validate($data);
        $this->assertEquals(1, count($messages));
    }

    public function testNumericValidatorFailingComma()
    {
        $data['number'] = 5.3;

        $validation = new Validation();

        $validation->add(
            'number',
            new \Phalcon\Validation\Validator\NumericValidator (
                [
                    'min' => 2,                                                     // Optional
                    'max' => 10         ,                                           // Optional
                    'message' => 'Only numeric (0-9) characters are allowed.',      // Optional
                    'messageMinimum' => 'The value must be at least 2',             // Optional
                    'messageMaximum' => 'The value must be lower than 10'           // Optional
                ]
            )
        );

        $messages = $validation->validate($data);
        $this->assertEquals(1, count($messages));
    }

    public function testNumericValidatorFloatOk()
    {
        $data['number'] = 5.3;

        $validation = new Validation();

        $validation->add(
            'number',
            new \Phalcon\Validation\Validator\NumericValidator (
                [
                    'allowFloat' => true,                                           // Optional, default: false
                    'min' => 2,                                                     // Optional
                    'max' => 10         ,                                           // Optional
                    'message' => 'Only numeric (0-9) characters are allowed.',      // Optional
                    'messageMinimum' => 'The value must be at least 2',             // Optional
                    'messageMaximum' => 'The value must be lower than 10'           // Optional
                ]
            )
        );

        $messages = $validation->validate($data);
        $this->assertEquals(0, count($messages));
    }

    public function testNumericValidatorFloatOkSignPlus()
    {
        $data['number'] = +5.362;

        $validation = new Validation();

        $validation->add(
            'number',
            new \Phalcon\Validation\Validator\NumericValidator (
                [
                    'allowSign' => true,                                           // Optional, default: false
                    'allowFloat' => true,                                           // Optional, default: false
                    'max' => 10         ,                                           // Optional
                    'message' => 'Only numeric (0-9) characters are allowed.',      // Optional
                    'messageMinimum' => 'The value must be at least 2',             // Optional
                    'messageMaximum' => 'The value must be lower than 10'           // Optional
                ]
            )
        );

        $messages = $validation->validate($data);
        $this->assertEquals(0, count($messages));
    }

    public function testNumericValidatorFloatOkSignMenus()
    {
        $data['number'] = -5.3;

        $validation = new Validation();

        $validation->add(
            'number',
            new \Phalcon\Validation\Validator\NumericValidator (
                [
                    'allowSign' => true,                                           // Optional, default: false
                    'allowFloat' => true,                                           // Optional, default: false
                    'max' => 10         ,                                           // Optional
                    'message' => 'Only numeric (0-9) characters are allowed.',      // Optional
                    'messageMinimum' => 'The value must be at least 2',             // Optional
                    'messageMaximum' => 'The value must be lower than 10'           // Optional
                ]
            )
        );

        $messages = $validation->validate($data);
        $this->assertEquals(0, count($messages));
    }

    public function testNumericValidatorFloatFailing()
    {
        $data['number'] = '5.3.1';

        $validation = new Validation();

        $validation->add(
            'number',
            new \Phalcon\Validation\Validator\NumericValidator (
                [
                    'allowFloat' => true,                                           // Optional, default: false
                    'min' => 2,                                                     // Optional
                    'max' => 10         ,                                           // Optional
                    'message' => 'Only numeric (0-9) characters are allowed.',      // Optional
                    'messageMinimum' => 'The value must be at least 2',             // Optional
                    'messageMaximum' => 'The value must be lower than 10'           // Optional
                ]
            )
        );

        $messages = $validation->validate($data);
        $this->assertEquals(1, count($messages));
    }

    public function testNumericValidatorFloatFailingSign()
    {
        $data['number'] = '-5.3.1';

        $validation = new Validation();

        $validation->add(
            'number',
            new \Phalcon\Validation\Validator\NumericValidator (
                [
                    'allowFloat' => true,                                           // Optional, default: false
                    'min' => 2,                                                     // Optional
                    'max' => 10         ,                                           // Optional
                    'message' => 'Only numeric (0-9) characters are allowed.',      // Optional
                    'messageMinimum' => 'The value must be at least 2',             // Optional
                    'messageMaximum' => 'The value must be lower than 10'           // Optional
                ]
            )
        );

        $messages = $validation->validate($data);
        $this->assertEquals(2, count($messages));
    }
}
