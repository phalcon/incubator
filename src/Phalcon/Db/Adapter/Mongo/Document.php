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

class Document
{
    protected $collection;

    public function __construct($collection, $doc = [])
    {
        $this->collection = $collection;
        $this->extract(new \RecursiveArrayIterator($doc));
    }

    private function extract($iterator, $className = null)
    {
        if (is_numeric($iterator->key())) {
            $container = [];
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

            if (DbRef::isRef($value)) {
                $value = new DbRef($this->collection, $value);
            } elseif (is_array($value)) {
                $value = $this->extract($iterator->getChildren(), 'stdClass');
            }

            if (is_numeric($key)) {
                $container[$key] = $value;
            } else {
                $container->{$key} = $value;
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
