
Phalcon\Mvc\Model\Behavior
==========================

Disclaimer
----------

This code has not been executed or tested. It is only supposed to
illustrate the behavior feature for models.

Description
-----------

Ability to attach behaviors to models without the need to define custom listeners
for common programming tasks. Currently supported:

* Timestampable - populates created and modified fields automatically

To be implemented:

* Sluggable - populates slug field based on the value of title field.
* Paranoid - lazy delete an entry by setting a deleted flag. Flag will be considered by all selects.

...

Defining a behavior
-------------------

The simple way, following the naming convention:

```php

class Robots extends \Phalcon\Mvc\Model\Behavior\ActAs
{

	protected $_actAs = array('timestampable');

	public function getSource()
	{
		return 'robots';
	}

}

```

Example with using options:

```php

class Robots extends \Phalcon\Mvc\Model\Behavior\ActAs
{

	protected $_actAs = array(
		'timestampable' => array(
			'createField' => 'created_at',
			'updateField' => 'updated_at',
		),
	);

	public function getSource()
	{
		return 'robots';
	}

}

```