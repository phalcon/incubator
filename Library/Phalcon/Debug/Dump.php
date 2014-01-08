<?php
namespace Phalcon\Debug;

/**
 * Concrete class for generating debug dumps related to the output source.
 */
class Dump
{


    /**
     * @var string
     */
    protected static $sapi = null;
    
    /**
     * Controls whether dump should be echoed
     * 
     * @var bool
     */
    protected static $output = true;
    
    /**
     * Controls whether output buffer should be flushed after echoing dump
     * 
     * @var bool
     */
    protected $flushBuffer = true;
    
    /**
     * Constructs Dump object.
     * 
     * @param bool $flushBuffer if set to false, ob_flush will not be called after echo
     */
    public function __construct($flushBuffer = true)
    {
        $this->flushBuffer = $flushBuffer;
    }

    /**
     * Get the current value of the debug output environment.
     * This defaults to the value of PHP_SAPI.
     *
     * @return string;
     */
    public static function getSapi()
    {
        if (static::$sapi === null) {
            static::$sapi = PHP_SAPI;
        }
        return static::$sapi;
    }
    
    /**
     * Sets sapi value
     * 
     * @param string $sapi
     */
    public static function setSapi($sapi)
    {
        static::$sapi = $sapi;
    }

    /**
     * Sets output flag.
     * 
     * @param type $output
     */
    public static function setOutput($flag)
    {
        static::$output = $flag;
    }
    
    /**
     * Gets current value of output flag.
     *  
     * @return bool
     */
    public static function getOutput()
    {
        return static::$output;
    }

    
    

    /**
     * Debug helper function.  This is a wrapper for var_dump|xdebug_var_dump that adds
     * the <pre /> tags, cleans up newlines and indents, adds file name and line number info
     * and runs htmlentities() before output.
     *
     * @param  mixed  $var   The variable to dump.
     * @param  bool   $outputDump    Overrides self::$output flag
     * @return string
     */
    public function dump($var, $outputDump = null)
    {
        // add file and line on which Dump was called
        $backtrace = debug_backtrace();
        $label = 'Dump - File: ' . $backtrace[0]['file'] . ', Line: ' . $backtrace[0]['line'];

        // var_dump the variable into a buffer and keep the output
        ob_start();
        if ($this->xdebugDumpExists()) {
            xdebug_var_dump($var);
        } else {
            var_dump($var);
        }
        
        $output = ob_get_clean();

        // neaten the newlines and indents
        $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
        if (static::getSapi() == 'cli') {
            $label = $label . PHP_EOL;
            $output = PHP_EOL . $label
                    . PHP_EOL . $output
                    . PHP_EOL;
        } else {
            $label = $label . PHP_EOL;
            $output = htmlentities($output, ENT_QUOTES, 'UTF-8');
            $output = '<pre>'
                    . $label
                    . $output
                    . '</pre>';
        }

        $echo = self::$output;
        if (is_bool($outputDump)) {
            $echo = $outputDump;
        }
        if ($echo) {
            echo $output;
            if ($this->flushBuffer) {
            ob_flush();
            }
        }
        return $output;
    }
    
    /**
     * Checks if xdebug_var_dump function is available
     * 
     * @return bool
     */
    protected function xdebugDumpExists()
    {
        return function_exists('xdebug_var_dump');
    }
}