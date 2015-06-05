<?php namespace EagerLoadingTestModel;

use Phalcon\Mvc\Model\Relation;

class Robot extends AbstractModel
{
    protected $id;
    protected $name;
    protected $parent_id;
    protected $manufacturer_id;

    public function initialize()
    {
        $this->belongsTo('manufacturer_id', 'EagerLoadingTestModel\Manufacturer', 'id', array (
            'alias'      => 'Manufacturer',
            'foreignKey' => true
        ));

        // Recursive relation
        $this->belongsTo('parent_id', 'EagerLoadingTestModel\Robot', 'id', array (
            'alias'      => 'Parent',
            'foreignKey' => true
        ));

        $this->hasMany('id', 'EagerLoadingTestModel\Robot', 'parent_id', array (
            'alias'      => 'Children',
            'foreignKey' => array (
                'action' => Relation::ACTION_CASCADE
            )
        ));

        $this->hasOne('id', 'EagerLoadingTestModel\Purpose', 'robot_id', array (
            'alias'      => 'Purpose',
            'foreignKey' => array (
                'action' => Relation::ACTION_CASCADE
            )
        ));
        
        $this->hasMany('id', 'EagerLoadingTestModel\Bug', 'robot_id', array (
            'alias'      => 'Bugs',
            'foreignKey' => array (
                'action' => Relation::ACTION_CASCADE
            )
        ));

        $this->hasManyToMany(
            'id',
            'EagerLoadingTestModel\RobotPart',
            'robot_id',
            'part_id',
            'EagerLoadingTestModel\Part',
            'id',
            array (
                'alias'      => 'Parts',
                'foreignKey' => array (
                    'action' => Relation::ACTION_CASCADE
                )
            )
        );

        // Wrong relation
        $this->hasMany(
            array ('id', 'parent_id'),
            'EagerLoadingTestModel\NotSupportedRelation',
            array ('id', 'robot_id'),
            array (
                'alias'      => 'NotSupportedRelations',
                'foreignKey' => array (
                    'action' => Relation::ACTION_CASCADE
                )
            )
        );
    }
}
