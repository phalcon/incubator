<?php
/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2012 Phalcon Team (http://www.phalconphp.com)       |
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

class Error
{

	/**
	 * @var array
	 */
	protected $_attributes;

	/**
	 * Class constructor sets the attributes.
	 *
	 * @param array $options
	 */
	public function __construct(array $options = array())
	{
		$defaults = array(
			'type'        => -1,
			'message'     => 'No error message',
			'file'        => '',
			'line'        => '',
			'exception'   => null,
			'isException' => false,
			'isError'     => false,
		);

		$options = array_merge($defaults, $options);

		foreach ($options as $option => $value) {
			$this->_attributes[$option] = $value;
		}
	}

	/**
	 * Magic method to retrieve the attributes.
	 *
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 */
	public function __call($method, $args)
	{
		return isset($this->_attributes[$method]) ? $this->_attributes[$method] : null;
	}

}
