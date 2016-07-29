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

class Collection extends \MongoCollection
{
    public $db;

    public function __construct($db, $name)
    {
        $this->db = $db;
        parent::__construct($db, $name);
    }

    public function __get($name)
    {
        return $this->db->selectCollection($name);
    }

    public function find($query = [], $fields = [])
    {
        return $this->findAsObject('Sonucu\Mongo\Document', $query, $fields);
    }

    public function findAsObject($className, $query = [], $fields = [])
    {
        return new Cursor($this, $className, $query, $fields);
    }

    public function findOne($query = [], $fields = [])
    {
        return $this->findOneAsObject('Sonucu\Mongo\Document', $query, $fields);
    }

    public function findOneAsObject($className, $query = [], $fields = [])
    {
        return new $className($this, parent::findOne($query, $fields));
    }

    public function insert(Document $doc, $options = [])
    {
        //TODO: iterate props and create db refs
    }

    public function batchInsert(array $col, $options = [])
    {
        //TODO: iterate props and create db refs
    }

    public function save(Document $doc, $options = [])
    {
        //TODO: iterate props and create db refs
    }
}
