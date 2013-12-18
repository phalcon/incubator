<?php

namespace Phalcon\Logger\Formatter;

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
	 * _name
	 * Holds name of this logger.
	 *
	 * @var string
	 * @access private
	 */
	private $_name;

	/**
	 * style
	 * Optional CSS snippet for logger icon in Firelogger console.
	 *
	 * @var string
	 * @access private
	 */
	private $style;

	/**
	 * encoding
	 *
	 * @var string
	 * @access private
	 */
	private $encoding = 'UTF-8';

	/**
	 * maxPickleDepth
	 * Maximum recursion for pickle method
	 *
	 * @var int
	 * @access private
	 */
	private $maxPickleDepth = 10;

	/**
	 * __construct
	 *
	 * @param string $name
	 * @access public
	 * @return void
	 */
	public function __construct($name = 'logger')
	{
		$this->_name = $name;
	}

	/**
	 * Setter for _name
	 *
	 * @return $this
	 */
	public function setName($name)
	{
		$this->_name = $name;
		return $this;
	}


	/**
	 * Setter for style
	 *
	 * @return $this
	 */
	public function setStyle($style)
	{
		$this->style = $style;
		return $this;
	}


	/**
	 * getTypeString
	 * Translates Phalcon log types into Firelogger log level strings.
	 *
	 * @param int $type
	 * @access public
	 * @return string
	 */
	public function getTypeString($type)
	{
		switch ($type) {
			case 0:
				// emergence
				return 'critical';
			case 2:
			case 3:
				// error, alert
				return 'error';
			case 4:
				// warning
				return 'warning';
			case 5:
			case 6:
				// info, notice
				return 'info';
			case 7:
			default:
				// debug, log
				return 'debug';
		}
	}

	/**
	 * pickle
	 * Reformats the passed log item. Recursive.
	 *
	 * @param mixed $var
	 * @param int   $level
	 * @access private
	 * @return mixed
	 */
	private function pickle($var, $level = 0)
	{
		if (is_bool($var) || is_null($var) || is_int($var) || is_float($var)) {
			return $var;

		} elseif (is_string($var)) {
			return @iconv('UTF-16', 'UTF-8//IGNORE', iconv($this->encoding, 'UTF-16//IGNORE', $var)); // intentionally @

		} elseif (is_array($var)) {
			static $marker;
			if ($marker === null) $marker = uniqid("\x00", true); // detects recursions
			if (isset($var[$marker])) {
				return '*RECURSION*';

			} elseif ($level < $this->maxPickleDepth || !$this->maxPickleDepth) {
				$var[$marker] = true;
				$res = array();
				foreach ($var as $k => &$v) {
					if ($k !== $marker) $res[$this->pickle($k)] = $this->pickle($v, $level + 1);
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
	 * extractTrace
	 * Extract useful information from exception traces.
	 *
	 * @param array $trace
	 * @access private
	 * @return array
	 */
	private function extractTrace($trace)
	{
		$t = array();
		$f = array();
		foreach ($trace as $frame) {
			// prevent notices about invalid indices, wasn't able to google smart solution, PHP is dumb ass
			$frame += array('file' => null, 'line' => null, 'class' => null, 'type' => null, 'function' => null, 'object' => null, 'args' => null);
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
	 * extractFileLine
	 * Extracts useful information from debug_backtrace()
	 *
	 * @param array $trace Array returned by debug_backtrace()
	 * @access private
	 * @return array
	 */
	private function extractFileLine($trace)
	{
		while (count($trace) && !array_key_exists('file', $trace[0])) array_shift($trace);
		$thisFile = $trace[0]['file'];
		while (count($trace) && (array_key_exists('file', $trace[0]) && $trace[0]['file'] == $thisFile)) array_shift($trace);
		while (count($trace) && !array_key_exists('file', $trace[0])) array_shift($trace);

		if (count($trace) == 0) return array("?", "0");
		$file = $trace[0]['file'];
		$line = $trace[0]['line'];
		return array($file, $line);
	}


	/**
	 * Applies a format to a message before sent it to the internal log
	 *
	 * @param str|int|float|array|null|Exception $message
	 * @param int                                $type
	 * @param int                                $timestamp
	 * @param array                              $trace Optional. This is the output from debug_backtrace().
	 * @param int                                $order Optional. How many logs are stored in the stack already.
	 * @return mixed
	 */
	public function format($message, $type, $timestamp, $trace = null, $order = 0)
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
			'name'      => $this->_name,
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
			$data = array();
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

}
