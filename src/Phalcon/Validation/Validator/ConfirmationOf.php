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

namespace Phalcon\Validation\Validator;

use Phalcon\Validation;
use Phalcon\Validation\AbstractValidator;
use Phalcon\Validation\Exception as ValidationException;

/**
 * Validates confirmation of other field value
 *
 * <code>
 * new \Phalcon\Validation\Validator\ConfirmationOf([
 *     'origField' => {string - original field attribute},
 *     'message' => {string - validation message},
 *     'allowEmpty' => {bool - allow empty value}
 * ])
 * </code>
 *
 * @package Phalcon\Validation\Validator
 */
class ConfirmationOf extends AbstractValidator
{

    /**
     * Value validation
     *
     * @param Validation $validation - validation object
     * @param   string $attribute - validated attribute
     * @return  bool
     * @throws  ValidationException
     */
    public function validate(Validation $validation, $attribute): bool
    {
        if (!$this->hasOption('origField')) {
            throw new ValidationException('Original field must be set');
        }

        $allowEmpty = $this->getOption('allowEmpty');
        $value = $validation->getValue($attribute);

        if ($allowEmpty && ((is_scalar($value) && (string) $value === '') || is_null($value))) {
            return true;
        }

        $origField = $this->getOption('origField');
        $origValue = $validation->getValue($origField);

        if (is_string($value) && $value == $origValue) {
            return true;
        }

        $message = ($this->hasOption('message') ? $this->getOption('message') : 'Value not confirmed');

        $validation->appendMessage(
            new Validation\Message(
                $message,
                $attribute,
                'ConfirmationOfValidator'
            )
        );

        return false;
    }
}
