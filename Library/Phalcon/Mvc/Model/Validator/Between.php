<?php
namespace Phalcon\Mvc\Model\Validator;

use Phalcon\Mvc\EntityInterface;
use Phalcon\Mvc\Model\Validator;
use Phalcon\Mvc\Model\ValidatorInterface;
use Phalcon\Mvc\Model\Exception;

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
 *    public function validation()
 *    {
 *        $this->validate(new Between([
 *            'field'   => 'position',
 *            'max'     => 50,
 *            'min'     => 2,
 *            'message' => 'Position is not between a valid range',
 *        ]));
 *
 *        if ($this->validationHasFailed() == true) {
 *            return false;
 *        }
 *    }
 *
 *}
 *</code>
 */
class Between extends Validator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     *
     * <strong>NOTE:</strong>
     * for Phalcon < 2.0.4 replace
     * <code>\Phalcon\Mvc\EntityInterface</code>
     * by
     * <code>\Phalcon\Mvc\ModelInterface</code>
     *
     * @param EntityInterface $record
     *
     * @return boolean
     * @throws Exception
     */
    public function validate(EntityInterface $record)
    {
        $field = $this->getOption('field');

        if (false === is_string($field)) {
            throw new Exception('Field name must be a string');
        }

        $value = $record->readAttribute($field);

        if (true === $this->isSetOption('allowEmpty') && empty($value)) {
            return true;
        }

        if (false === $this->isSetOption('min') || false === $this->isSetOption('max')) {
            throw new Exception('A minimum and maximum must be set');
        }

        $maximum = $this->getOption('max');
        $minimum = $this->getOption('min');

        if ($value < $minimum || $value > $maximum) {
            // Check if the developer has defined a custom message
            $message = $this->getOption('message') ?: sprintf('%s is not between a valid range', $field);

            $this->appendMessage($message, $field, 'Between');
            return false;
        }

        return true;
    }
}
