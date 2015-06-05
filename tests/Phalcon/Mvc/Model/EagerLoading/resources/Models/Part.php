<?php namespace EagerLoadingTestModel;

use Phalcon\Mvc\Model\Relation;

class Part extends AbstractModel
{
    protected $id;
    protected $name;

    public function initialize()
    {
        $this->hasManyToMany(
            'id',
            'EagerLoadingTestModel\RobotPart',
            'part_id',
            'robot_id',
            'EagerLoadingTestModel\Robot',
            'id',
            array (
                'alias'      => 'Robots',
                'foreignKey' => array (
                    'action' => Relation::ACTION_RESTRICT
                )
            )
        );
    }
}
