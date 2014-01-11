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
  +------------------------------------------------------------------------+
  | Authors: Andres Gutierrez <andres@phalconphp.com>                      |
  |          Eduar Carvajal <eduar@phalconphp.com>                         |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Translate\Adapter;

use Phalcon\Translate\Adapter,
    Phalcon\Translate\AdapterInterface,
    Phalcon\Translate\Exception;

class Gettext extends Adapter implements AdapterInterface
{

    /**
     * _domains
     *
     * @var array
     * @access private
     */
    private $_domains = array();

    /**
     * _defaultDomain
     *
     * @var string
     * @access private
     */
    private $_defaultDomain;

    /**
     * Phalcon\Translate\Adapter\Gettext constructor
     *
     * @param array $options    Required options: (string) locale, (string|array) file, (string) directory.
     */
    public function __construct($options)
    {

        if (!is_array($options)) {
            throw new Exception('Invalid options');
        }

        if (!isset($options['locale'])) {
            throw new Exception('Parameter "locale" is required');
        }

        if (!isset($options['file'])) {
            throw new Exception('Parameter "file" is required');
        }

        if (!isset($options['directory'])) {
            throw new Exception('Parameter "directory" is required');
        }

        putenv("LC_ALL=" . $options['locale']);
        setlocale(LC_ALL, $options['locale']);
        if (is_array($options['file'])) {
            foreach ($options['file'] as $file) {
                bindtextdomain($file, $options['directory']);
            }
            // set the first domain as default
            $this->_defaultDomain = reset($options['file']);
            $this->_domains = $options['file'];
        } else {
            bindtextdomain($options['file'], $options['directory']);
            $this->_defaultDomain = $options['file'];
            $this->_domains = array($options['file']);
        }
        textdomain($this->_defaultDomain);
    }

    /**
     * Returns the translation related to the given key
     *
     * @param    string $index
     * @param    array  $placeholders
     * @param    string $domain
     * @return    string
     */
    public function query($index, $placeholders = null, $domain = null)
    {
        if ($domain === null) {
            return gettext($index);
        }

        $translation = dgettext($domain, $index);
        if (is_array($placeholders)) {
            foreach ($placeholders as $key => $value) {
                $translation = str_replace('%' . $key . '%', $value, $translation);
            }
        }

        return $translation;
    }

    /**
     * Returns the translation related to the given key and context (msgctxt).
     *
     * @param    string $msgid
     * @param    string $msgctxt        Optional. If ommitted or NULL, this method behaves as query().
     * @param    array  $placeholders   Optional.
     * @param    string $category       Optional. Specify the locale category. Defaults to LC_MESSAGES
     * @return    string
     */
    public function cquery($msgid, $msgctxt = null, $placeholders = null, $category = LC_MESSAGES, $domain = NULL)
    {
        if ($domain !== NULL && !in_array($domain, $this->_domains)) {
            throw new \InvalidArgumentException($domain . ' is invalid translation domain');
        }
        if ($msgctxt === null) {
            return $this->query($msgid, $placeholders, $domain);
        }

        if ($domain === NULL) {
            $domain = textdomain(NULL);
        }
        $contextString = "{$msgctxt}\004{$msgid}";
        $translation = dcgettext($domain, $contextString, $category);
        if ($translation == $contextString)  {
            $translation = $msgid;
        }
        if (is_array($placeholders)) {
            foreach ($placeholders as $key => $value) {
                $translation = str_replace('%' . $key . '%', $value, $translation);
            }
        }
        return $translation;
    }

    /**
     * Returns the translation related to the given key and context (msgctxt).
     * This is an alias to cquery().
     *
     * @param    string $msgid
     * @param    string $msgctxt        Optional.
     * @param    array  $placeholders   Optional.
     * @param    string $category       Optional. Specify the locale category. Defaults to LC_MESSAGES
     * @return    string
     */
    public function __($msgid, $msgctxt = null, $placeholders = null, $category = LC_MESSAGES)
    {
        return $this->cquery($msgid, $msgctxt, $placeholders, $category);
    }

    /**
     * Returns the translation related to the given key and context (msgctxt) from a specific domain.
     *
     * @param    string $domain
     * @param    string $msgid
     * @param    string $msgctxt        Optional.
     * @param    array  $placeholders   Optional.
     * @param    string $category       Optional. Specify the locale category. Defaults to LC_MESSAGES
     * @return    string
     */
    public function dquery($domain, $msgid, $msgctxt = null, $placeholders = null, $category = LC_MESSAGES)
    {
        return $this->cquery($msgid, $msgctxt, $placeholders, $category, $domain);
    }

    /**
     * Check whether is defined a translation key in gettext
     *
     * @param    string $index
     * @return    bool
     */
    public function exists($index)
    {
        return gettext($index) !== '';
    }

    /**
     * setDomain
     *
     * Changes the current domain (i.e. the translation file). The passed domain must be one
     * of those passed to the constructor.
     *
     * @param string $domain
     * @access public
     * @return string   Returns the new current domain.
     */
    public function setDomain($domain)
    {
        if (!in_array($domain, $this->_domains)) {
            throw new \InvalidArgumentException($domain . ' is invalid translation domain');
        }
        return textdomain($domain);
    }

    /**
     * resetDomain
     *
     * Sets the default domain. The default domain is the first item in the array of domains
     * passed tot he constructor. Obviously, this method is irrelevant if Gettext was configured with a single
     * domain only.
     *
     * @access public
     * @return string   Returns the new current domain.
     */
    public function resetDomain()
    {
        return textdomain($this->_defaultDomain);
    }


}
