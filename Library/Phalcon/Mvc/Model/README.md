Eager loading usage
-----

The usage is similar to Laravel, in PHP >=5.4 within a model that uses `Phalcon\Mvc\Model\EagerLoadingTrait` trait you can do the following:

```php
<?php
use Phalcon\Mvc\Model\EagerLoading\Loader,
	Phalcon\Mvc\Model\EagerLoading\QueryBuilder;

$robotsAndParts = Robot::with('Parts');

// Equivalent to:

$robots = Robot::find();
foreach ($robots as $robot) {
	$robot->parts; // $robot->__get('parts')
}

// Or

$robot = Robot::findFirst()->load('Parts');

// Equivalent to:

$robot = Robot::findFirst();
$robots->parts; // $robot->__get('parts')

// Because Robot::find() returns a resultset, so in that case this is solved with:
$robots = Loader::fromResultset(Robot::find(), 'Parts'); # Equivalent to the second example

// Multiple and nested relations can be used too
$robots = Robot::with('Parts', 'Foo.Bar');

// And arguments can be passed to the find method
$robots = Robot::with('Parts', 'Foo.Bar', ['limit' => 5]);

// And constraints
$robots = Robot::with(
	[
		'Parts',
		'Foo.Bar' => function (QueryBuilder $builder) {
			// Limit Bar
			$builder->limit(5);
		}
	],
	[
		'limit' => 5
	]
);

```

Distributed as single package at https://github.com/stibiumz/phalcon.eager-loading
