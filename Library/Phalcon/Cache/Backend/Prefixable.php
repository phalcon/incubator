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
  | Authors: Nikita Vershinin <endeveit@gmail.com>                         |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Cache\Backend;

use Phalcon\Cache\Backend;
use Phalcon\Cache\BackendInterface;

/**
 * Phalcon\Cache\Backend\Prefixable
 * Abstract class for backend with support of «prefix» option.
 */
abstract class Prefixable extends Backend implements BackendInterface
{
    /**
     * Returns prefixed identifier.
     *
     * @param  string $id
     * @return string
     */
    protected function getPrefixedIdentifier($id)
    {
        return $this->_prefix . $id;
    }
}
