<?php

namespace Phalcon\Test\Mvc\Model\EagerLoading\Stubs;

use Phalcon\Mvc\Model\Relation;

class Part extends AbstractModel
{
    protected $id;
    protected $name;

    public function initialize()
    {
        $this->hasManyToMany(
            'id',
            __NAMESPACE__ . '\RobotPart',
            'part_id',
            'robot_id',
            __NAMESPACE__ . '\Robot',
            'id',
            [
                'alias'      => 'Robots',
                'foreignKey' => [
                    'action' => Relation::ACTION_RESTRICT
                ]
            ]
        );
    }
}
