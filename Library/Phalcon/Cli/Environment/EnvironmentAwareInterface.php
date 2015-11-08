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
 * The Environment Aware Interface
 *
 * This interface must be implemented in those classes that uses Environment object
 *
 * @package Phalcon\Cli\Environment
 */
interface EnvironmentAwareInterface
{
    /**
     * Sets the Environment object
     *
     * @param Environment $environment Environment
     * @return $this
     */
    public function setEnvironment(Environment $environment);

    /**
     * Gets the Environment object
     *
     * @return Environment
     */
    public function getEnvironment();
}
