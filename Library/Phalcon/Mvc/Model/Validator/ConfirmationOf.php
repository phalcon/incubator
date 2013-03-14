<?php

namespace Phalcon\Mvc\Model\Validator
{

    /**
     * Phalcon\Mvc\Model\Validator\ConfirmationOf
     *
     * Allows to validate if a field has a confirmation field with the same value
     *
     * Don't forget to add confirmation field to be skipped on create and update
     *
     *<code>
     *  use Phalcon\Mvc\Model\Validator\ConfirmationOf;
     *
     *  class User extends Phalcon\Mvc\Model
     *  {
     *
     *      public function initialize()
     *      {
     *          $this->skipAttributesOnCreate(array('password_confirmation'));
     *          $this->skipAttributesOnUpdate(array('password_confirmation'));
     *      }
     *
     *      public function validation()
     *      {
     *          $this->validate(new ConfirmationOf(array(
     *              'field' => 'password',
     *              'field_confirmation' => 'password_confirmation',
     *              'message' => 'Both fields should contain equal values'
     *          )));
     *
     *          if ($this->validationHasFailed() == true) {
     *              return false;
     *          }
     *      }
     *
     *  }
     *</code>
     *
     */
    class ConfirmationOf extends \Phalcon\Mvc\Model\Validator
    {

        /**
         * Executes the validator
         *
         * @param \Phalcon\Mvc\ModelInterface $record
         * @return boolean
         */
        public function validate($record)
        {
            $field = $this->getOption('field');
            $fieldConfirmation = $this->getOption('field_confirmation');

            $fieldValue = $record->readAttribute($field);
            $fieldConfirmationValue = $record->readAttribute($fieldConfirmation);

            $message = $this->getOption('message') ?: 'Both fields should contain equal values';

            if ($fieldConfirmationValue) {
                if ($fieldValue !== $fieldConfirmationValue) {
                    $this->appendMessage($message, $field, 'ConfirmationOf');
                    $this->appendMessage($message, $fieldConfirmation, 'ConfirmationOf');

                    return false;
                }
            }

            return true;
        }

    }

}