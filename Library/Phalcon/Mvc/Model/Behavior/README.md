# Phalcon\Mvc\Model\Behavior

## NestedSet

### Installing and configuring

First you need to configure model as follows:
```php
use Phalcon\Mvc\Model\Behavior\NestedSet as NestedSetBehavior;

class Categories extends \Phalcon\Mvc\Model
{
    public function initialize()
    {
        $this->addBehavior(new NestedSetBehavior([
            'rootAttribute'  => 'root',
            'leftAttribute'  => 'lft',
            'rightAttribute' => 'rgt',
            'levelAttribute' => 'level'
        ]));
    }
}
```

There is no need to validate fields specified in `leftAttribute`, `rightAttribute`, `rootAttribute` and `levelAttribute` options.

By default:

* `leftAttribute` = `lft`
* `rightAttribute` = `rgt`
* `levelAttribute` = `level`
* `rootAttribute` = `root`

so you can skip configuring these.

There are two ways this behavior can work: one tree per table and multiple trees per table.
The mode is selected based on the value of `hasManyRoots` option that is `false` by default meaning single tree mode.
In multiple trees mode you can set `rootAttribute` option to match existing field in the table storing the tree.

### Selecting from a tree

In the following we'll use an example model Category with the following in its DB:
```
- 1. Mobile phones
    - 2. iPhone
    - 3. Samsung
        - 4. X100
        - 5. C200
    - 6. Motorola
- 7. Cars
    - 8. Audi
    - 9. Ford
    - 10. Mercedes
```
In this example we have two trees. Tree `roots` are ones with ID=1 and ID=7.

#### Getting all roots

```php
$roots = (new Categories())->roots();
```
Result: result set containing Mobile phones and Cars nodes.

You can also add the following method to the model if you use a single root:
```php
public static function getRoot()
{
    return self::findFirst('lft = 1');
}
```

Or the following method if you use multiple roots:
```php
public static function getRoots()
{
    return self::find('lft = 1');
}
```

#### Getting all descendants of a node

```php
$category = Categories::findFirst(1);
$descendants = $category->descendants();
```

Result: result set containing iPhone, Samsung, X100, C200 and Motorola.

#### Getting all children of a node

```php
$category = Categories::findFirst(1);
$children = $category->children();
```

Result: result set containing iPhone, Samsung and Motorola.

#### Getting all ancestors of a node

```php
$category = Categories::findFirst(5);
$ancestors = $category->ancestors();
```

Result: result set containing Samsung and Mobile phones.

#### Getting parent of a node

```php
$category = Categories::findFirst(9);
$parent = $category->parent();
```

Result: Cars node.

#### Getting node siblings

```php
$category = Categories::findFirst(9);
$nextSibling = $category->next();
$prevSibling = $category->prev();
```

Result: Mercedes node and Audi node.

#### Getting the whole tree

You can get the whole tree using standard model methods like the following.
For single tree per table:
```php
Categories::find(['order' => 'lft']);
```

For multiple trees per table:
```php
Categories::find(['root=:root:', 'order'=>'lft', 'bind' => ['root' => $root_id]]);
```

### Modifying a tree

In this section we'll build a tree like the one used in the previous section.

#### Creating root nodes

You can create a root node using `saveNode()`. In a single tree per table mode you can create only one root node.
If you'll attempt to create more there will be Exception thrown.

```php
$root = new Categories();
$root->title = 'Mobile Phones';
$root->saveNode();

$root = new Categories();
$root->title = 'Cars';
$root->saveNode();
```

Result:
```
- 1. Mobile Phones
- 2. Cars
```

#### Adding child nodes

There are multiple methods allowing you adding child nodes. Let's use these to add nodes to the tree we have:
```php
$category1 = new Categories();
$category1->title = 'Ford';

$category2 = new Categories();
$category2->title = 'Mercedes';

$category3 = new Categories();
$category3->title = 'Audi';

$root = Categories::findFirst(1);
$category1->appendTo($root);
$category2->insertAfter($category1);
$category3->insertBefore($category1);
```

Result:
```
- 1. Mobile phones
    - 3. Audi
    - 4. Ford
    - 5. Mercedes
- 2. Cars
```

Logically the tree above does not looks correct. We'll fix it later.
```php
$category1 = new Categories();
$category1->title = 'Samsung';

$category2 = new Categories();
$category2->title = 'Motorola';

$category3 = new Categories();
$category3->title = 'iPhone';

$root = Categories::findFirst(2);
$category1->appendTo($root);
$category2->insertAfter($category1);
$category3->prependTo($root);
```

Result:
```
- 1. Mobile phones
    - 3. Audi
    - 4. Ford
    - 5. Mercedes
- 2. Cars
    - 6. iPhone
    - 7. Samsung
    - 8. Motorola
```

