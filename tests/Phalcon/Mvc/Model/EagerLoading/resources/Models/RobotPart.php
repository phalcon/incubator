<?php namespace EagerLoadingTestModel;

class RobotPart extends AbstractModel
{
    protected $robot_id;
    protected $part_id;

    public function initialize()
    {
        $this->belongsTo('robot_id', 'EagerLoadingTestModel\Robot', 'id', array (
            'alias'      => 'Robot',
            'foreignKey' => true
        ));

        $this->belongsTo('part_id', 'EagerLoadingTestModel\Part', 'id', array (
            'alias'      => 'Part',
            'foreignKey' => true
        ));
    }
}
