Phalcon\Mvc\Model\Behavior
==========================
NestedSet
--------------
```php
use Phalcon\Mvc\Model\Behavior\NestedSet as NestedSetBehavior;

class Categories extends \Phalcon\Mvc\Model
{

    public function initialize()
    {
        $this->addBehavior(new NestedSetBehavior(array(
            'leftAttribute' => 'lft',
            'rightAttribute' => 'rgt',
            'levelAttribute' => 'level'
        )));
    }

}
```

Blameable
--------------
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
