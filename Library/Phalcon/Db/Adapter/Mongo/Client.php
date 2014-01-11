<?php
/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2014 Phalcon Team (http://www.phalconphp.com)       |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file docs/LICENSE.txt.                        |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Tuğrul Topuz <tugrultopuz@gmail.com>                          |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Db\Adapter\Mongo;

class Client extends \MongoClient
{
    public function __get($dbName)
    {
        return new Db($this, $dbName);
    }

    public function selectCollection($db, $collection)
    {
        return new Collection($this->selectDB($db), $collection);
    }

    public function selectDB($name)
    {
        return new Db($this, $name);
    }
}
