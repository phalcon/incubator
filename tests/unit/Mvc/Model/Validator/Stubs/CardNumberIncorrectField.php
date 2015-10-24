<?php

namespace Phalcon\Test\Mvc\Model\Validator\Stubs;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Validator\CardNumber;

class CardNumberIncorrectField extends Model
{
    public function validation()
    {
        $params = ['field' => 1];
        $this->validate(new CardNumber($params));

        return $this->validationHasFailed() != true;
    }
}
