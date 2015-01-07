<?php
namespace Phalcon\Mvc\Model\Validator;

use Phalcon\Mvc\Model\Validator as ModelValidator,
    Phalcon\Mvc\Model\ValidatorInterface,
    Phalcon\Mvc\Model\Exception,
    Phalcon\Mvc\ModelInterface;

/**
 * Phalcon\Mvc\Model\Validator\Between
 *
 * Validates that a value is between a range of two values
 *
 *<code>
 *use Phalcon\Mvc\Model\Validator\Between;
 *
 *class Sliders extends Phalcon\Mvc\Model
 *{
 *
 *	public function validation()
 *	{
 *		$this->validate(new Between(array(
 *			'field' => 'position',
 *			'max' => 50,
 *			'min' => 2,
 *			'message' => 'Position is not between a valid range',
 *		)));
 *
 *		if ($this->validationHasFailed() == true) {
 *			return false;
 *		}
 *	}
 *
 *}
 *</code>
 */
class Between extends ModelValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     *
     * @param $record
     * @return boolean
     * @throws Exception
     */
    public function validate($record)
    {
        if (false === is_object($record) || false === $record instanceof ModelInterface) {
            throw new Exception('Invalid parameter type.');
        }

        $field = $this->getOption('field');

        if(false === is_string($field)) {
            throw new Exception('Field name must be a string');
        }

        if(false === $this->isSetOption('min') || false === $this->isSetOption('max')) {
            throw new Exception('A minimum and maximum must be set');
        }

        $maximum = $this->getOption('max');
        $minimum = $this->getOption('min');

        $value = $record->readAttribute($field);

        if ($value < $minimum || $value > $maximum) {
            // Check if the developer has defined a custom message
            $message = $this->getOption('message');
            if (false === isset($message)) {
                $message = sprintf('%s is not between a valid range', $field);
            }

            $this->appendMessage($message, $field, 'Between');
            return false;
        }

        return true;
    }
}
