<?php

namespace Phalcon\Test\Collections\Cars;

use MongoDB\BSON\ObjectID;
use Phalcon\Mvc\MongoCollection;

/**
 * Phalcon\Test\Collections\Cars
 *
 * @property string $name
 *
 * @package Phalcon\Test\Collections
 */
class Parts extends MongoCollection
{
    protected $_embedded = true;
}
