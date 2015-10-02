<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Validator\Decimal;

class TestDecimalModel extends Model
{
    public $field;
    public $places;
    public $digits;
    public $point;
    public $message;
    public $allowEmpty;

    public function validation()
    {
        $params = ['field' => 'field'];

        if ($this->places) {
            $params['places'] = $this->places;
        }

        if ($this->digits) {
            $params['digits'] = $this->digits;
        }

        if ($this->point) {
            $params['point'] = $this->point;
        }

        if ($this->message) {
            $params['message'] = $this->message;
        }

        if ($this->allowEmpty) {
            $params['allowEmpty'] = $this->allowEmpty;
        }

        $this->validate(new Decimal($params));

        return $this->validationHasFailed() != true;
    }
}
