<?php

class TestBetweenModel extends \Phalcon\Mvc\Model
{
    public $position;
    public $min;
    public $max;
    public $message;
    public $allowEmpty;

    public function validation()
    {
        $params = array(
            'field' => 'position'
        );

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
        $this->validate(
            new \Phalcon\Mvc\Model\Validator\Between(
                $params
            )
        );

        return $this->validationHasFailed() != true;
    }

}
