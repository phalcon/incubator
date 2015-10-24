<?php

namespace Phalcon\Test\Mvc\Model\EagerLoading\Stubs;

use Phalcon\Mvc\Model\Relation;

class Purpose extends AbstractModel
{
    protected $id;
    protected $name;
    protected $robot_id;

    public function initialize()
    {
        $this->belongsTo('robot_id', __NAMESPACE__ . '\Robot', 'id', [
            'alias'      => 'Robot',
            'foreignKey' => [
                'action' => Relation::ACTION_RESTRICT
            ]
        ]);
    }
}
