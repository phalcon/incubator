<?php
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
        if (!isset($config['adapter'])) {
            throw new Exception('Adapter option must be required');
        }
        $namespace = __NAMESPACE__ . '\\' . 'Pdo' . '\\';
        $className = $namespace . ucfirst(strtolower($config['adapter']));
        if (!class_exists($className)) {
            throw new Exception('Database adapter '  . $config['adapter'] . ' is not supported');
        }
        return new $className($config);
    }
}
