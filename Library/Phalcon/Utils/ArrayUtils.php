<?php

/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2017 Phalcon Team (https://www.phalconphp.com)      |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file LICENSE.txt.                             |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Sergii Svyrydenko <sergey.v.sviridenko@gmail.com>             |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Utils;

use Traversable;
use InvalidArgumentException;

/**
 * Utility class for manipulation of PHP arrays.
 *
 * @package Phalcon\Utils
 */
class ArrayUtils
{
    /**
     * Convert an iterator to an array.
     *
     * Converts an iterator to an array. The $recursive flag, on by default,
     * hints whether or not you want to do so recursively.
     *
     * @param  array | Traversable  $iterator The array or Traversable object to convert
     * @param  bool $recursive Recursively check all nested structures
     * @throws InvalidArgumentException if $iterator is not an array or a Traversable object
     * @return array
     */
    public function iteratorToArray($iterator, $recursive = true)
    {
        if (!is_array($iterator) && !$iterator instanceof Traversable) {
            throw new InvalidArgumentException(
                __METHOD__ . ' must be either an array or Traversable'
            );
        }

        if (!$recursive) {
            if (is_array($iterator)) {
                return $iterator;
            }

            return iterator_to_array($iterator);
        }

        if (method_exists($iterator, 'toArray')) {
            return $iterator->toArray();
        }

        $array = [];

        foreach ($iterator as $key => $value) {
            if (is_scalar($value)) {
                $array[$key] = $value;
            } elseif ($value instanceof Traversable) {
                $array[$key] = $this->iteratorToArray($value, $recursive);
            } elseif (is_array($value)) {
                $array[$key] = $this->iteratorToArray($value, $recursive);
            } else {
                $array[$key] = $value;
            }
        }

        return $array;
    }
}
