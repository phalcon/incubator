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

use Phalcon\Translate\Adapter;
use Phalcon\Translate\AdapterInterface;
use Phalcon\Translate\Exception;

class Gettext extends Adapter implements AdapterInterface
{

    /**
     * @var array
     */
    protected $domains = array();

    /**
     * @var string
     */
    protected $defaultDomain;

    /**
     * Class constructor.
     *
     * @param array $options Required options: (string) locale,
     *                       (string|array) file, (string) directory.
     * @throws \Phalcon\Translate\Exception
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
            $this->defaultDomain = reset($options['file']);
            $this->domains = $options['file'];
        } else {
            bindtextdomain($options['file'], $options['directory']);
            $this->defaultDomain = $options['file'];
            $this->domains = array($options['file']);
        }

        textdomain($this->defaultDomain);
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $index
     * @param  array  $placeholders
     * @param  string $domain
     * @return string
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
     * {@inheritdoc}
     *
     * @param  string                    $msgid
     * @param  string                    $msgctxt      Optional. If ommitted or NULL, this method behaves as query().
     * @param  array                     $placeholders Optional.
     * @param  string                    $category     Optional. Specify the locale category. Defaults to LC_MESSAGES
     * @return string
     * @throws \InvalidArgumentException
     */
    public function cquery($msgid, $msgctxt = null, $placeholders = null, $category = LC_MESSAGES, $domain = null)
    {
        if ($domain !== null && !in_array($domain, $this->domains)) {
            throw new \InvalidArgumentException($domain . ' is invalid translation domain');
        }
        if ($msgctxt === null) {
            return $this->query($msgid, $placeholders, $domain);
        }

        if ($domain === null) {
            $domain = textdomain(null);
        }

        $contextString = "{$msgctxt}\004{$msgid}";
        $translation   = dcgettext($domain, $contextString, $category);

        if ($translation == $contextString) {
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
     * @param  string  $msgid
     * @param  string  $msgctxt      Optional.
     * @param  array   $placeholders Optional.
     * @param  integer $category     Optional. Specify the locale category. Defaults to LC_MESSAGES
     * @return string
     */
    // @codingStandardsIgnoreStart
    public function __($msgid, $msgctxt = null, $placeholders = null, $category = LC_MESSAGES)
    // @codingStandardsIgnoreEnd
    {
        return $this->cquery($msgid, $msgctxt, $placeholders, $category);
    }

    /**
     * Returns the translation related to the given key and context (msgctxt) from a specific domain.
     *
     * @param  string  $domain
     * @param  string  $msgid
     * @param  string  $msgctxt      Optional.
     * @param  array   $placeholders Optional.
     * @param  integer $category     Optional. Specify the locale category. Defaults to LC_MESSAGES
     * @return string
     */
    public function dquery($domain, $msgid, $msgctxt = null, $placeholders = null, $category = LC_MESSAGES)
    {
        return $this->cquery($msgid, $msgctxt, $placeholders, $category, $domain);
    }

    /**
     * {@inheritdoc}
     *
     * @param  string  $index
     * @return boolean
     */
    public function exists($index)
    {
        return gettext($index) !== '';
    }

    /**
     * Changes the current domain (i.e. the translation file). The passed domain must be one
     * of those passed to the constructor.
     *
     * @param  string                    $domain
     * @return string                    Returns the new current domain.
     * @throws \InvalidArgumentException
     */
    public function setDomain($domain)
    {
        if (!in_array($domain, $this->domains)) {
            throw new \InvalidArgumentException($domain . ' is invalid translation domain');
        }

        return textdomain($domain);
    }

    /**
     * Sets the default domain. The default domain is the first item in the array of domains
     * passed tot he constructor. Obviously, this method is irrelevant if Gettext was configured with a single
     * domain only.
     *
     * @access public
     * @return string Returns the new current domain.
     */
    public function resetDomain()
    {
        return textdomain($this->defaultDomain);
    }
}
