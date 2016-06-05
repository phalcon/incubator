<?php

namespace Phalcon\Test\Mvc\Model\Validator\Stubs;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Validator\CardNumber;

class CardNumberModel extends Model
{
    public $cardnumber;
    public $type;

    public function validation()
    {
        $params = ['field' => 'cardnumber'];

        if ($this->type || $this->type === 0) {
            $params['type'] = $this->type;
        }

        $this->validate(new CardNumber($params));

        return $this->validationHasFailed() != true;
    }
}
