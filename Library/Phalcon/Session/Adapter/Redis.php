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
  | Authors: kenjikobe <kenji.minamoto@gmail.com>                          |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Session\Adapter;

use \Phalcon\Session\Adapter,
	\Phalcon\Session\AdapterInterface,
	\Phalcon\Session\Exception;

/**
 * Phalcon\Session\Adapter\Redis
 *
 * Database adapter for Phalcon\Session
 */
class Redis extends Adapter implements AdapterInterface
{

	/**
	 * Phalcon\Session\Adapter\Redis constructor
	 *
	 * @param array $options
	 */
	public function __construct($options=null)
	{

		if(!isset($options['path'])){
			throw new Exception("The parameter 'save_path' is required");
		}

		ini_set('session.save_handler', 'redis');
		ini_set('session.save_path', $options['path']);

		parent::__construct($options);
	}

}

