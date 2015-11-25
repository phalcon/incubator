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

namespace Phalcon\Cli\Input;

/**
 * Argument Interface.
 *
 * This interface is meant to represent a command line argument according to IEEE Std 1003.1, 2013 Edition
 * and to provide methods for most common operations. The ArgumentInterface uses a convention stating that
 * the argument is a synonym of the operand. Additional functionality for working with arguments can be
 * provided on top of the interface (ParameterInterface) or externally.
 *
 * This interface includes properties for each of the following:
 *
 * - Argument type (optional, required, array)
 *
 * @link http://pubs.opengroup.org/onlinepubs/9699919799/basedefs/V1_chap12.html
 * @package Phalcon\Cli\Input
 */
interface ArgumentInterface extends ParameterInterface
{
    /**
     * This constant marks the Argument as required.
     * @type int
     */
    const IS_REQUIRED = 1;

    /**
     * This constant marks the Argument as optional.
     * @type int
     */
    const IS_OPTIONAL = 2;

    /**
     * This constant marks the Argument as an array.
     * @type int
     */
    const IS_ARRAY = 4;

    /**
     * Checks if the Argument is required.
     *
     * @return bool
     */
    public function isRequired();

    /**
     * Checks if the Argument is optional.
     *
     * @return bool
     */
    public function isOptional();

    /**
     * Checks if the Argument can take multiple values.
     *
     * @return bool
     */
    public function isArray();

    /**
     * Sets the Argument mode (one of the IS_* constants).
     *
     * @param int $mode The Argument mode.
     * @return ArgumentInterface
     */
    public function setMode($mode);

    /**
     * Gets the Argument mode (one of the IS_* constants).
     *
     * @return int
     */
    public function getMode();
}
