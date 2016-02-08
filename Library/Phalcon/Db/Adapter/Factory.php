<?php
/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2016 Phalcon Team (http://www.phalconphp.com)       |
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
 * @package Phalcon\Db\Adapter
 */
namespace Phalcon\Db\Adapter;

use Phalcon\Db\Exception;
use Phalcon\Db\AdapterInterface;

class Factory
{
    /**
     * Load config from file extension dynamical
     *
     * @param array $config Adapter config
     * @return AdapterInterface
     *
     * @throws Exception
     */
    public static function load(array $config)
    {
        if (!isset($config['adapter']) || empty($config['adapter']) || !is_string($config['adapter'])) {
            throw new Exception("A database 'adapter' option is required and must be a nonempty string.");
        }

        $namespace = __NAMESPACE__ . '\\' . 'Pdo' . '\\';
        $adapter = ucfirst(strtolower($config['adapter']));
        $className = $namespace . $adapter;

        if (!class_exists($className)) {
            throw new Exception("Database adapter {$adapter} is not supported");
        }

        unset($config['adapter']);
        return new $className($config);
    }
}
