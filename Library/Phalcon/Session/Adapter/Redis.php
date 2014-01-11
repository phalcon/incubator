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

use Phalcon\Session\Adapter;
use Phalcon\Session\AdapterInterface;
use Phalcon\Session\Exception;

/**
 * Phalcon\Session\Adapter\Redis
 * Database adapter for Phalcon\Session
 */
class Redis extends Adapter implements AdapterInterface
{

    /**
     * Phalcon\Session\Adapter\Redis constructor
     *
     * @param array $options
     */
    public function __construct($options = null)
    {

        if (!isset($options['path'])) {
            throw new Exception("The parameter 'save_path' is required");
        }

        ini_set('session.save_handler', 'redis');
        ini_set('session.save_path', $options['path']);

        if (isset($options['name'])) {
            ini_set('session.name', $options['name']);
        }

        if (isset($options['lifetime'])) {
            ini_set('session.gc_maxlifetime', $options['lifetime']);
        }

        if (isset($options['cookie_lifetime'])) {
            ini_set('session.cookie_lifetime', $options['cookie_lifetime']);
        }

        parent::__construct($options);
    }

}
