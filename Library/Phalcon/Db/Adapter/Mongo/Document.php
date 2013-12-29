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

class Document
{
    protected $collection;

    public function __construct($collection, $doc = array())
    {
        $this->collection = $collection;
        $this->extract(new \RecursiveArrayIterator($doc));
    }

    private function extract($iterator, $className = null)
    {
        if (is_numeric($iterator->key())) {
            $container = array();
        } else {
            if (empty($className)) {
                $container = $this;
            } else {
                $container = new $className;
            }
        }

        while ($iterator->valid()) {

            $key = $iterator->key();
            $value = $iterator->current();

            if (is_numeric($key)) {
                if (DbRef::isRef($value)) {
                    $container[$key] = new DbRef($this->collection, $value);
                } else if (is_array($value)) {
                    $container[$key] = $this->extract($iterator->getChildren(), 'stdClass');
                } else {
                    $container[$key] = $value;
                }
            } else {
                if (DbRef::isRef($value)) {
                    $container->{$key} = new DbRef($this->collection, $value);
                } else if (is_array($value)) {
                    $container->{$key} = $this->extract($iterator->getChildren(), 'stdClass');
                } else {
                    $container->{$key} = $value;
                }
            }

            $iterator->next();
        }

        return $container;
    }

    public function save()
    {
        //TODO: upsert document
    }
}
