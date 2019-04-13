<?php

namespace Phalcon\Validation\Validator;

use Phalcon\Validation\Message;
use Phalcon\Validation\Validator;
use Phalcon\Validation\ValidatorInterface;

class ArrayInclusionIn extends Validator implements ValidatorInterface
{

    /**
     * Executes the validation
     *
     * @param \Phalcon\Validation $validation
     * @param string $attribute
     * @return bool
     *
     * @throws \Exception
     */
    public function validate(\Phalcon\Validation $validation, $attribute)
    {
        $array = $validation->getValue($attribute);
        $domain = $this->getOption('domain');
        $allowEmpty = $this->getOption('allowEmpty');

        if ((empty($array) && !$allowEmpty) || empty($domain) || !is_array($array)) {
            $validation->appendMessage(
                new Message(
                    'Invalid argument supplied',
                    $attribute
                )
            );

            return false;
        }

        foreach ($array as $item) {
            if (!in_array($item, $domain)) {
                $message = $this->getOption(
                    'message',
                    'Values provided not exist in domain'
                );

                $validation->appendMessage(
                    new Message(
                        $message,
                        $attribute
                    )
                );

                return false;
            }
        }

        return true;
    }
}
