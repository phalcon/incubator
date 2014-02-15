<?php
namespace Phalcon\Logger\Formatter;

use \Phalcon\Logger\Exception;
use \Phalcon\Logger as Logger;

/**
 * Phalcon\Logger\Formatter\Firelogger
 * Formats messages to be sent to Firelogger
 *
 * @link    http://firelogger.binaryage.com/
 * @version 0.1
 * @author  Richard Laffers <rlaffers@gmail.com>
 * @license The BSD 3-Clause License {@link http://opensource.org/licenses/BSD-3-Clause}
 */
class Firelogger extends \Phalcon\Logger\Formatter implements \Phalcon\Logger\FormatterInterface
{

    /**
     * Holds name of this logger.
     *
     * @var string
     */
    protected $name;

    /**
     * Optional CSS snippet for logger icon in Firelogger console.
     *
     * @var string
     */
    protected $style;

    /**
     * encoding
     *
     * @var string
     */
    protected $encoding = 'UTF-8';

    /**
     * Maximum recursion for pickle method
     *
     * @var integer
     */
    protected $maxPickleDepth = 10;

    /**
     * Class constructor.
     *
     * @param string $name
     */
    public function __construct($name = 'logger')
    {
        $this->name = $name;
    }

    /**
     * Setter for _name
     *
     * @param  string                               $name
     * @return \Phalcon\Logger\Formatter\Firelogger
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Setter for style
     *
     * @param  string                               $style
     * @return \Phalcon\Logger\Formatter\Firelogger
     */
    public function setStyle($style)
    {
        $this->style = $style;

        return $this;
    }

    /**
     * Translates Phalcon log types into Firelogger log level strings.
     *
     * @param  integer $type
     * @return string
     */
    public function getTypeString($type)
    {

        switch ($type) {
            case Logger::EMERGENCE:
            case Logger::CRITICAL:
                // emergence, critical
                return 'critical';
            case Logger::ALERT:
            case Logger::ERROR:
                // error, alert
                return 'error';
            case Logger::WARNING:
                // warning
                return 'warning';
            case Logger::NOTICE:
            case Logger::INFO:
                // info, notice
                return 'info';
            case Logger::DEBUG:
            case Logger::CUSTOM:
            case Logger::SPECIAL:
            default:
                // debug, log, custom, special
                return 'debug';
        }
    }

    /**
     * Applies a format to a message before sent it to the internal log
     *
     * @param  string|integer|float|array|null|\Exception $message
     * @param  integer                                    $type
     * @param  integer                                    $timestamp
     * @param  array                                      $context
     * @param  array                                      $trace     This is the output from debug_backtrace().
     * @param  integer                                    $order     How many logs are stored in the stack already.
     * @return mixed
     */
    public function format($message, $type, $timestamp, $context = array(), $trace = null, $order = 0)
    {
        $level = $this->getTypeString($type);

        if ($message instanceof \Exception) {
            $exception = $message;
            $message = '';
        } elseif (!is_string($message)) {
            $richMessage = $message;
            $message = '';
        }

        $item = array(
            'name'      => $this->name,
            'args'      => array(),
            'level'     => $level,
            'timestamp' => $timestamp,
            'order'     => $order, // PHP is really fast, timestamp has insufficient resolution for log records ordering
            'time'      => gmdate('H:i:s', (int) $timestamp) . '.000',
            'template'  => $message,
            'message'   => $message
        );

        if ($this->style) {
            $item['style'] = $this->style;
        }

        if (isset($exception)) {
            // exception with backtrace
            $traceInfo = $this->extractTrace($exception->getTrace());
            $item['exc_info'] = array(
                $exception->getMessage(),
                $exception->getFile(),
                $traceInfo[0]
            );
            $item['exc_frames'] = $traceInfo[1];
            $item['exc_text'] = get_class($exception);
            $item['template'] = $exception->getMessage();
            $item['code'] = $exception->getCode();
            $item['pathname'] = $exception->getFile();
            $item['lineno'] = $exception->getLine();
        } else {
            // rich log record
            $backtrace = debug_backtrace();
            list($file, $line) = $this->extractFileLine($backtrace);

            $item['pathname'] = $file;
            $item['lineno'] = $line;

            if (isset($trace)) {
                $traceInfo = $this->extractTrace($trace);
                $item['exc_info'] = array(
                    '',
                    '',
                    $traceInfo[0]
                );
                $item['exc_frames'] = $traceInfo[1];
            }

            if (isset($richMessage)) {
                $item['args'] = array($richMessage);
            }
        }

        return $this->pickle($item);
    }

