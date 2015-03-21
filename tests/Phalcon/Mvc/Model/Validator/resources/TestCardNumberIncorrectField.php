<?php

class TestCardNumberIncorrectField extends \Phalcon\Mvc\Model
{
    public function validation()
    {
        $params = array(
            'field' => 1
        );
        $this->validate(
            new \Phalcon\Mvc\Model\Validator\CardNumber(
                $params
            )
        );

        return $this->validationHasFailed() != true;
    }

}
