<?php

namespace Phalcon\Test\Mvc\Model\EagerLoading\Stubs;

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
        $this->belongsTo(
            ['id','robot_id'],
            __NAMESPACE__ . '\Robots',
            ['id','robot_id'],
            [
                'alias'      => 'Robot',
                'foreignKey' => true
            ]
        );
    }
}
