<?php

namespace Phalcon\Test\Mvc\Model\Validator\Stubs;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Validator\Between;

class BetweenIncorrectField extends Model
{
    public $position;
    public $min;
    public $max;
    public $message;

    public function validation()
    {
        $params = ['field' => 1];

        $this->validate(new Between($params));

        return $this->validationHasFailed() != true;
    }
}
