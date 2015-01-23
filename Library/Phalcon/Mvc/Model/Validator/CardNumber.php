<?php
namespace Phalcon\Mvc\Model\Validator;

use Phalcon\Mvc\Model\Exception;
use Phalcon\Mvc\ModelInterface;

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
    public function validate($record)
    {
        if (false === is_object($record) || false === $record instanceof ModelInterface) {
            throw new Exception('Invalid parameter type.');
        }

        $field = $this->getOption('field');

        if (false === is_string($field)) {
            throw new Exception('Field name must be a string');
        }

        $fieldValue = $record->readAttribute($field);

        $value = strrev(preg_replace('/[^\d]/', '', $fieldValue));
        $checkSum = 0;

        for ($i = 0; $i < strlen($value); $i++) {

            if (($i % 2) == 0) {
                $temp = $value[$i];
            } else {
                $vabufl = $value[$i] * 2;
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
