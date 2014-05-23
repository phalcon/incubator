Phalcon\Mvc\Model\Behavior
==========================

```php

class Products extends Phalcon\Mvc\Model
{

    public function initialize()
    {
        $this->keepSnapshots(true);
    }

}
```

```
CREATE TABLE audit (
    id integer primary key auto_increment,
    user_name varchar(32) not null,
    model_name varchar(32) not null,
    ipaddress char(15) not null,
    type char(1) not null, /* C=Create/U=Update */
    created_at datetime not null
);

CREATE TABLE audit_detail (
    id integer primary key auto_increment,
    audit_id integer not null,
    field_name varchar(32) not null,
    old_value varchar(32),
    new_value varchar(32) not null
)
```

DateTime
--------

DateTime behavior.
 Enables models to use instances of \DateTime objects for db datetime fields.
 Works by converting DateTime object on before save to its MySQL (only MySQL tested so far) datetime field representation
 and then restores it after save operation is executed.

 Expects array as constructor argument with field name(s) as key(s) and options array as value.
 Options available are:
 - $options['timezone'] (optional, timezone identifier string)
 - $options['className'] (optional, user-defined DateTime instance)

 Example usage:
 ```php
 class Products extends Phalcon\Mvc\Model
 {
     /**
      * @var \DateTime
      */
     protected $updatedAt;

     /**
      * @var \DateTime
      */
     protected $updatedAt;

     public function initialize()
     {
         $options = array(
             'timezone' => 'Europe/Belgrade',
         //    'className' => 'My\CustomDateTimeClass',
          );
          $this->addBehavior(
              new \Phalcon\Mvc\Model\Behavior\DateTime(array(
                  'createdAt' => $options,
                  'updatedAt' => $options
              ));
          );
     }
 }
 ```
