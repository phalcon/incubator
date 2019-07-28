<?php

/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2016 Phalcon Team (https://www.phalconphp.com)      |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file LICENSE.txt.                             |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Serghei Iakovlev <serghei@phalconphp.com>                     |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Avatar;

use InvalidArgumentException;
use Phalcon\Config;

/**
 * Phalcon Gravatar.
 *
 * This component provides an easy way to retrieve a user's profile image
 * from Gravatar site based on a given email address.
 *
 * If the email address cannot be matched with a Gravatar account, an alternative will
 * be returned based on the Gravatar::$defaultImage setting. Users with gravatars can
 * have a default image if you want to.
 *
 * @link https://en.gravatar.com/site/implement/
 * @package Phalcon\Avatar
 */
class Gravatar implements Avatarable
{
    /**
     * The gravatar service URL
     * @type string
     */
    const HTTP_URL  = 'http://www.gravatar.com/avatar/';

    /**
     * The secure gravatar service URL
     * @type string
     */
    const HTTPS_URL = 'https://secure.gravatar.com/avatar/';

    /**
     * The minimum size allowed for the Gravatar
     * @type int
     */
    const MIN_AVATAR_SIZE = 0;

    /**
     * The maximum size allowed for the Gravatar
     * @type int
     */
    const MAX_AVATAR_SIZE = 2048;

    /**
     * @type string
     */
    const RATING_G = 'g';

    /**
     * @type string
     */
    const RATING_PG = 'pg';

    /**
     * @type string
     */
    const RATING_R = 'r';

    /**
     * @type string
     */
    const RATING_X = 'x';

    /**
     * The default image.
     *
     * Possible values:
     * - String of the gravatar-recognized default image "type" to use
     * - URL
     * - false if using the default gravatar image
     *
     * @var mixed
     */
    private $defaultImage = false;

    /**
     * Gravatar defaults
     * @var array
     */
    private $validDefaults = [
        '404'       => true,
        'mm'        => true,
        'identicon' => true,
        'monsterid' => true,
        'wavatar'   => true,
        'retro'     => true,
        'blank'     => true,
    ];

    /**
     * Gravatar rating
     * @var array
     */
    private $validRatings = [
        self::RATING_G  => true,
        self::RATING_PG => true,
        self::RATING_R  => true,
        self::RATING_X  => true,
    ];

    /**
     * The size to use for avatars
     * @var int
     */
    private $size = 80;

    /**
     * The maximum rating to allow for the avatar
     * @var string
     */
    private $rating = self::RATING_G;

    /**
     * Should we use the secure (HTTPS) URL base?
     * @var bool
     */
    private $secureURL = false;

    /**
     * The default image shall be shown even if user that has an gravatar profile.
     * @var bool
     */
    private $forceDefault = false;

    public function __construct($config)
    {
        if ($config instanceof Config) {
            $config = $config->toArray();
        }

        if (!is_array($config)) {
            throw new InvalidArgumentException(
                'Config must be either an array or \Phalcon\Config instance'
            );
        }

        if (isset($config['default_image'])) {
            $this->setDefaultImage(
                $config['default_image']
            );
        }

        if (isset($config['rating'])) {
            $this->setRating(
                $config['rating']
            );
        }

        if (isset($config['size'])) {
            $this->setSize(
                $config['size']
            );
        }

        if (isset($config['use_https']) && $config['use_https']) {
            $this->enableSecureURL();
        }
    }

    /**
     * {@inheritdoc}
     *
     * Possible $image formats:
     * - a string specifying a recognized gravatar "default"
     * - a string containing a valid image URL
     * - boolean false for the gravatar default
     *
     * @param mixed $image The default image to use
     * @return Gravatar
     *
     * @throws InvalidArgumentException
     */
    public function setDefaultImage($image)
    {
        if (false === $image) {
            $this->defaultImage = false;

            return $this;
        }

        $default = strtolower(trim($image));

        if (!isset($this->validDefaults[$default])) {
            if (!filter_var($image, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED)) {
                throw new InvalidArgumentException(
                    'The default image specified is not a recognized gravatar "default" and is not a valid URL'
                );
            } else {
                $this->defaultImage = $image;
            }
        } else {
            $this->defaultImage = $default;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed
     */
    public function getDefaultImage()
    {
        return $this->defaultImage;
    }

    /**
     * {@inheritdoc}
     *
     * By default, images from Gravatar.com will be returned as 80x80px
     *
     * @param int $size The avatar size to use
     * @return Gravatar
     *
     * @throws InvalidArgumentException
     */
    public function setSize($size)
    {
        $options = [
            'options' => [
                'min_range' => static::MIN_AVATAR_SIZE,
                'max_range' => static::MAX_AVATAR_SIZE,
            ]
        ];

        if (false === filter_var($size, FILTER_VALIDATE_INT, $options)) {
            throw new InvalidArgumentException(
                sprintf(
                    "Can't set Gravatar size. Size must be an integer within %s and %s pixels",
                    static::MIN_AVATAR_SIZE,
                    static::MAX_AVATAR_SIZE
                )
            );
        }

        $this->size = (int) $size;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set the maximum allowed rating for avatars
     *
     * @param string $rating The maximum rating to use for avatars ('g', 'pg', 'r', 'x')
     * @return Gravatar
     *
     * @throws InvalidArgumentException
     */
    public function setRating($rating)
    {
        $rating = strtolower(trim($rating));

        if (!isset($this->validRatings[$rating])) {
            $allowed = array_keys($this->validRatings);
            $last    = array_pop($allowed);
            $allowed = join(',', $allowed);

            throw new InvalidArgumentException(
                sprintf(
                    "Invalid rating '%s' specified. Available for use only: %s or %s",
                    $rating,
                    $allowed,
                    $last
                )
            );
        }

        $this->rating = $rating;

        return $this;
    }

    /**
     * Get the current maximum allowed rating for avatars
     *
     * The string representing the current maximum allowed rating ('g', 'pg', 'r', 'x').
     *
     * @return int
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Check if we are using the secure protocol for the image URLs
     *
     * @return bool
     */
    public function isUseSecureURL()
    {
        return $this->secureURL;
    }

    /**
     * Enable the use of the secure protocol for image URLs
     *
     * @return Gravatar
     */
    public function enableSecureURL()
    {
        $this->secureURL = true;

        return $this;
    }

    /**
     * Disable the use of the secure protocol for image URLs
     *
     * @return Gravatar
     */
    public function disableSecureURL()
    {
        $this->secureURL = false;

        return $this;
    }

    /**
     * Get the email hash to use
     *
     * @param string $email The email to get the hash for
     * @return string
     */
    public function getEmailHash($email)
    {
        return md5(strtolower(trim($email)));
    }

    /**
     * Forces Gravatar to display default image
     *
     * @return Gravatar
     */
    public function enableForceDefault()
    {
        $this->forceDefault = true;

        return $this;
    }

    /**
     * Disable forces default image
     *
     * @return Gravatar
     */
    public function disableForceDefault()
    {
        $this->forceDefault = false;

        return $this;
    }

    /**
     * Check if need to force the default image to always load
     *
     * @return bool
     */
    public function isUseForceDefault()
    {
        return $this->forceDefault;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $identity The email to get the gravatar for
     * @return string
     */
    public function getAvatar($identity)
    {
        return $this->buildURL($identity);
    }

    /**
     * Build the Gravatar URL based on the configuration and provided email address
     *
     * @param string $email The email to get the gravatar for
     * @return string
     */
    protected function buildURL($email)
    {
        $url = static::HTTP_URL;

        if ($this->secureURL) {
            $url = static::HTTPS_URL;
        }

        $url .= $this->getEmailHash($email);

        $query = [
            's' => $this->getSize(),
            'r' => $this->getRating(),
        ];

        if ($this->defaultImage) {
            $query = array_merge(
                $query,
                [
                    'd' => $this->defaultImage,
                ]
            );
        }

        if ($this->forceDefault) {
            $query = array_merge(
                $query,
                [
                    'f' => 'y',
                ]
            );
        }

        $url .= '?' . http_build_query($query, '', '&');

        return $url;
    }
}
