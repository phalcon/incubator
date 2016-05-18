<?php

namespace Phalcon\Test\Mvc\Model\EagerLoading\Stubs;

class RobotPart extends AbstractModel
{
    protected $robot_id;
    protected $part_id;

    public function initialize()
    {
        $this->belongsTo('robot_id', __NAMESPACE__ . '\Robot', 'id', [
            'alias'      => 'Robot',
            'foreignKey' => true
        ]);

        $this->belongsTo('part_id', __NAMESPACE__ . '\Part', 'id', [
            'alias'      => 'Part',
            'foreignKey' => true
        ]);
    }
}
