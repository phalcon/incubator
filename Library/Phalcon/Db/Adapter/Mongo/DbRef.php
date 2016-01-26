<?php
/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2016 Phalcon Team (http://www.phalconphp.com)       |
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

class DbRef extends \MongoDBRef
{
    protected $collection;
    protected $ref;

    public function __construct($collection, $ref)
    {
        $this->collection = $collection;
        $this->ref = $ref;
    }

    public function getRelated()
    {
        $db = $this->collection->db;
        $ref = $this->ref;

        if (empty($ref['$db'])) {
            $collection = $db->selectCollection($ref['$ref']);
        } else {
            $collection = $db->conn->selectCollection($ref['$db'], $ref['$ref']);
        }

        $doc = self::get($db, $ref);

        if (is_null($doc)) {
            return null;
        }

        return new Document($collection, $doc);
    }

    public function __get($name)
    {
        return $this->getRelated()->{$name};
    }

    public function __set($name, $value)
    {
        $this->getRelated()->{$name} = $value;
    }
}
