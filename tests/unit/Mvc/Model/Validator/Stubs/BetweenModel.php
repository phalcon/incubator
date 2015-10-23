<?php

namespace Phalcon\Test\Mvc\Model\Validator\Stubs;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Validator\Between;

class BetweenModel extends Model
{
    public $position;
    public $min;
    public $max;
    public $message;
    public $allowEmpty;

    public function validation()
    {
        $params = [
            'field' => 'position'
        ];

        if ($this->min) {
            $params['min'] = $this->min;
        }

        if ($this->max) {
            $params['max'] = $this->max;
        }

        if ($this->message) {
            $params['message'] = $this->message;
        }

        if ($this->allowEmpty) {
            $params['allowEmpty'] = $this->allowEmpty;
        }

        $this->validate(new Between($params));

        return $this->validationHasFailed() != true;
    }

}
