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
  | Authors: Andres Gutierrez <andres@phalconphp.com>                      |
  |          Nikolaos Dimopoulos <nikos@niden.net>                         |
  |          Ilgıt Yıldırım <ilgityildirim@gmail.com>                      |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Utils;

use Phalcon\Exception;

/**
 * Slug component
 *
 * @package Phalcon\Utils
 */
class Slug
{
    /**
     * Creates a slug to be used for pretty URLs.
     *
     * @param  string $string
     * @param  array  $replace
     * @param  string $delimiter
     * @return string
     *
     * @throws \Phalcon\Exception
     */
    public static function generate($string, $replace = [], $delimiter = '-')
    {
        if (!extension_loaded('intl')) {
            throw new Exception('intl module not loaded');
        }

        // Save the old locale and set the new locale to UTF-8
        $oldLocale = setlocale(LC_ALL, '0');
        setlocale(LC_ALL, 'en_US.UTF-8');

        // Better to replace given $replace array as index => value
        // Example $replace['ı' => 'i', 'İ' => 'i'];
        if (!empty($replace) && is_array($replace)) {
            $string = str_replace(array_keys($replace), array_values($replace), $string);
        }

        $transliterator = \Transliterator::create('Any-Latin; Latin-ASCII');
        $string = $transliterator->transliterate(
            mb_convert_encoding(htmlspecialchars_decode($string), 'UTF-8', 'auto')
        );

        // replace non letter or non digits by -
        $string = preg_replace('#[^\pL\d]+#u', '-', $string);

        // Trim trailing -
        $string = trim($string, '-');

        $clean = preg_replace('~[^-\w]+~', '', $string);
        $clean = strtolower($clean);
        $clean = preg_replace('#[\/_|+ -]+#', $delimiter, $clean);
        $clean = trim($clean, $delimiter);

        // Revert back to the old locale

        if ((stripos($oldLocale, "=") > 0)) { // if $oldLocale is array
            parse_str(str_replace(';', '&', $oldLocale), $loc);
            $oldLocale = array_values($loc);
        }

        setlocale(LC_ALL, $oldLocale);

        return $clean;
    }
}
