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
 * Parameter Interface.
 *
 * Represents an abstract command line Parameter.
 * This interface must be used as a base interface for any parameters - Options and Arguments
 * (so-called Operands).
 *
 * This interface includes properties for each of the following:
 *
 * - Parameter name
 * - Parameter description
 * - Parameter default value
 *
 * @package Phalcon\Cli\Input
 */
interface ParameterInterface
{
    /**
     * Gets the Parameter name.
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the Parameter name.
     *
     * @param string $name The Parameter name.
     * @return ParameterInterface
     */
    public function setName($name);

    /**
     * Gets the description text of the Parameter.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Sets the description text of the Parameter.
     *
     * @param string $description The description text of the Parameter.
     * @return ParameterInterface
     */
    public function setDescription($description);

    /**
     * Gets the default value of the Parameter.
     *
     * @return mixed
     */
    public function getDefault();

    /**
     * Sets the default value of the Parameter.
     *
     * @param mixed $default The default value of the Parameter.
     * @return ParameterInterface
     */
    public function setDefault($default);

    /**
     * Checks if Parameter has default value
     *
     * @return bool
     */
    public function hasDefault();
}
