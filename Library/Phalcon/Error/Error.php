<?php
/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2015 Phalcon Team (http://www.phalconphp.com)       |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file docs/LICENSE.txt.                        |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Andres Gutierrez <andres@phalconphp.com>                      |
  |          Eduar Carvajal <eduar@phalconphp.com>                         |
  |          Nikita Vershinin <endeveit@gmail.com>                         |
  +------------------------------------------------------------------------+
*/
namespace Phalcon\Error;

/**
 * Class Error
 * @package Phalcon\Error
 *
 * @method int type()
 * @method string message()
 * @method string file()
 * @method string line()
 * @method \Exception exception()
 * @method bool isException()
 * @method bool isError()
 */
class Error
{
    /**
     * @var array
     */
    protected $attributes;

    /**
     * Class constructor sets the attributes.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $defaults = [
            'type'        => -1,
            'message'     => 'No error message',
            'file'        => '',
            'line'        => '',
            'exception'   => null,
            'isException' => false,
            'isError'     => false,
        ];

        $options = array_merge($defaults, $options);

        foreach ($options as $option => $value) {
            $this->attributes[$option] = $value;
        }
    }

    /**
     * Magic method to retrieve the attributes.
     *
     * @param  string $method
     * @param  array  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return isset($this->attributes[$method]) ? $this->attributes[$method] : null;
    }
}
