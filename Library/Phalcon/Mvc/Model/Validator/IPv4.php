<?php
namespace Phalcon\Mvc\Model\Validator;

use Phalcon\Mvc\Model\Exception;

/**
 * Phalcon\Mvc\Model\Validator\CardNumber
 *
 * Validates that a value is ipv4 address in valid range
 *
 *<code>
 *use Phalcon\Mvc\Model\Validator\CardNumber;
 *
 *class Server extends Phalcon\Mvc\Model
 *{
 *
 *  public function validation()
 *  {
 *      $this->validate(new IPv4(array(
 *          'field' => 'ip4address',
 *          'message' => 'Incorrect ipv4 address',
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
class IPv4 extends \Phalcon\Mvc\Model\Validator
{
    public function validate($record)
    {
        if (false === is_object($record) || false === ($record instanceof Phalcon\Mvc\ModelInterface || $record instanceof Phalcon\Mvc\CollectionInterface)) {
            throw new Exception('Invalid parameter type.');
        }

        $field = $this->getOption('field');

        if (false === is_string($field)) {
            throw new Exception('Field name must be a string');
        }

        $fieldValue = $record->readAttribute($field);

        $result = preg_match('/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$/', $fieldValue, $matches);

        // Address contains not allowed symbols
        if (1 != $result) {
            $this->appendMessage(
                $this->getOption('message')
                ?: 'IPv4 is incorrect',
                $field, "IPv4");

            return false;
        }
        
        array_walk($matches, function (&$value, $index) {
            $value = (int) $value;
        });

        array_shift($matches);

        foreach ($matches as $key => $value) {
            // Rejects 0.0.0.0/8 addresses
            if (0 == $key && 0 == $value) {
                $this->appendMessage(
                    $this->getOption('message')
                    ?: 'IPv4 is incorrect',
                    $field, "IPv4"
                );
                
                return false;
            } else {
                // Rejects incorrect octets
                if (($value < 0 || $value > 255)) {
                    $this->appendMessage(
                        $this->getOption('message')
                        ?: 'IPv4 is incorrect',
                    $field, "IPv4");

                    return false;
                }
            }
        }
        return true;
    }
}
