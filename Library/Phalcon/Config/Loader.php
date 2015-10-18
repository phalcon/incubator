<?php
/**
 * Phalcon config loader
 *
 * @author Kachit
 * @package Phalcon\Config
 */
namespace Phalcon\Config;

class Loader {

    /**
     * Load config from file extension dynamical
     *
     * @param string $filePath
     * @throws Exception
     */
    public static function load($filePath) {
        if (!is_file($filePath)) {
            throw new Exception('Config file not found');
        }
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $namespace = __NAMESPACE__ . '\\' . 'Adapter' . '\\';
        $className = $namespace . ucfirst($extension);
        if (!class_exists($className)) {
            throw new Exception('Config adapter for .'  . $extension . ' files is not support');
        }
        return new $className($filePath);
    }
}