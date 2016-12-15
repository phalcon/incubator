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

use Phalcon\Annotations\Adapter;
use Phalcon\Annotations\Exception;
use Phalcon\Annotations\Reflection;
use Phalcon\Traits\ConfigurableTrait;

/**
 * Phalcon\Annotations\Extended\AbstractAdapter
 *
 * This is the base class for Phalcon\Annotations\Extended adapters
 *
 * @package Phalcon\Annotations\Extended
 */
abstract class AbstractAdapter extends Adapter implements AdapterInterface
{
    use ConfigurableTrait;

    /**
     * Configurable properties.
     * @var array
     */
    protected $configurable = [];

    /**
     * AbstractAdapter constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->setParameters($options);
    }

    /**
     * Returns prefixed identifier.
     *
     * @param  string $id
     * @return string
     */
    abstract protected function getPrefixedIdentifier($id);

    /**
     * Check and cast returned result.
     *
     * @param  mixed $result
     * @return bool
     */
    protected function castResult($result)
    {
        if ($result instanceof Reflection) {
            return $result;
        }

        return false;
    }

    /**
     * Check annotation key.
     *
     * @param string $key
     *
     * @throws Exception
     */
    protected function checkKey($key)
    {
        if (!is_string($key)) {
            throw new Exception(
                sprintf('Invalid key type key to retrieve annotations. Expected string but got %s.', gettype($key))
            );
        }
    }
}
