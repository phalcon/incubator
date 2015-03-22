<?php

class TestCardNumberFail
{

    public $cardnumber;
    public $type;

    private $validations = array();

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


    protected function validate($validator)
    {
        $this->validations[] = $validator;
    }


    public function validationHasFailed()
    {
        $flag = true;

        foreach ($this->validations as $validator) {
            $flag = $validator->validate($this);
        }

        return $flag;
    }


}