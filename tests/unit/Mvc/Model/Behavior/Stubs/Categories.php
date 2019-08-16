<?php

use Phalcon\Mvc\Model;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\Model\Behavior\NestedSet as NestedSetBehavior;

/**
 * Class CategoriesOneRoot
 *
 * @method Resultset\Simple roots()
 * @method boolean saveNode(array $attributes = null, array $whiteList = null)
 * @method boolean appendTo(ModelInterface $target, array $attributes = null)
 * @method boolean insertAfter(ModelInterface $target, array $attributes = null)
 * @method boolean insertBefore(ModelInterface $target, array $attributes = null)
 * @method boolean prependTo(ModelInterface $target, array $attributes = null)
 * @method boolean moveAsFirst(ModelInterface $target)
 * @method boolean moveBefore(ModelInterface $target)
 *
 * @property int id
 * @property string name
 * @property string description
 * @property int root
 * @property int lft
 * @property int rgt
 * @property int level
 */
class CategoriesOneRoot extends Model
{
    public static $table = 'categories';

    public function initialize()
    {
        $this->setSource(self::$table);
        $this->addBehavior(new NestedSetBehavior([
            'hasManyRoots' => false,
        ]));
    }
}

/**
 * Class CategoriesManyRoots
 *
 * @method Resultset\Simple roots()
 * @method boolean saveNode(array $attributes = null, array $whiteList = null)
 * @method boolean appendTo(ModelInterface $target, array $attributes = null)
 * @method boolean insertAfter(ModelInterface $target, array $attributes = null)
 * @method boolean insertBefore(ModelInterface $target, array $attributes = null)
 * @method boolean prependTo(ModelInterface $target, array $attributes = null)
 * @method boolean moveAsFirst(ModelInterface $target)
 * @method boolean moveBefore(ModelInterface $target)
 *
 * @property int id
 * @property string name
 * @property string description
 * @property int root
 * @property int lft
 * @property int rgt
 * @property int level
 */
class CategoriesManyRoots extends Model
{
    public static $table = 'categories';

    public function initialize()
    {
        $this->setSource(self::$table);

        $this->addBehavior(
            new NestedSetBehavior(
                [
                    'hasManyRoots' => true,
                ]
            )
        );
    }
}
