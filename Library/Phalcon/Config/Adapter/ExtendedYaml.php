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
  | Authors: Alexey Stetsenko <freekzy@gmail.com>                          |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Config\Adapter;

use Phalcon\Config;
use Phalcon\Config\Exception;

/**
 * Phalcon\Config\Adapter\ExtendedYaml
 *
 * Reads yaml files and convert it to Phalcon\Config objects.
 * Unlike native Yaml adapter supports callbacks.
 *
 * @package Phalcon\Config\Adapter
 */
class ExtendedYaml extends Config
{
    /**
     * Class constructor.
     *
     * @param  string $filePath Config file path
     * @param  array $callbacks Content handlers for YAML nodes. Associative array of YAML tag => callable mappings.
     * @link http://php.net/manual/ru/yaml.callbacks.parse.php
     *
     * @throws \Phalcon\Config\Exception
     */
    public function __construct($filePath, array $callbacks = [])
    {
        if (!extension_loaded('yaml')) {
            throw new Exception('Yaml extension not loaded');
        }

        $ndocs = 0;
        if (false === $result = yaml_parse_file($filePath, 0, $ndocs, $callbacks)) {
            throw new Exception("Configuration file $filePath can't be loaded");
        }

        parent::__construct($result);
    }
}
