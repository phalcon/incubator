<?php

class TestCardNumberCollection extends \Phalcon\Mvc\Collection
{

    public $cardnumber;
    public $type;

    public function validation()
    {
        $params = array(
            'field' => 'cardnumber'
        );
        if ($this->type) {
            $params['type'] = $this->type;
        }
        $this->validate(
            new \Phalcon\Mvc\Model\Validator\CardNumber(
                $params
            )
        );

        return $this->validationHasFailed() != true;
    }

}