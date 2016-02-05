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

class Cursor extends \MongoCursor
{
    protected $className;
    protected $collection;

    public function __construct(
        $collection,
        $className = 'Phalcon\Db\Adapter\Mongo\Cursor',
        $query = array(),
        $fields = array()
    ) {
        $ns = $collection->db->name . '.' . $collection->getName();
        parent::__construct($collection->db->conn, $ns, $query, $fields);

        $this->collection = $collection;
        $this->className = $className;
    }

    public function current()
    {
        return new $this->className($this->collection, parent::current());
    }

    public function getNext()
    {
        $this->next();

        return $this->current();
    }

    public function addOption($key, $value)
    {
        parent::addOption($key, $value);

        return $this;
    }

    public function awaitData($wait = true)
    {
        parent::awaitData($wait);

        return $this;
    }

    public function batchSize($batchSize)
    {
        parent::batchSize($batchSize);

        return $this;
    }

    public function fields($f)
    {
        parent::fields($f);

        return $this;
    }

    public function hint($index)
    {
        parent::hint($index);

        return $this;
    }

    public function immortal($liveForever = true)
    {
        parent::immortal($liveForever);

        return $this;
    }

    public function limit($num)
    {
        parent::limit($num);

        return $this;
    }

    public function partial($okay = true)
    {
        parent::partial($okay);

        return $this;
    }

    public function setFlag($flag, $set = true)
    {
        parent::setFlag($flag, $set);

        return $this;
    }

    public function skip($num)
    {
        parent::skip($num);

        return $this;
    }

    public function slaveOkay($okay = true)
    {
        parent::slaveOkay($okay);

        return $this;
    }

    public function snapshot()
    {
        parent::snapshot();

        return $this;
    }

    public function sort($fields)
    {
        parent::sort($fields);

        return $this;
    }

    public function tailable($tail = true)
    {
        parent::tailable($tail);

        return $this;
    }

    public function timeout($ms)
    {
        parent::timeout($ms);

        return $this;
    }
}
