<?php namespace EagerLoadingTestModel;

use Phalcon\Mvc\Model\Relation;

class Bug extends AbstractModel
{
    protected $id;
    protected $name;
    protected $robot_id;

    public function initialize()
    {
        $this->belongsTo('robot_id', 'EagerLoadingTestModel\Robot', 'id', array (
            'alias'      => 'Robot',
            'foreignKey' => array (
                'action' => Relation::ACTION_CASCADE
            )
        ));
    }
}
