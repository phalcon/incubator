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
  | Authors: Grigory Parshikov <root@parshikov.github.io>                  |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Validation\Validator;

use Phalcon\Validation;
use Phalcon\Validation\Message;
use Phalcon\Validation\Validator;
use Phalcon\Validation\Exception;

/**
 * Phalcon\Mvc\Model\Validator\CardNumber
 *
 * Checks if a credit card number using Luhn algorithm
 *
 * <code>
 * use Phalcon\Validation\Validator\CardNumber as CreditCardValidator;
 *
 * $validator->add('creditcard', new CreditCardValidator([
 *     'message' => 'The credit card number is not valid',
 *     'type'    => CardNumber::VISA, // Any if not specified
 * ]));
 * </code>
 */
class CardNumber extends Validator
{
    const AMERICAN_EXPRESS  = 0; // 34, 37
    const MASTERCARD        = 1; // 51-55
    const VISA              = 2; // 4

    /**
     * {@inheritdoc}
     *
     * @param Validation $validation
     * @param string $attribute
     *
     * @return bool
     * @throws Exception
     */
    public function validate(Validation $validation, $attribute)
    {
        $value = preg_replace('/[^\d]/', '', $validation->getValue($attribute));
        $message = ($this->hasOption('message')) ? $this->getOption('message') : 'Credit card number is invalid';

        if ($this->hasOption('type')) {
            $type = $this->getOption('type');

            switch ($type) {
                case CardNumber::AMERICAN_EXPRESS:
                    $issuer = substr($value, 0, 2);
                    $result = (true === in_array($issuer, [34, 37]));
                    break;
                case CardNumber::MASTERCARD:
                    $issuer = substr($value, 0, 2);
                    $result = (true === in_array($issuer, [51, 52, 53, 54, 55]));
                    break;
                case CardNumber::VISA:
                    $issuer = $value[0];
                    $result = ($issuer == 4);
                    break;
                default:
                    throw new Exception('Incorrect type specifier');
            }

            if (false === $result) {
                $validation->appendMessage(new Message($message, $attribute, 'CardNumber'));
                return false;
            }
        }

        $value = strrev($value);
        $checkSum = 0;

        for ($i = 0; $i < strlen($value); $i++) {
            if (($i % 2) == 0) {
                $temp = $value[$i];
            } else {
                $temp = $value[$i] * 2;
                if ($temp > 9) {
                    $temp -= 9;
                }
            }
            $checkSum += $temp;
        }

        if (($checkSum % 10) != 0) {
            $validation->appendMessage(new Message($message, $attribute, 'CardNumber'));
            return false;
        }

        return true;
    }
}
