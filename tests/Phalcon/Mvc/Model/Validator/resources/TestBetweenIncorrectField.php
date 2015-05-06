<?php


class TestBetweenIncorrectField extends \Phalcon\Mvc\Model
{
    public $position;
    public $min;
    public $max;
    public $message;

    public function validation()
    {
        $params = array(
            'field' => 1
        );

        $this->validate(
            new \Phalcon\Mvc\Model\Validator\Between(
                $params
            )
        );

        return $this->validationHasFailed() != true;
    }

}