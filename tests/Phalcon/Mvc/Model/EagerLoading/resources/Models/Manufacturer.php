<?php namespace EagerLoadingTestModel;

use Phalcon\Mvc\Model\Relation;

class Manufacturer extends AbstractModel
{
    protected $id;
    protected $name;

    public function initialize()
    {
        $this->hasMany('id', 'EagerLoadingTestModel\Robot', 'manufacturer_id', array (
            'alias'      => 'Robots',
            'foreignKey' => array (
                'action' => Relation::ACTION_CASCADE
            )
        ));
    }
}
