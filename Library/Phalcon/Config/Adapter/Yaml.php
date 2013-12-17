<?php

namespace Phalcon\Config\Adapter;

use Phalcon\Config;
use Phalcon\Config\Exception;

/**
 * Phalcon\Config\Adapter\Yaml
 * Reads yaml files and convert it to Phalcon\Config objects.
 */
class Yaml extends Config implements \ArrayAccess
{

	/**
	 * Phalcon\Config\Adapter\Yaml
	 *
	 * @param string $filePath
	 */
	public function __construct($filePath, $callbacks = array())
	{
		if (!extension_loaded('yaml'))
			throw new Exception('Yaml extension not loaded');

		if (false === $result = @yaml_parse_file($filePath, 0, $ndocs, $callbacks))
			throw new Exception('Configuration file ' . $filePath . ' can\'t be loaded');

		parent::__construct($result);
	}

}
