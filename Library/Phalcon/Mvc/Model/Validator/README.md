
Phalcon\Mvc\Model\Validator
===========================

Validators for Phalcon\Mvc\Model

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

CardNumber
-------
Validates credit card number using Luhn algorithm.

```php

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Validator\CardNumber;

class User extends Phalcon\Mvc\Model
{
     public function validation()
     {
          $this->validate(new CardNumber(array(
                'field' => 'cardnumber',
                'type'   => CardNumber::VISA, // Only one type. Any if not specified
                'message' => 'Card number must be valid',
          )));

          if ($this->validationHasFailed() == true) {
               return false;
          }
      }
}

```
