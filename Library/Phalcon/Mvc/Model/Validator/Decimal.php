<?php
namespace Phalcon\Mvc\Model\Validator;

use Phalcon\Mvc\Model\Validator as ModelValidator;
use Phalcon\Mvc\Model\ValidatorInterface;
use Phalcon\Mvc\Model\Exception;
use Phalcon\Mvc\ModelInterface;

/**
 * Phalcon\Mvc\Model\Validator\Decimal
 *
 * Validates that a value is a valid number in proper decimal format (negative and decimal numbers allowed).
 * Optionally, a specific number of digits can be checked too.
 *
 * Uses {@link http://www.php.net/manual/en/function.localeconv.php locale conversion} to allow decimal point to be
 * locale specific.
 *
 *<code>
 *use Phalcon\Mvc\Model\Validator\Decimal;
 *
 *class Product extends Phalcon\Mvc\Model
 *{
 *
 *  public function validation()
 *  {
 *      $this->validate(new Decimal(array(
 *          'field' => 'price',
 *          'places' => 2,
 *          'digit' => 3, // optional
 *          'point' => ',' // optional. uses to override system decimal point
 *          'message' => 'Price must contain valid decimal value',
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
class Decimal extends ModelValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     *
     * @param $record
     * @return boolean
     * @throws Exception
     */
    public function validate(ModelInterface $record)
    {
        if (false === is_object($record) || false === $record instanceof ModelInterface) {
            throw new Exception('Invalid parameter type.');
        }

        $field = $this->getOption('field');

        if (false === is_string($field)) {
            throw new Exception('Field name must be a string');
        }

        $value = $record->readAttribute($field);

        if (true === $this->isSetOption('allowEmpty') && empty($value)) {
            return true;
        }

        if (false === $this->isSetOption('places')) {
            throw new Exception('A number of decimal places must be set');
        }

        $places = $this->getOption('places');

        if ($this->isSetOption('digits')) {
            // Specific number of digits
            $digits = '{'.( (int) $this->getOption('digits')).'}';
        } else {
            // Any number of digits
            $digits = '+';
        }

        if ($this->isSetOption('point')) {
            $decimal = $this->getOption('point');
        } else {
            // Get the decimal point for the current locale
            list($decimal) = array_values(localeconv());
        }

        $regexp = (bool) preg_match('#^[+-]?[0-9]'.$digits.preg_quote($decimal).'[0-9]{'.((int) $places).'}$#', $value);

        if (!$regexp) {
            // Check if the developer has defined a custom message
            $message = $this->getOption('message') ?: sprintf('%s must contain valid decimal value', $field);

            $this->appendMessage($message, $field, 'Decimal');
            return false;
        }

        return true;
    }
}
