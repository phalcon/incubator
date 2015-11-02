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
 * Phalcon DB adapters factory
 *
 * @author Kachit
 * @package Phalcon\Db\Adapter
 */
namespace Phalcon\Db\Adapter;

use Phalcon\Db\Exception;

class Factory
{
    /**
     * Load config from file extension dynamical
     *
     * @param array $config
     * @throws Exception
     */
    public static function load(array $config)
    {
        if (!isset($config['adapter']) || empty($config['adapter'])) {
            throw new Exception('Adapter option must be required');
        }
        $namespace = __NAMESPACE__ . '\\' . 'Pdo' . '\\';
        $adapter = ucfirst(strtolower($config['adapter']));
        $className = $namespace . $adapter;
        if (!class_exists($className)) {
            throw new Exception('Database adapter '  . $adapter . ' is not supported');
        }
        return new $className($config);
    }
}