```php
$category1 = new Categories();
$category1->title = 'X100';

$category2 = new Categories();
$category2->title = 'C200';

$node = Categories::findFirst(3);
$category1->appendTo($node);
$category2->prependTo($node);
```

Result:
```
- 1. Mobile phones
    - 3. Audi
        - 9. С200
        - 10. X100
    - 4. Ford
    - 5. Mercedes
- 2. Cars
    - 6. iPhone
    - 7. Samsung
    - 8. Motorola
```

### Modifying a tree

In this section we'll finally make our tree logical.

#### Tree modification methods

There are several methods allowing you to modify a tree.
Let's start:
```php
// move phones to the proper place
$x100 = Categories::findFirst(10);
$c200 = Categories::findFirst(9);

$samsung = Categories::findFirst(7);
$x100->moveAsFirst($samsung);
$c200->moveBefore($x100);

// now move all Samsung phones branch
$mobile_phones = Categories::findFirst(1);
$samsung->moveAsFirst($mobile_phones);

// move the rest of phone models
$iphone = Categories::findFirst(6);
$iphone->moveAsFirst($mobile_phones);

$motorola = Categories::findFirst(8);
$motorola->moveAfter($samsung);

// move car models to appropriate place
$cars = Categories::findFirst(2);
$audi = Categories::findFirst(3);
$ford = Categories::findFirst(4);
$mercedes = Categories::findFirst(5);

foreach([$audi,$ford,$mercedes] as $category) {
    $category->moveAsLast($cars);
}
```

Result:
```
- 1. Mobile phones
    - 6. iPhone
    - 7. Samsung
        - 10. X100
        - 9. С200
    - 8. Motorola
- 2. Cars
    - 3. Audi
    - 4. Ford
    - 5. Mercedes
```

#### Moving a node making it a new root
There is a special `moveAsRoot()` method that allows moving a node and making it a new root.
All descendants are moved as well in this case.

Example:
```php
$node = Categories::findFirst(10);
$node->moveAsRoot();
```

#### Identifying node type
There are three methods to get node type: `isRoot()`, `isLeaf()`, `isDescendantOf()`.

Example:
```php
$root = Categories::findFirst(1);
var_dump($root->isRoot()); // true;
var_dump($root->isLeaf()); // false;

$node = Categories::findFirst(9);
var_dump($node->isDescendantOf($root)); // true;
var_dump($node->isRoot()); // false;
var_dump($node->isLeaf()); // true;

$samsung = Categories::findFirst(7);
var_dump($node->isDescendantOf($samsung)); // true;
```
### Useful code

#### Non-recursive tree traversal

```php
$order = 'lft'; // or 'root, lft' for multiple trees
$categories = Categories::find(['order' => $order]);
$level = 0;

foreach ($categories as $n => $category) {
    if ($category->level == $level) {
        echo "</li>\n";
    } elseif ($category->level>$level) {
        echo "<ul>\n";
    } else {
        echo "</li>\n";

        for ($i = $level - $category->level; $i; $i--) {
            echo "</ul>\n";
            echo "</li>\n";
        }
    }

    echo "<li>\n";
    echo $category->title;
    $level = $category->level;
}

for ($i = $level; $i; $i--) {
    echo "</li>\n";
    echo "</ul>\n";
}
```

## Blameable

```php
class Products extends Phalcon\Mvc\Model
{
    public function initialize()
    {
        $this->keepSnapshots(true);
    }
}
```

```sql
CREATE TABLE `audit` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_name` VARCHAR(255) NOT NULL,
  `model_name` VARCHAR(255) NOT NULL,
  `ipaddress` CHAR(15) NOT NULL,
  `type` CHAR(1) NOT NULL, /* C=Create/U=Update */
  `created_at` DATETIME NOT NULL,
  `primary_key` TEXT DEFAULT NULL, /* for BC reasons */
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `audit_detail` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `audit_id` BIGINT NOT NULL,
  `field_name` VARCHAR(255) NOT NULL,
  `old_value` TEXT DEFAULT NULL,
  `new_value` TEXT NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

This is an example structure, please fit fields to suit your needs. By default incubator provides needed classes for Blameable behavior, however if you would 
want to use your own classes you can do it by implementing `AuditDetailInterface` and 
`AuditInterface` and setting them in constructor:

```php
public function initialize()
{
    $this->addBehavior(
        [
            'auditClass'       => MyAudit::class,
            'auditDetailClass' => MyAuditDetail::class
        ]
    );
}
```

Also by default `Audit` class will look for userName key in session for getting user name.
You can change this behavior by:

```php
public function initialize()
{
    $this->addBehavior(
        [
            'userCallback' => function(Phalcon\DiInterface $di) {
                // your custom code to return user name
            }
        ]
    );
}
```
