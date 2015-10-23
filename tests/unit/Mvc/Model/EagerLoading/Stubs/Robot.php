<?php

namespace Phalcon\Test\Mvc\Model\EagerLoading\Stubs;

use Phalcon\Mvc\Model\Relation;

/**
 * Class Robot
 * @package Phalcon\Test\Mvc\Model\EagerLoading\Stubs
 *
 * @property \Phalcon\Mvc\Model\Resultset\Simple parts
 * @property \Phalcon\Test\Mvc\Model\EagerLoading\Stubs\Purpose purpose
 * @property \Phalcon\Mvc\Model\Resultset\Simple bugs
 * @method static Robot findFirstById(int $robotId)
 */
class Robot extends AbstractModel
{
    protected $id;
    protected $name;
    protected $parent_id;
    protected $manufacturer_id;

    public function initialize()
    {
        $this->belongsTo('manufacturer_id', __NAMESPACE__ . '\Manufacturer', 'id', [
            'alias'      => 'Manufacturer',
            'foreignKey' => true
        ]);

        // Recursive relation
        $this->belongsTo('parent_id', __NAMESPACE__ . '\Robot', 'id', [
            'alias'      => 'Parent',
            'foreignKey' => true
        ]);

        $this->hasMany('id', __NAMESPACE__ . '\Robot', 'parent_id', [
            'alias'      => 'Children',
            'foreignKey' => [
                'action' => Relation::ACTION_CASCADE
            ]
        ]);

        $this->hasOne('id', __NAMESPACE__ . '\Purpose', 'robot_id', [
            'alias'      => 'Purpose',
            'foreignKey' => [
                'action' => Relation::ACTION_CASCADE
            ]
        ]);

        $this->hasMany('id', __NAMESPACE__ . '\Bug', 'robot_id', [
            'alias'      => 'Bugs',
            'foreignKey' => [
                'action' => Relation::ACTION_CASCADE
            ]
        ]);

        $this->hasManyToMany(
            'id',
            __NAMESPACE__ . '\RobotPart',
            'robot_id',
            'part_id',
            __NAMESPACE__ . '\Part',
            'id',
            [
                'alias'      => 'Parts',
                'foreignKey' => [
                    'action' => Relation::ACTION_CASCADE
                ]
            ]
        );

        // Wrong relation
        $this->hasMany(
            ['id', 'parent_id'],
            __NAMESPACE__ . '\NotSupportedRelation',
            ['id', 'robot_id'],
            [
                'alias'      => 'NotSupportedRelations',
                'foreignKey' => [
                    'action' => Relation::ACTION_CASCADE
                ]
            ]
        );
    }
}
