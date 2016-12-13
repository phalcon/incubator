<?php

/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2016 Phalcon Team (https://www.phalconphp.com)      |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file LICENSE.txt.                             |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Serghei Iakovlev <serghei@phalconphp.com>                     |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Annotations\Extended\Adapter;

use Phalcon\Annotations\Exception;
use Phalcon\Annotations\Reflection;
use Phalcon\Annotations\Extended\AbstractAdapter;

/**
 * Phalcon\Annotations\Extended\Adapter\Memory
 *
 * Extended Memory adapter for storing annotations in memory.
 * This adapter is the suitable development/testing.
 *
 * <code>
 * use Phalcon\Annotations\Extended\Adapter\Memory;
 *
 * $annotations = new Memory();
 * </code>
 *
 * @package Phalcon\Annotations\Extended\Adapter
 */
class Memory extends AbstractAdapter
{
    protected $data = [];

    /**
     * Reads parsed annotations from memory.
     *
     * @param  string $key
     * @return Reflection|bool
     *
     * @throws Exception
     */
    public function read($key)
    {
        $this->checkKey($key);

        $result = null;
        $prefixedKey = $this->getPrefixedIdentifier($key);

        if (isset($this->data[$prefixedKey])) {
            $result = $this->data[$prefixedKey];
        }

        return $this->castResult($result);
    }

    /**
     * Writes parsed annotations to memory.
     *
     * @param  string     $key
     * @param  Reflection $reflection
     * @return bool
     *
     * @throws Exception
     */
    public function write($key, Reflection $reflection)
    {
        $this->checkKey($key);

        $prefixedKey = $this->getPrefixedIdentifier($key);

        $this->data[$prefixedKey] = $reflection;


        return true;
    }

    /**
     * Immediately invalidates all existing items.
     *
     * @return bool
     */
    public function flush()
    {
        $this->data = [];

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $id
     * @return string
     */
    protected function getPrefixedIdentifier($key)
    {
        return strtolower($key);
    }
}
