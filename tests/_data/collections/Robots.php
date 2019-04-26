<?php

namespace Phalcon\Test\Collections;

use Phalcon\Mvc\MongoCollection;

/**
 * Robots Collection
 *
 * @method static Robots[] find(array $parameters = null)
 * @method static Robots findFirst(array $parameters = null)
 *
 * @property \MongoId _id
 * @property string name
 * @property string type
 * @property int year
 * @property string datetime
 * @property string deleted
 * @property string text
 *
 * @package Phalcon\Test\Collections
 */
class Robots extends MongoCollection
{
    public function validationHasFailed(): bool
    {
        new \Exception("Not implemented");
    }
}
