<?php
/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2016 Phalcon Team (http://www.phalconphp.com)       |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file LICENSE.txt.                             |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Tuğrul Topuz <tugrultopuz@gmail.com>                          |
  +------------------------------------------------------------------------+
*/
namespace Phalcon\Db\Adapter\Mongo;

use MongoDB;

class Db extends MongoDB
{
    public $conn = null;
    public $name = null;

    public function __construct($conn, $name)
    {
        $this->conn = $conn;
        $this->name = $name;
        parent::__construct($conn, $name);
    }

    public function __get($name)
    {
        return new Collection($this, $name);
    }

    public function selectCollection($name)
    {
        return new Collection($this, $name);
    }

    public function createCollection($name, $options = [])
    {
        parent::createCollection($name, $options);

        return $this->selectCollection($name);
    }
}
