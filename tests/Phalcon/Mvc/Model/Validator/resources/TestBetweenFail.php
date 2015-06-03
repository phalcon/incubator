<?php

class TestBetweenFail
{

    public $position;
    public $min;
    public $max;
    public $message;

    public function validation()
    {
        $params = array(
            'field' => 'position'
        );

        if ($this->min) {
            $params['min'] = $this->min;
        }
        if ($this->min) {
            $params['max'] = $this->max;
        }
        if ($this->message) {
            $params['message'] = $this->message;
        }
        $this->validate(
            new \Phalcon\Mvc\Model\Validator\Between(
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