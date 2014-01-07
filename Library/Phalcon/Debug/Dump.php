<?php
namespace Phalcon\Debug;
class Dump
{
    public static $output = 1;
    
    /**
     * Not meant to be used
     */
    private function __construct(){}
    
    /**
     * Dumps var and flushes output
     * 
     * @param mixed $var 
     * @return boolean
     */
    public static function dump($var)
    {
        if(!self::$output)
        {
            return;
        }
        
        if(extension_loaded('xdebug'))
        {
            xdebug_var_dump($var);
            ob_flush();
            return;
        }
        
        var_dump($var);
        ob_flush();
    }
}