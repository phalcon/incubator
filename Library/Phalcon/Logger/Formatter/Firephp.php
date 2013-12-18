<?php

namespace Phalcon\Logger\Formatter;

/**
 * Phalcon\Logger\Formatter\Firephp
 * Formats messages to be sent to Firephp
 */
class Firephp extends \Phalcon\Logger\Formatter implements \Phalcon\Logger\FormatterInterface
{

	public function getTypeString($type)
	{
		switch ($type) {
			case 7:
				return 'LOG';
			case 3:
				return 'ERROR';
			case 4:
				return 'WARN';
			case 1:
				return 'ERROR';
			case 8:
				return 'INFO';
			case 2:
				return 'WARN';
			case 5:
				return 'INFO';
			case 6:
				return 'INFO';
			case 0:
				return 'ERROR';
			case 9:
				return 'LOG';
			default:
				return 'LOG';
		}
	}

	/**
	 * Applies a format to a message before sent it to the internal log
	 *
	 * @param string $message
	 * @param int    $type
	 * @param int    $timestamp
	 */
	public function format($message, $type, $timestamp)
	{

		$file = null;
		$line = null;

		$typeStr = $this->getTypeString($type);

		$backtrace = debug_backtrace();
		foreach ($backtrace as $trace) {
			if (isset($trace['file'])) {
				if (!strpos($trace['file'], 'Phalcon')) {
					$file = $trace['file'];
					$line = $trace['line'];
				}
			}
		}

		$log = array(
			array(
				'Type' => $typeStr,
				'File' => $file,
				'Line' => $line
			),
			$message
		);

		$encoded = json_encode($log);

		return strlen($encoded) . '|' . $encoded . '|';
	}

}