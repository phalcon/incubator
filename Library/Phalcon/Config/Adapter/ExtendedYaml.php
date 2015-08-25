<?php
namespace Phalcon\Config\Adapter;

use Phalcon\Config;
use Phalcon\Config\Exception;

/**
 * Phalcon\Config\Adapter\ExtendedYaml
 * Reads yaml files and convert it to Phalcon\Config objects.
 * Unlike native Yaml adapter supports callbacks.
 */
class ExtendedYaml extends Config implements \ArrayAccess
{
    /**
     * Class constructor.
     *
     * @param  string                    $filePath
     * @param  array                     $callbacks
     * @throws \Phalcon\Config\Exception
     */
    public function __construct($filePath, $callbacks = array())
    {
        if (!extension_loaded('yaml')) {
            throw new Exception('Yaml extension not loaded');
        }

        $ndocs = 0;
        if (false === $result = @yaml_parse_file($filePath, 0, $ndocs, $callbacks)) {
            throw new Exception('Configuration file ' . $filePath . ' can\'t be loaded');
        }

        parent::__construct($result);
    }
}
