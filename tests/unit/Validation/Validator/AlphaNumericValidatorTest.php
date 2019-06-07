<?php

namespace Phalcon\Test\Validation\Validator;

use Phalcon\Validation;
use Phalcon\Test\Codeception\UnitTestCase;
use Phalcon\Validation\Validator\AlphaNumericValidator;

/**
 * \Phalcon\Test\Validation\Validator\AlphaNumericValidatorTest
 * Tests for Phalcon\Validation\Validator\AlphaNumericValidator component
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
class AlphaNumericValidatorTest extends UnitTestCase
{
    public function testAlphaNumericValidatorOk()
    {
        $data['text'] = '0123456789 abcdefghijklmnopqrstuvz ñ _';

        $validation = new Validation();

        $validation->add(
            'text',
            new AlphaNumericValidator(
                [
                    'whiteSpace'     => true,                                            // Optional, default false
                    'underscore'     => true,                                            // Optional, default false
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

    public function testAlphaNumericValidatorFailingWhiteSpace()
    {
        $data['text'] = '0123456789 abcdefghijklmnopqrstuvz ñ _';

        $validation = new Validation();

        $validation->add(
            'text',
            new AlphaNumericValidator(
                [
                    'whiteSpace'     => false,                                           // Optional, default false
                    'underscore'     => true,                                            // Optional, default false
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

    public function testAlphaNumericValidatorFailingUnderscope()
    {
        $data['text'] = '0123456789 abcdefghijklmnopqrstuvz ñ _';

        $validation = new Validation();

        $validation->add(
            'text',
            new AlphaNumericValidator(
                [
                    'whiteSpace'     => true,                                            // Optional, default false
                    'underscore'     => false,                                           // Optional, default false
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

    public function testAlphaNumericValidatorFailingLengthAndUnderscore()
    {
        $data['text'] = '0123456789 abcdefghijklmnopqrstuvz ñ _';

        $validation = new Validation();

        $validation->add(
            'text',
            new AlphaNumericValidator(
                [
                    'whiteSpace'     => true,                                            // Optional, default false
                    'underscore'     => false,                                           // Optional, default false
                    'min'            => 5,                                               // Optional
                    'max'            => 10,                                              // Optional
                    'message'        => 'Validation failed.',                            // Optional
                    'messageMinimum' => 'The value must contain at least 5 characters.', // Optional
                    'messageMaximum' => 'The value can contain maximum 100 characters.', // Optional
                ]
            )
        );

        $messages = $validation->validate($data);

        $this->assertCount(
            2,
            $messages
        );
    }
}
