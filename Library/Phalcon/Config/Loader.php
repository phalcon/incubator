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
  | Authors: Anton Kornilov <kachit@yandex.ru>                             |
  +------------------------------------------------------------------------+
*/

/**
 * Phalcon config loader
 *
 * @package Phalcon\Config
 */
namespace Phalcon\Config;

use Phalcon\Config;

class Loader
{
    /**
     * Load config from file extension dynamical
     *
     * @param string $filePath
     *
     * @return Config
     * @throws Exception
     */
    public static function load($filePath)
    {
        if (!is_file($filePath)) {
            throw new Exception('Config file not found');
        }

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $namespace = __NAMESPACE__ . '\\' . 'Adapter' . '\\';
        $className = $namespace . ucfirst(strtolower($extension));

        if (!class_exists($className)) {
            throw new Exception('Config adapter for .'  . $extension . ' files is not support');
        }

        return new $className($filePath);
    }
}
