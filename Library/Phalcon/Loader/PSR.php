<?php
namespace Phalcon\Loader;

use Phalcon\Loader;
use Phalcon\Loader\Exception;

/**
 * Phalcon\Loader\PSR
 * Implements PSR-0 autoloader for your apps.
 */
class PSR extends Loader
{
    /**
     * AutoLoad
     *
     * @param string $className
     */
    public function autoLoad($className)
    {

        $array = explode('\\', $className);
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

        //If it did not fit standard PSR-0, pass it on to the original Phalcon autoloader
        parent::autoLoad($className);
    }

}
