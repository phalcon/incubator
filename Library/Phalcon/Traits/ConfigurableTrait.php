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

namespace Phalcon\Traits;

use Traversable;
use Phalcon\Text;
use InvalidArgumentException;

/**
 * Phalcon\Traits\ConfigurableTrait
 *
 * Allows to define parameters which can be set by passing them to the class constructor.
 * These parameters should be defined in the `$configurable` array.
 *
 * @property array $configurable
 * @package Phalcon\Traits
 */
trait ConfigurableTrait
{
    /**
     * Sets the parameters.
     *
     * @param  Traversable|array $parameters
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    protected function setParameters($parameters)
    {
        if (!property_exists($this, 'configurable') || !is_array($this->configurable)) {
            return $this;
        }

        if (!is_array($parameters) && !($parameters instanceof Traversable)) {
            throw new InvalidArgumentException('The $parameters argument must be either an array or Traversable');
        }

        foreach ($parameters as $key => $value) {
            if (!in_array($key, $this->configurable, true)) {
                continue;
            }

            $method = 'set' . Text::camelize($key);

            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }
}
