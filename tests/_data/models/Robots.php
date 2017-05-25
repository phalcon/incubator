<?php

namespace Phalcon\Test\Models;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Behavior\Blameable;
use Phalcon\Test\Behavior\Blameable\Audit;

class Robots extends Model
{
    public function initialize()
    {
        $this->keepSnapshots(true);
        $this->addBehavior(
            new Blameable(
                [
                    'auditClass' => Audit::class,
                ]
            )
        );
    }
}
