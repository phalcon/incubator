<?php
namespace Phalcon\Mvc\Model\Validator;

use Phalcon\Mvc\Model\Validator;
use Phalcon\Mvc\Model\ValidatorInterface;
use Phalcon\Mvc\ModelInterface;

/**
 * Phalcon\Mvc\Model\Validator\ConfirmationOf
 * Allows to validate if a field has a confirmation field with the same value
 * Don't forget to add confirmation field to be skipped on create and update
 */
class ConfirmationOf extends Validator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     *
     * @param  \Phalcon\Mvc\ModelInterface $record
     * @return boolean
     */
    public function validate(ModelInterface $record)
    {
        $field = $this->getOption('field');
        $fieldConfirmation = $this->getOption('field_confirmation');

        $fieldValue = $record->readAttribute($field);
        $fieldConfirmationValue = $record->readAttribute($fieldConfirmation);

        $message = $this->getOption('message')
            ? $this->getOption('message')
            : 'Both fields should contain equal values';

        if ($fieldConfirmationValue) {
            if ($fieldValue !== $fieldConfirmationValue) {
                $this->appendMessage($message, $fieldConfirmation, 'ConfirmationOf');

                return false;
            }
        }

        return true;
    }
}
