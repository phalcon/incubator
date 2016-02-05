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

namespace Phalcon\Avatar;

/**
 * Avatar interface.
 *
 * Defining an interface for classes that use avatars.
 *
 * @package Phalcon\Avatar
 */
interface Avatarable
{
    /**
     * Sets the default image to use for avatars.
     *
     * @param mixed $image The default image to use
     * @return $this
     */
    public function setDefaultImage($image);

    /**
     * Gets the current default image.
     *
     * @return mixed
     */
    public function getDefaultImage();

    /**
     * Sets the avatar size to use.
     *
     * @param int $size The avatar size to use.
     * @return $this
     */
    public function setSize($size);

    /**
     * Gets the currently set avatar size.
     *
     * @return int
     */
    public function getSize();

    /**
     * Gets the avatar URL based on the provided email identity.
     *
     * @param mixed $identity The identity to get the gravatar for.
     * @return string
     */
    public function getAvatar($identity);
}
