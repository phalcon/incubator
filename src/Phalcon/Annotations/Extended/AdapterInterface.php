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

namespace Phalcon\Annotations\Extended;

use Phalcon\Annotations\Reflection;
use Phalcon\Annotations\AdapterInterface as BaseInterface;

/**
 * Phalcon\Annotations\Extended\AdapterInterface
 *
 * This interface must be implemented by adapters in Phalcon\Annotations\Extended
 *
 * @package Phalcon\Annotations\Extended
 */
interface AdapterInterface extends BaseInterface
{
    /**
     * Reads parsed annotations from the current storage.
     *
     * @param  string $key
     * @return Reflection|bool
     */
    public function read($key);

    /**
     * Writes parsed annotations to the current storage.
     *
     * @param  string     $key
     * @param  Reflection $reflection
     * @return bool
     */
    public function write($key, Reflection $reflection);

    /**
     * Immediately invalidates all existing items.
     *
     * @return bool
     */
    public function flush();
}
