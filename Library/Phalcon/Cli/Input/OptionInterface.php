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
 * Option Interface.
 *
 * This interface is meant to represent a command line option according to IEEE Std 1003.1, 2013 Edition
 * and to provide methods for most common operations. Additional functionality for working with
 * options can be provided on top of the interface (ParameterInterface) or externally.
 *
 * This interface includes properties for each of the following:
 *
 * - Option value type (unacceptable, optional, required, array)
 *
 * @link http://pubs.opengroup.org/onlinepubs/9699919799/basedefs/V1_chap12.html
 * @package Phalcon\Cli\Input
 */
interface OptionInterface extends ParameterInterface
{
    /**
     * This constant marks the Option value as unacceptable.
     * @type int
     */
    const VALUE_UNACCEPTABLE = 1;

    /**
     * This constant marks the Option value as required.
     * @type int
     */
    const VALUE_REQUIRED = 2;

    /**
     * This constant marks the Option value as optional.
     * @type int
     */
    const VALUE_OPTIONAL = 4;

    /**
     * This constant marks the Option value as an array.
     * @type int
     */
    const VALUE_IS_ARRAY = 8;

    /**
     * Gets the short option name.
     *
     * @return string
     */
    public function getShortName();

    /**
     * Gets the short Option name.
     *
     * @param string $name The short Option name.
     * @return OptionInterface
     */
    public function setShortName($name);

    /**
     * Checks if the Option has default short name.
     *
     * @return bool
     */
    public function hasShortName();

    /**
     * Checks if the Option shouldn't have a value.
     *
     * @return bool
     */
    public function isValueUnacceptable();

    /**
     * Checks if the Option requires a value.
     *
     * @return bool
     */
    public function isValueRequired();

    /**
     * Checks if the Option takes an optional value.
     *
     * @return bool
     */
    public function isValueOptional();

    /**
     * Checks if the Option can take multiple values.
     *
     * @return bool
     */
    public function isValueArray();

    /**
     * Sets the Option value mode (one of the VALUE_* constants).
     *
     * @param int $mode The Option value mode.
     * @return OptionInterface
     */
    public function setValueMode($mode);

    /**
     * gets the Option value mode (one of the VALUE_* constants).
     *
     * @return int
     */
    public function getValueMode();
}
