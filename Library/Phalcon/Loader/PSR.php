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
  | Authors: Piyush Rajesh <mba.piyushgupta@gmail.com>                     |
  |          Serghei Iakovlev <serghei@phalconphp.com>                     |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Loader;

use Phalcon\Loader;

/**
 * Phalcon\Loader\PSR.
 * Implements PSR-0 autoloader for your apps.
 *
 * @package Phalcon\Loader
 */
class PSR extends Loader
{
    /**
     * Namespace separator
     * @var string
     */
    protected $namespaceSeparator = '\\';

    /**
     * Loads the given class or interface.
     *
     * @param string $className The name of the class to load.
     *
     * @return bool
     */
    public function autoLoad($className)
    {
        // Reduce slashes
        $className = ltrim($className, $this->namespaceSeparator);

        $array = explode($this->namespaceSeparator, $className);

        if (array_key_exists($array[0], $this->_namespaces)) {
            $array[0] = $this->_namespaces[$array[0]];
            $class = array_pop($array);
            array_push($array, str_replace("_", DIRECTORY_SEPARATOR, $class));

            $file = implode($array, DIRECTORY_SEPARATOR);

            foreach ($this->_extensions as $ext) {
                if (file_exists($file . ".$ext")) {
                    require $file . ".$ext";
                    return true;
                }
            }
        }

        // If it did not fit standard PSR-0, pass it on to the original Phalcon autoloader
        return parent::autoLoad($className);
    }
}
