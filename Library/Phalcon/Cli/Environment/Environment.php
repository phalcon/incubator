<?php

/*
 +------------------------------------------------------------------------+
 | Phalcon Framework                                                      |
 +------------------------------------------------------------------------+
 | Copyright (c) 2011-2015 Phalcon Team (http://www.phalconphp.com)       |
 +------------------------------------------------------------------------+
 | This source file is subject to the New BSD License that is bundled     |
 | with this package in the file docs/LICENSE.txt.                        |
 |                                                                        |
 | If you did not receive a copy of the license and are unable to         |
 | obtain it through the world-wide-web, please send an email             |
 | to license@phalconphp.com so we can send you a copy immediately.       |
 +------------------------------------------------------------------------+
 | Authors: Serghei Iakovlev <serghei@phalconphp.com>                     |
 +------------------------------------------------------------------------+
 */

namespace Phalcon\Cli\Environment;

/**
 * Console Environment
 *
 * @package Phalcon\Cli\Environment
 */
class Environment implements EnvironmentInterface
{
    /**
     * Terminal dimensions
     * @var array
     */
    protected $dimensions = null;

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function isWindows()
    {
        return 'WIN' === strtoupper(substr(PHP_OS, 0, 3));
    }

    /**
     * When currently running under MS Windows checks if ANSI x3.64 is supported and enabled.
     *
     * @link http://vt100.net/annarbor/aaa-ug/section13.html
     *
     * @return bool
     */
    public function isAnsicon()
    {
        return false !== getenv('ANSICON') || 'ON' === getenv('ConEmuANSI');
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function isConsole()
    {
        return 'cli' === PHP_SAPI;
    }

    /**
     * {@inheritdoc}
     *
     * @param int|resource $fd File descriptor, must be either a file resource or an integer [Optional]
     * @return bool
     */
    public function isInteractive($fd = EnvironmentInterface::STDOUT)
    {
        return function_exists('posix_isatty') && @posix_isatty($fd);
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function hasColorSupport()
    {
        if ($this->isWindows()) {
            return $this->isAnsicon();
        }

        if (!defined('STDOUT')) {
            return false;
        }

        return $this->isInteractive(STDOUT);
    }

    /**
     * Gets the terminal dimensions based on the current environment.
     *
     * @return array
     */
    public function getDimensions()
    {
        if (!empty($this->dimensions)) {
            return $this->dimensions;
        }

        if ($this->isWindows()) {
            if ($this->isAnsicon() && preg_match('#(?:\d+x\d+)\s+\((\d+)x(\d+)\)#', trim(getenv('ANSICON')), $match)) {
                // ANSICON maintains an environment variable which holds the current screen size
                // e.g. ANSICON=200x9999 (200x100)
                return [(int) $match[1], (int) $match[2]];
            }

            if (1 === preg_match('/^(\d+)x(\d+)$/', $this->getModeCon(), $match)) {
                return [(int) $match[1], (int) $match[2]];
            }
        } elseif (1 === preg_match('/^(\d+)x(\d+)$/', $this->getSttySize(), $match)) {
            return [(int) $match[1], (int) $match[2]];
        }

        // fallback mode
        return [EnvironmentInterface::WIDTH, EnvironmentInterface::HEIGHT];
    }

    /**
     * Sets terminal dimensions.
     *
     * @param int $width  The width
     * @param int $height The height
     * @return $this
     */
    public function setDimensions($width, $height)
    {
        if ((is_int($width) || ctype_digit($width)) && (is_int($height) || ctype_digit($height))) {
            $this->dimensions = [$width, $height];
        }

        return $this;
    }

    /**
     * Runs and parses Microsoft DOS `MODE CON` command if it's available.
     *
     * @link https://technet.microsoft.com/en-us/library/bb490932.aspx
     *
     * @return null|string
     */
    public function getModeCon()
    {
        if (!function_exists('proc_open')) {
            return null;
        }

        $descriptorspec = [
            1 => ['pipe', 'w'], // stdout
            2 => ['pipe', 'w']  // stderr
        ];

        $process = proc_open(
            'MODE CON',
            $descriptorspec,
            $pipes,
            null,
            null,
            ['suppress_errors' => true] // suppressing any error output
        );

        if (is_resource($process)) {
            $info = stream_get_contents($pipes[1]);

            fclose($pipes[1]);
            fclose($pipes[2]);

            proc_close($process);

            if (1 === preg_match('/--------+\r?\n.+?(\d+)\r?\n.+?(\d+)\r?\n/', $info, $match)) {
                return $match[2].'x'.$match[1];
            }
        }

        return null;
    }

    /**
     * Runs and parses `stty size` command if it's available.
     *
     * @return null|string
     */
    public function getSttySize()
    {
        if (!function_exists('proc_open')) {
            return null;
        }

        $descriptorspec = [
            1 => ['pipe', 'w'], // stdout
            2 => ['pipe', 'w']  // stderr
        ];

        $process = proc_open(
            'stty size',
            $descriptorspec,
            $pipes,
            null,
            null,
            ['suppress_errors' => true] // suppressing any error output
        );

        if (is_resource($process)) {
            $info = stream_get_contents($pipes[1]);

            fclose($pipes[1]);
            fclose($pipes[2]);

            proc_close($process);

            if (1 === preg_match('#(\d+) (\d+)#', $info, $match)) {
                return $match[2].'x'.$match[1];
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function getNumberOfColumns()
    {
        $dimensions = $this->getdimensions();

        return $dimensions[0];
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function getNumberOfRows()
    {
        $dimensions = $this->getdimensions();

        return $dimensions[1];
    }
}
