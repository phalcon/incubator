<?php

namespace Phalcon\Test\Collections;

use MongoDB\BSON\ObjectID;
use Phalcon\Mvc\MongoCollection;

/**
 * Phalcon\Test\Collections\Cars
 *
 * @property string $name
 * @method ObjectID getId()
 *
 * @package Phalcon\Test\Collections
 */
class Heroes extends MongoCollection
{
    public function getSource(): string
    {
        return 'heroes';
    }

    public function validationHasFailed(): bool
    {
        new \Exception("Not implemented");
    }
}