    /**
     * Reformats the passed log item. Recursive.
     *
     * @param  mixed   $var
     * @param  integer $level
     * @return mixed
     */
    protected function pickle($var, $level = 0)
    {
        if (is_bool($var) || is_null($var) || is_int($var) || is_float($var)) {
            return $var;
        } elseif (is_string($var)) {
            // intentionally @
            return @iconv('UTF-16', 'UTF-8//IGNORE', iconv($this->encoding, 'UTF-16//IGNORE', $var));
        } elseif (is_array($var)) {
            static $marker;
            if ($marker === null) {
                $marker = uniqid("\x00", true);
            } // detects recursions

            if (isset($var[$marker])) {
                return '*RECURSION*';
            } elseif ($level < $this->maxPickleDepth || !$this->maxPickleDepth) {
                $var[$marker] = true;
                $res = array();

                foreach ($var as $k => &$v) {
                    if ($k !== $marker) {
                        $res[$this->pickle($k)] = $this->pickle($v, $level + 1);
                    }
                }

                unset($var[$marker]);

                return $res;

            } else {
                return '...';
            }

        } elseif (is_object($var)) {
            $arr = (array) $var;
            $arr['__class##'] = get_class($var);

            static $list = array(); // detects recursions
            if (in_array($var, $list, true)) {
                return '*RECURSION*';
            } elseif ($level < $this->maxPickleDepth || !$this->maxPickleDepth) {
                $list[] = $var;
                $res = array();

                foreach ($arr as $k => &$v) {
                    if ($k[0] === "\x00") {
                        $k = substr($k, strrpos($k, "\x00") + 1);
                    }

                    $res[$this->pickle($k)] = $this->pickle($v, $level + 1);
                }

                array_pop($list);

                return $res;
            } else {
                return '...';
            }
        } elseif (is_resource($var)) {
            return '*' . get_resource_type($var) . ' resource*';

        } else {
            return '*unknown type*';
        }
    }

    /**
     * Extract useful information from exception traces.
     *
     * @param  array $trace
     * @return array
     */
    protected function extractTrace($trace)
    {
        $t = array();
        $f = array();
        foreach ($trace as $frame) {
            // prevent notices about invalid indices, wasn't able to google smart solution, PHP is dumb ass
            $frame += array(
                'file'     => null,
                'line'     => null,
                'class'    => null,
                'type'     => null,
                'function' => null,
                'object'   => null,
                'args'     => null
            );

            $t[] = array(
                $frame['file'],
                $frame['line'],
                $frame['class'] . $frame['type'] . $frame['function'],
                $frame['object']
            );

            $f[] = $frame['args'];
        };

        return array($t, $f);
    }

    /**
     * Extracts useful information from debug_backtrace()
     *
     * @param  array $trace Array returned by debug_backtrace()
     * @return array
     */
    protected function extractFileLine($trace)
    {
        while (count($trace) && !array_key_exists('file', $trace[0])) {
            array_shift($trace);
        }

        $thisFile = $trace[0]['file'];
        while (count($trace) && (array_key_exists('file', $trace[0]) && $trace[0]['file'] == $thisFile)) {
            array_shift($trace);
        }

        while (count($trace) && !array_key_exists('file', $trace[0])) {
            array_shift($trace);
        }

        if (count($trace) == 0) {
            return array("?", "0");
        }

        $file = $trace[0]['file'];
        $line = $trace[0]['line'];

        return array($file, $line);
    }
}
