<?php

namespace Phalcon\Test\Collections;

use MongoDB\BSON\ObjectID;
use Phalcon\Mvc\MongoCollection;

/**
 * Phalcon\Test\Collections\Cars
 *
 * @property string $manufacturer
 * @property string $model
 * @property string $rank
 * @property int $value
 * @method ObjectID getId()
 *
 * @package Phalcon\Test\Collections
 */
class Cars extends MongoCollection
{
    public function getSource()
    {
        return 'cars';
    }
}
