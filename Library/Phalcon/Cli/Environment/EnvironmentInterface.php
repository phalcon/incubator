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
 * Environment Interface
 *
 * @package Phalcon\Cli\Environment
 */
interface EnvironmentInterface
{
    const STDIN  = 0;
    const STDOUT = 1;
    const STDERR = 2;

    const WIDTH  = 80;
    const HEIGHT = 25;

    /**
     * Checks if currently running under MS Windows.
     *
     * @return bool
     */
    public function isWindows();

    /**
     * Checks if running in a console environment (CLI).
     *
     * @return bool
     */
    public function isConsole();

    /**
     * Checks if the file descriptor is an interactive terminal.
     *
     * @param int|resource $fd File descriptor, must be either a file resource or an integer [Optional]
     * @return bool
     */
    public function isInteractive($fd = self::STDOUT);

    /**
     * Checks the supports of colorization.
     *
     * @return bool
     */
    public function hasColorSupport();

    /**
     * Gets the number of columns of the terminal.
     *
     * @return int
     */
    public function getNumberOfColumns();

    /**
     * Gets the number of rows of the terminal.
     *
     * @return int
     */
    public function getNumberOfRows();
}
