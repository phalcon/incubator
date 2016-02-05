<?php

/*
 +------------------------------------------------------------------------+
 | Phalcon Framework                                                      |
 +------------------------------------------------------------------------+
 | Copyright (c) 2011-2016 Phalcon Team (http://www.phalconphp.com)       |
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
 * The Environment Aware Interface
 *
 * This interface must be implemented in those classes that uses EnvironmentInterface interface
 *
 * @package Phalcon\Cli\Environment
 */
interface EnvironmentAwareInterface
{
    /**
     * Sets the Environment object
     *
     * @param EnvironmentInterface $environment Environment
     * @return $this
     */
    public function setEnvironment(EnvironmentInterface $environment);

    /**
     * Gets the Environment object
     *
     * @return EnvironmentInterface
     */
    public function getEnvironment();
}
