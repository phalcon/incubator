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

namespace Phalcon\Config;

use Phalcon\Config\Adapter\Ini;
use Phalcon\Config\Adapter\Json;
use Phalcon\Config\Adapter\Php;
use Phalcon\Config\Adapter\Yaml;
use Phalcon\Config;

/**
 * Phalcon config loader
 *
 * @package Phalcon\Config
 */
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

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'ini':
                return new Ini($filePath);
            case 'json':
                return new Json($filePath);
            case 'php':
            case 'php5':
            case 'inc':
                return new Php($filePath);
            case 'yml':
            case 'yaml':
                return new Yaml($filePath);
            default:
                throw new Exception('Config adapter for .'  . $extension . ' files is not support');
        }
    }

    /**
     * Load config files from directory and merge to one
     *
     * @param string $configsDir
     * @return Config
     * @throws Exception
     */
    public static function loadDir($configsDir)
    {
        if (!is_dir($configsDir)) {
            throw new Exception('Config directory not found');
        }
        $config = new Config();
        $fileSystem = new \FilesystemIterator($configsDir, \FilesystemIterator::SKIP_DOTS);
        /* @var \SplFileInfo $configFile*/
        foreach ($fileSystem as $configFile) {
            if ($configFile->isFile()) {
                $cfg = self::load($configFile->getRealPath());
                $config->merge($cfg);
            }
        }
        return $config;
    }
}
