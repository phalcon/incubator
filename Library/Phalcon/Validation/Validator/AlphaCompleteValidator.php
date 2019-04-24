<?php

namespace Phalcon\Validation\Validator;

use Phalcon\Validation;
use Phalcon\Validation\Message;
use Phalcon\Validation\Validator;
use Phalcon\Validation\ValidatorInterface;

class AlphaCompleteValidator extends Validator implements ValidatorInterface
{
    /**
     * Executes the validation. Allowed options:
     * 'min' : input value must not be shorter than it;
     * 'max' : input value must not be longer than it.
     *
     * @param  Validation $validator
     * @param  string $attribute
     *
     * @return boolean
     */
    public function validate(\Phalcon\Validation $validator, $attribute): bool
    {
        $value = $validator->getValue($attribute);

        $allowPipes = (bool)$this->getOption('allowPipes');
        $allowPipes = $allowPipes ? '|' : '';

        $allowBlackSlashes = (bool)$this->getOption('allowBackslashes');
        $allowBlackSlashes = $allowBlackSlashes ? '\\\\' : '';

        $allowUrlChars = (bool)$this->getOption('allowUrlChars');
        $allowUrlChars = $allowUrlChars ? '=#' : '';

        if (!preg_match('/^([-\p{L}*0-9_+!.,:\/;' . $allowPipes . $allowBlackSlashes . $allowUrlChars
                . '?&\(\)\[\]\{\}\'\"\s])+$/u', $value)) {
            $message = 'The value can contain only alphanumeric, underscore, white spaces, slashes, apostrophes, '
                . 'brackets, punctuation characters';

            if ($allowPipes) {
                $message .= ', pipes';
            }

            if ($allowBlackSlashes) {
                $message .= ', backslashes';
            }

            if ($allowUrlChars) {
                $message .= ', equals and hashtags';
            }

            $message = $this->getOption('message', $message);

            $validator->appendMessage(new Message($message, $attribute, 'AlphaComplete'));
        }

        if ($min = (int)$this->getOption('min')) {
            if (strlen($value) < $min) {
                $messageMin = $this->getOption(
                    'messageMinimum',
                    'The value must contain at least ' . $min . ' characters.'
                );

                $validator->appendMessage(new Message($messageMin, $attribute, 'AlphaComplete'));
            }
        }

        if ($max = (int)$this->getOption('max')) {
            if (strlen($value) > $max) {
                $messageMax = $this->getOption(
                    'messageMaximum',
                    'The value can contain maximum ' . $max . ' characters.'
                );

                $validator->appendMessage(new Message($messageMax, $attribute, 'AlphaComplete'));
            }
        }

        if (count($validator->getMessages())) {
            return false;
        }

        return true;
    }
}
