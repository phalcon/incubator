<?php

namespace Phalcon\Validation\Validator;

use Phalcon\Validation;
use Phalcon\Validation\Message;
use Phalcon\Validation\Validator;
use Phalcon\Validation\ValidatorInterface;

class NumericValidator extends Validator implements ValidatorInterface
{
    /**
     * Executes the validation. Allowed options:
     * 'allowFloat' : allow . and , characters;
     * 'min' : input value must not be lower than it;
     * 'max' : input value must not be higher than it.
     *
     * @param  Validation $validator
     * @param  string $attribute
     *
     * @return boolean
     */
    public function validate(\Phalcon\Validation $validator, $attribute)
    {
        $value = $validator->getValue($attribute);

        $allowFloat = (bool)$this->getOption('allowFloat');
        $allowFloat = $allowFloat ? '.,' : '';

        $allowSign = (bool)$this->getOption('allowSign');
        $allowSign = $allowSign ? '[-+]?' : '';
        $allowSignMessage = $allowSign ? 'signed' : 'unsigned';

        if ($allowFloat) {
            if (!preg_match('/^(^' . $allowSign . '[0-9]*\.?[0-9]+)+$/u', (string)$value)) {

                $message = $this->getOption('message',
                    'The value must be a valid ' . $allowSignMessage . ' floating number');

                $validator->appendMessage(new Message($message, $attribute, 'Numeric'));
            }
        } else {
            if (!preg_match('/^(' . $allowSign . '[0-9])+$/u', $value)) {

                $message = $this->getOption('message',
                    'The value must be a valid ' . $allowSignMessage . ' integer number');

                $validator->appendMessage(new Message($message, $attribute, 'Numeric'));
            }
        }

        if ($min = (int)$this->getOption('min')) {
            if ($value < $min) {
                $messageMin = $this->getOption('messageMinimum',
                    'The value must be at least ' . $min);

                $validator->appendMessage(new Message($messageMin, $attribute, 'Numeric'));
            }
        }

        if ($max = (int)$this->getOption('max')) {
            if ($value > $max) {
                $messageMax = $this->getOption('messageMaximum',
                    'The value must be lower than ' . $max);

                $validator->appendMessage(new Message($messageMax, $attribute, 'Numeric'));
            }
        }

        if (count($validator)) {
            return false;
        }

        return true;
    }
}
