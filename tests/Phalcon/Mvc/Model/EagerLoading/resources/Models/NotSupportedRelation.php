<?php namespace EagerLoadingTestModel;

use Phalcon\Mvc\Model\Relation;

class NotSupportedRelation extends AbstractModel
{
    protected $id;
    protected $name;
    protected $robot_id;

    public function getSource()
    {
        return 'bug';
    }

    public function initialize()
    {
        $this->belongsTo(array ('id','robot_id'), 'EagerLoadingTestModel\Robots', array ('id','robot_id'), array (
            'alias'      => 'Robot',
            'foreignKey' => true
        ));
    }
}
