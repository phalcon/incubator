<?php

/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2016 Phalcon Team (https://www.phalconphp.com)      |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file LICENSE.txt.                             |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: David Hubner <david.hubner@gmail.com>                         |
  +------------------------------------------------------------------------+
 */

namespace Phalcon\Test\Validation\Validator;

use Phalcon\Validation;
use Codeception\TestCase\Test;
use Phalcon\Validation\Validator\PasswordStrength;

class PasswordStrengthTest extends Test
{
    public function testValidateWeakOnDefaultScore()
    {
        $validation = $this->getValidationMock();

        $validation->expects($this->any())
                   ->method('getValue')
                   ->willReturn('Weak1');

        $validator = new PasswordStrength();
        $this->assertTrue($validator->validate($validation, 'password'));
    }

    public function testValidateVeryWeakOnDefaultScore()
    {
        $validation = $this->getValidationMock();

        $validation->expects($this->any())
                   ->method('getValue')
                   ->willReturn('12345');

        $validation->expects($this->any())
                   ->method('appendMessage')
                   ->willReturn(true);

        $validator = new PasswordStrength();
        $this->assertFalse($validator->validate($validation, 'password'));
    }

    public function testValidateMediumOnScore3()
    {
        $validation = $this->getValidationMock();

        $validation->expects($this->any())
                   ->method('getValue')
                   ->willReturn('Medium99');

        $validator = new PasswordStrength(['minScore' => 3]);
        $this->assertTrue($validator->validate($validation, 'password'));
    }

    public function testValidateWeakOnScore3()
    {
        $validation = $this->getValidationMock();

        $validation->expects($this->any())
                   ->method('getValue')
                   ->willReturn('Weak1');

        $validation->expects($this->any())
                   ->method('appendMessage')
                   ->willReturn(true);

        $validator = new PasswordStrength(['minScore' => 3]);
        $this->assertFalse($validator->validate($validation, 'password'));
    }

    public function testValidateAllowEmpty()
    {
        $validation = $this->getValidationMock();

        $validation->expects($this->any())
                   ->method('getValue')
                   ->willReturn('');

        $validator = new PasswordStrength(['allowEmpty' => true]);
        $this->assertTrue($validator->validate($validation, 'password'));
    }

    public function testValidateNotAllowEmpty()
    {
        $validation = $this->getValidationMock();

        $validation->expects($this->any())
                   ->method('getValue')
                   ->willReturn('');

        $validation->expects($this->any())
                   ->method('appendMessage')
                   ->willReturn(true);

        $validator = new PasswordStrength(['allowEmpty' => false]);
        $this->assertFalse($validator->validate($validation, 'password'));
    }

    public function testValidateInvalidValue()
    {
        $validation = $this->getValidationMock();

        $validation->expects($this->any())
                   ->method('getValue')
                   ->willReturn(['value', 'value']);

        $validation->expects($this->any())
                   ->method('appendMessage')
                   ->willReturn(true);

        $validator = new PasswordStrength();
        $this->assertFalse($validator->validate($validation, 'password'));
    }

    public function testValidateMediumOnScore4()
    {
        $validation = $this->getValidationMock();

        $validation->expects($this->any())
                   ->method('getValue')
                   ->willReturn('Medium99');

        $validation->expects($this->any())
                   ->method('appendMessage')
                   ->willReturn(true);

        $validator = new PasswordStrength(['minScore' => 4]);
        $this->assertFalse($validator->validate($validation, 'password'));
    }

    public function testValidateStrongOnScore4()
    {
        $validation = $this->getValidationMock();

        $validation->expects($this->any())
                   ->method('getValue')
                   ->willReturn('Strong-9');

        $validator = new PasswordStrength(['minScore' => 4]);
        $this->assertTrue($validator->validate($validation, 'password'));
    }

    protected function getValidationMock()
    {
        return $this->getMockBuilder(Validation::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
