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
     * @param array $options         Required options:
     *                               (string) locale
     *                               (string|array) file
     *                               (string) directory
     *                               ~ or ~
     *                               (array) domains (instead of file and directory),
     *                               where keys are domain names and
     *                               values their respective directories.
     *
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

        if (isset($options['domains'])) {
            unset($options['file']);
            unset($options['directory']);
        }

        if (!isset($options['domains']) && !isset($options['file'])) {
            throw new Exception('Option "file" is required unless "domains" is specified.');
        }

        if (!isset($options['domains']) && !isset($options['directory'])) {
            throw new Exception('Option "directory" is required unless "domains" is specified.');
        }

        if (isset($options['domains']) && !is_array($options['domains'])) {
            throw new Exception('If the option "domains" is specified it must be an array.');
        }

        putenv("LC_ALL=" . $options['locale']);
        setlocale(LC_ALL, $options['locale']);

        if (isset($options['domains'])) {
            foreach ($options['domains'] as $domain => $dir) {
                bindtextdomain($domain, $dir);
            }
            // set the first domain as default
            reset($options['domains']);
            $this->defaultDomain = key($options['domains']);
            // save list of domains
            $this->domains = array_keys($options['domains']);

        } else {
            if (is_array($options['file'])) {
                foreach ($options['file'] as $domain) {
                    bindtextdomain($domain, $options['directory']);
                }

                // set the first domain as default
                $this->defaultDomain = reset($options['file']);
                $this->domains = $options['file'];
            } else {
                bindtextdomain($options['file'], $options['directory']);
                $this->defaultDomain = $options['file'];
                $this->domains = array($options['file']);
            }
        }

        textdomain($this->defaultDomain);
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $index
     * @param  array $placeholders
     * @param  string $domain
     *
     * @return string
     */
    public function query($index, $placeholders = null, $domain = null)
    {
        if ($domain === null) {
            $translation = gettext($index);
        } else {
            $translation = dgettext($domain, $index);
        }

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
     * @param  string $msgid
     * @param  string $msgctxt     Optional. If ommitted or NULL,
     *                             this method behaves as query().
     * @param  array $placeholders Optional.
     * @param  string $category    Optional. Specify the locale category.
     *                             Defaults to LC_MESSAGES
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function cquery($msgid, $msgctxt = null, $placeholders = null, $category = LC_MESSAGES, $domain = null)
    {
        if ($msgctxt === null) {
            return $this->query($msgid, $placeholders, $domain);
        }

        $this->setDomain($domain);

        $contextString = "{$msgctxt}\004{$msgid}";
        $translation = dcgettext($domain, $contextString, $category);

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
     * @param  string $msgid
     * @param  string $msgctxt     Optional.
     * @param  array $placeholders Optional.
     * @param  integer $category   Optional. Specify the locale category.
     *                             Defaults to LC_MESSAGES
     *
     * @return string
     */
    // @codingStandardsIgnoreStart
    public function __($msgid, $msgctxt = null, $placeholders = null, $category = LC_MESSAGES)
    {
        return $this->cquery($msgid, $msgctxt, $placeholders, $category);
    }
    // @codingStandardsIgnoreEnd

    /**
     * Returns the translation related to the given key
     * and context (msgctxt) from a specific domain.
     *
     * @param  string $domain
     * @param  string $msgid
     * @param  string $msgctxt     Optional.
     * @param  array $placeholders Optional.
     * @param  integer $category   Optional. Specify the locale category. Defaults to LC_MESSAGES
     *
     * @return string
     */
    public function dquery($domain, $msgid, $msgctxt = null, $placeholders = null, $category = LC_MESSAGES)
    {
        return $this->cquery($msgid, $msgctxt, $placeholders, $category, $domain);
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $msgid1
     * @param  string $msgid2
     * @param  integer $count
     * @param  array $placeholders
     * @param  string $domain
     *
     * @return string
     */
    public function nquery($msgid1, $msgid2, $count, $placeholders = null, $domain = null)
    {
        self::validateCount($count);
        if ($domain === null) {
            $translation = ngettext($msgid1, $msgid2, $count);
        } else {
            $translation = dngettext($domain, $msgid1, $msgid2, $count);
        }

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
     * @param  string $msgid1
     * @param  string $msgid2
     * @param  integer $count
     * @param  string $msgctxt     Optional. If ommitted or NULL, this method behaves as nquery().
     * @param  array $placeholders Optional.
     * @param  string $category    Optional. Specify the locale category. Defaults to LC_MESSAGES
     * @param  string $domain      Optional.
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function cnquery(
        $msgid1,
        $msgid2,
        $count,
        $msgctxt = null,
        $placeholders = null,
        $category = LC_MESSAGES,
        $domain = null
    ) {
        self::validateCount($count);
        if ($msgctxt === null) {
            return $this->nquery($msgid1, $msgid2, $count, $placeholders, $domain);
        }

        $this->setDomain($domain);

        $contextString1 = "{$msgctxt}\004{$msgid1}";
        $contextString2 = "{$msgctxt}\004{$msgid2}";
        $translation = dcngettext($domain, $contextString1, $contextString2, $count, $category);

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
     * Returns the translation related to the given key and context (msgctxt)
     * from a specific domain with plural form support.
     *
     * @param  string $domain
     * @param  string $msgid1
     * @param  string $msgid2
     * @param  integer $count
     * @param  string $msgctxt     Optional.
     * @param  array $placeholders Optional.
     * @param  integer $category   Optional. Specify the locale category. Defaults to LC_MESSAGES
     *
     * @return string
     */
    public function dnquery(
        $domain,
        $msgid1,
        $msgid2,
        $count,
        $msgctxt = null,
        $placeholders = null,
        $category = LC_MESSAGES
    ) {
        return $this->cnquery($msgid1, $msgid2, $count, $msgctxt, $placeholders, $category, $domain);
    }


    /**
     * {@inheritdoc}
     *
     * @param  string $index
     *
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
     * @param  string $domain
     *
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

    /**
     * Count parameter validation
     *
     * @access public
     * @throws \InvalidArgumentException
     */
    public static function validateCount($count)
    {
        if (!is_int($count) || $count < 0) {
            throw new \InvalidArgumentException("Count must be a nonnegative integer. $count given.");
        }
    }
}
