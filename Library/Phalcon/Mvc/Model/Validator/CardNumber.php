<?php
namespace Phalcon\Mvc\Model\Validator;

use Phalcon\Mvc\Model\Exception;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Mvc\CollectionInterface;

/**
 * Phalcon\Mvc\Model\Validator\CardNumber
 *
 * Validates credit card number using Luhn algorithm
 *
 *<code>
 *use Phalcon\Mvc\Model\Validator\CardNumber;
 *
 *class User extends Phalcon\Mvc\Model
 *{
 *
 *  public function validation()
 *  {
 *      $this->validate(new CardNumber(array(
 *          'field' => 'cardnumber',
 *          'type'   => CardNumber::VISA, // Any if not specified
 *          'message' => 'Card number must be valid',
 *      )));
 *
 *      if ($this->validationHasFailed() == true) {
 *          return false;
 *      }
 *  }
 *
 *}
 *</code>
 */
class CardNumber extends \Phalcon\Mvc\Model\Validator
{
    const AMERICAN_EXPRESS  = 0; // 34, 37
    const MASTERCARD        = 1; // 51-55
    const VISA              = 2; // 4

    public function validate($record)
    {
        if (false === $record instanceof ModelInterface && false === $record instanceof CollectionInterface) {
            throw new Exception('Invalid parameter type.');
        }

        $field = $this->getOption('field');

        if (false === is_string($field)) {
            throw new Exception('Field name must be a string');
        }

        $fieldValue = $record->readAttribute($field);
        $value = preg_replace('/[^\d]/', '', $fieldValue);

        if ($this->isSetOption('type')) {
            $type = $this->getOption('type');

            $result = true;

            switch ($type) {
                case CardNumber::AMERICAN_EXPRESS:
                    $issuer = substr($value, 0, 2);
                    $result = (true === in_array($issuer, array(34, 37)));
                    break;
                case CardNumber::MASTERCARD:
                    $issuer = substr($value, 0, 2);
                    $result = (true === in_array($issuer, array(51, 52, 53, 54, 55)));
                    break;
                case CardNumber::VISA:
                    $issuer = $value[0];
                    $result = ($issuer == 4);
                    break;
                default:
                    throw new Exception('Incorrect type specifier');
            }

            if (false === $result) {
                $message = $this->getOption('message') ?: 'Credit card number is invalid';

                $this->appendMessage($message, $field, "CardNumber");
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
            $message = $this->getOption('message') ?: 'Credit card number is invalid';

            $this->appendMessage($message, $field, "CardNumber");
            return false;
        }
        return true;
    }
}
