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
  | Authors: David Hubner <david.hubner@gmail.com>                             |
  +------------------------------------------------------------------------+
 */

namespace Phalcon\Test\Validation\Validator;

use Phalcon\Validation\Validator\PasswordStrength,
    Codeception\TestCase\Test,
    Codeception\Util\Stub;

class PasswordStrengthTest extends Test
{

    protected function _before()
    {
        
    }

    protected function _after()
    {
        
    }

    public function testValidateWeakOnDefaultScore()
    {
        $validation = Stub::make('Phalcon\Validation', array('getValue' => 'Weak1'));
        $validator = new PasswordStrength();
        $this->assertTrue($validator->validate($validation, 'password'));
    }

    public function testValidateVeryWeakOnDefaultScore()
    {
        $validation = Stub::make('Phalcon\Validation', array('getValue' => '12345', 'appendMessage' => true));
        $validator = new PasswordStrength();
        $this->assertFalse($validator->validate($validation, 'password'));
    }

    public function testValidateMediumOnScore3()
    {
        $validation = Stub::make('Phalcon\Validation', array('getValue' => 'Medium99'));
        $validator = new PasswordStrength(array(
            'minScore' => 3
        ));
        $this->assertTrue($validator->validate($validation, 'password'));
    }

    public function testValidateWeakOnScore3()
    {
        $validation = Stub::make('Phalcon\Validation', array('getValue' => 'Weak1', 'appendMessage' => true));
        $validator = new PasswordStrength(array(
            'minScore' => 3
        ));
        $this->assertFalse($validator->validate($validation, 'password'));
    }

    public function testValidateStrongOnScore4()
    {
        $validation = Stub::make('Phalcon\Validation', array('getValue' => 'Strong-9'));
        $validator = new PasswordStrength(array(
            'minScore' => 4
        ));
        $this->assertTrue($validator->validate($validation, 'password'));
    }

    public function testValidateMediumOnScore4()
    {
        $validation = Stub::make('Phalcon\Validation', array('getValue' => 'Medium99', 'appendMessage' => true));
        $validator = new PasswordStrength(array(
            'minScore' => 4
        ));
        $this->assertFalse($validator->validate($validation, 'password'));
    }

    public function testValidateAllowEmpty()
    {
        $validation = Stub::make('Phalcon\Validation', array('getValue' => ''));
        $validator = new PasswordStrength(array(
            'allowEmpty' => true
        ));
        $this->assertTrue($validator->validate($validation, 'password'));
    }

    public function testValidateNotAllowEmpty()
    {
        $validation = Stub::make('Phalcon\Validation', array('getValue' => '', 'appendMessage' => true));
        $validator = new PasswordStrength(array(
            'allowEmpty' => false
        ));
        $this->assertFalse($validator->validate($validation, 'password'));
    }

    public function testValidateInvalidValue()
    {
        $validation = Stub::make('Phalcon\Validation', array('getValue' => array('value', 'value'), 'appendMessage' => true));
        $validator = new PasswordStrength();
        $this->assertFalse($validator->validate($validation, 'password'));
    }

}
