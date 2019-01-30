<?php

/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2016 Phalcon Team (http://www.phalconphp.com)       |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file docs/LICENSE.txt.                        |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: David Hubner <david.hubner@gmail.com>                         |
  +------------------------------------------------------------------------+
 */

namespace Phalcon\Test\Validation\Validator;

use Phalcon\Test\Codeception\UnitTestCase as Test;
use Codeception\Util\Stub;
use Phalcon\Validation\Validator\ConfirmationOf;

class ConfirmationOfTest extends Test
{
    public function testValidateExceptionWithoutOrigField()
    {
        $validation = Stub::make('Phalcon\Validation');
        $validator = new ConfirmationOf();
        $this->setExpectedException('Phalcon\Validation\Exception');
        $validator->validate($validation, 'confirmation');
    }

    public function testValidateSameAsOrig()
    {
        $validation = Stub::make('Phalcon\Validation', array('getValue' => 'value'));
        $validator = new ConfirmationOf(array(
            'origField' => 'original'
        ));
        $this->assertTrue($validator->validate($validation, 'confirmation'));
    }

    public function testValidateNotSameAsOrig()
    {
        $validation = Stub::make('Phalcon\Validation', array('getValue' => Stub::consecutive('val1', 'val2'), 'appendMessage' => true));
        $validator = new ConfirmationOf(array(
            'origField' => 'original'
        ));
        $this->assertFalse($validator->validate($validation, 'confirmation'));
    }

    public function testValidateAllowEmpty()
    {
        $validation = Stub::make('Phalcon\Validation', array('getValue' => Stub::consecutive('', 'val2')));
        $validator = new ConfirmationOf(array(
            'origField' => 'original',
            'allowEmpty' => true
        ));
        $this->assertTrue($validator->validate($validation, 'confirmation'));
    }

    public function testValidateNotAllowEmpty()
    {
        $validation = Stub::make('Phalcon\Validation', array('getValue' => Stub::consecutive('', 'val2'), 'appendMessage' => true));
        $validator = new ConfirmationOf(array(
            'origField' => 'original',
            'allowEmpty' => false
        ));
        $this->assertFalse($validator->validate($validation, 'confirmation'));
    }

    public function testValidateInvalidValue()
    {
        $validation = Stub::make('Phalcon\Validation', array('getValue' => array('value', 'value'), 'appendMessage' => true));
        $validator = new ConfirmationOf(array(
            'origField' => 'original'
        ));
        $this->assertFalse($validator->validate($validation, 'confirmation'));
    }
}
