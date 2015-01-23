
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
-------
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

CardNumber
-------
Validates credit card number using Luhn algorithm

```php

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Validator\CardNumber;

class User extends Phalcon\Mvc\Model
{
     public function validation()
     {
          $this->validate(new CardNumber(array(
               'field' => 'cardnumber',
               'message' => 'Card number must be valid',
          )));
 
          if ($this->validationHasFailed() == true) {
               return false;
          }
      }
}

```

IPv4
-------
Validates that a value is ipv4 address in valid range

```php

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Validator\IPv4;

class Server extends Phalcon\Mvc\Model
{
     public function validation()
     {
          $this->validate(new IPv4(array(
               'field' => 'ipv4address',
               'message' => 'Incorrect ipv4 address',
          )));
 
          if ($this->validationHasFailed() == true) {
               return false;
          }
      }
}

```
