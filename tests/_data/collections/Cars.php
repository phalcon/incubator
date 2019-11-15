<?php

namespace Phalcon\Test\Collections;

use MongoDB\BSON\ObjectID;
use Phalcon\Mvc\MongoCollection;
use Phalcon\Test\Collections\Cars\Parts;

/**
 * Phalcon\Test\Collections\Cars
 *
 * @property string $manufacturer
 * @property string $model
 * @property string $rank
 * @property int $value
 * @property \Phalcon\Test\Collections\Cars\Parts $parts
 * @method ObjectID getId()
 *
 * @package Phalcon\Test\Collections
 */
class Cars extends MongoCollection
{
    protected $_embeddedArray = [
        'parts' => Parts::class
    ];

    public function getSource()
    {
        return 'cars';
    }
}
