
Phalcon\Mvc\Model\Validator
===========================

Validators for Phalcon\Mvc\Model

ConfirmationOf
--------------
Allows to validate if a field has a confirmation field with the same value

```php

use Phalcon\Mvc\Model\Validator\ConfirmationOf;

use Phalcon\Mvc\Model;

class User extends Model
{

     public function initialize()
     {
          $this->skipAttributesOnCreate(array('password_confirmation'));
          $this->skipAttributesOnUpdate(array('password_confirmation'));
     }

     public function validation()
     {
          $this->validate(new ConfirmationOf(array(
               'field' => 'password',
               'field_confirmation' => 'password_confirmation',
               'message' => 'Both fields should contain equal values'
          )));

          if ($this->validationHasFailed() == true) {
               return false;
          }
     }

}

```

Between
-------
Validates that a value is between a range of two values

```php

use Phalcon\Mvc\Model\Validator\Between;

use Phalcon\Mvc\Model;

class Slider extends Model
{
     public function validation()
     {
          $this->validate(new Between(array(
               'field' => 'position',
               'max' => 50,
               'min' => 2,
               'message' => 'Position is not between a valid range'
          )));

          if ($this->validationHasFailed() == true) {
               return false;
          }
     }
}
```
