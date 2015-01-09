
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

Decimal
--------------
Allows to validate if a field has a valid number in proper decimal format (negative and decimal numbers allowed).
Optionally, a specific number of digits can be checked too. Uses [locale conversion](http://www.php.net/manual/en/function.localeconv.php) to allow decimal point to be locale specific.

```php
use Phalcon\Mvc\Model\Validator\Decimal;

use Phalcon\Mvc\Model;

class Product extends Model
{

     public function validation()
     {
         $this->validate(new Decimal(array(
              'field' => 'price',
              'places' => 2,
              'digit' => 3, // optional
              'point' => ',' // optional. uses to override system decimal point
              'message' => 'Price must contain valid decimal value',
         )));

         if ($this->validationHasFailed() == true) {
              return false;
         }
     }

}

```
