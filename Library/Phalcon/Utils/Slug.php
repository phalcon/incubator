<?php

/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2012 Phalcon Team (http://www.phalconphp.com)       |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file docs/LICENSE.txt.                        |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  |                                                                        |
  | The code below is inspired by Matteo Spinelli's (cubiq.org) blog post  |
  | http://cubiq.org/the-perfect-php-clean-url-generator                   |
  +------------------------------------------------------------------------+
  | Authors: Andres Gutierrez <andres@phalconphp.com>                      |
  |          Nikolaos Dimopoulos <nikos@niden.net>                         |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Utils;

class Slug
{
    /**
     * Creates a slug to be used for pretty URLs
     *
     * @link http://cubiq.org/the-perfect-php-clean-url-generator
     *
     * @param        $string
     * @param array  $replace
     * @param string $delimiter
     *
     * @return mixed
     */
    public static function generate($string, $replace = array(), $delimiter = '-')
    {

        if (!extension_loaded('iconv'))
        {
            throw new \Phalcon\Exception('iconv module not loaded');
        }

        // Save the old locale
        $oldLocale = setlocale(LC_ALL, 0);

        // Set the locale to UTF8
        setlocale(LC_ALL, 'en_US.UTF8');

        if( !empty($replace) )
        {
            $string = str_replace((array) $replace, ' ', $string);
        }

        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
        $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
        $clean = strtolower(trim($clean, '-'));
        $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

        // Revert back to the old locale
        setlocale(LC_ALL, $oldLocale);

        return $clean;
    }
}
