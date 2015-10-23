<?php

namespace Phalcon\Test\Mvc\Model\Validator\Stubs;

use Phalcon\Mvc\Collection;
use Phalcon\Mvc\Model\Validator\CardNumber;

class CardNumberCollection extends Collection
{
    public $cardnumber;
    public $type;

    public function validation()
    {
        $params = ['field' => 'cardnumber'];

        if ($this->type) {
            $params['type'] = $this->type;
        }

        $this->validate(new CardNumber($params));

        return $this->validationHasFailed() != true;
    }
}
