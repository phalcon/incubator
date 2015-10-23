<?php

namespace Phalcon\Test\Mvc\Model\EagerLoading\Stubs;

use Phalcon\Mvc\Model\Relation;

/**
 * Class Manufacturer
 * @package Phalcon\Test\Mvc\Model\EagerLoading\Stubs
 *
 * @property \Phalcon\Mvc\Model\Resultset\Simple robots
 * @method static Manufacturer findFirstById(int $manufacturerId)
 */
class Manufacturer extends AbstractModel
{
    protected $id;
    protected $name;

    public function initialize()
    {
        $this->hasMany('id', __NAMESPACE__ . '\Robot', 'manufacturer_id', [
            'alias'      => 'Robots',
            'foreignKey' => [
                'action' => Relation::ACTION_CASCADE
            ]
        ]);
    }
}
