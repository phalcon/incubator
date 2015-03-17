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

class Gettext extends Base implements AdapterInterface
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

        putenv("LC_ALL=" . $options['locale']);
        setlocale(LC_ALL, $options['locale']);

        $this->prepareOptions($options);

        textdomain($this->defaultDomain);
    }

    /**
     * Validator for constructor
     *
     * @param array $options
     *
     */
    protected function prepareOptions($options)
    {
        if (isset($options['domains'])) {
            $this->prepareOptionsWithDomain($options);
        } else {
            $this->prepareOptionsWithoutDomain($options);
        }
    }

    /**
     * Validator for gettext with domains
     *
     * @param array $options
     *
     * @throws \Phalcon\Translate\Exception
     */
    private function prepareOptionsWithDomain($options)
    {
        if (!is_array($options['domains'])) {
            throw new Exception('Parameter "domains" must be an array.');
        }
        unset($options['file']);
        unset($options['directory']);

        foreach ($options['domains'] as $domain => $dir) {
            bindtextdomain($domain, $dir);
        }
        // set the first domain as default
        reset($options['domains']);
        $this->defaultDomain = key($options['domains']);
        // save list of domains
        $this->domains = array_keys($options['domains']);
    }

    /**
     * Validator for gettext without domains
     *
     * @param array $options
     *
     * @throws \Phalcon\Translate\Exception
     */
    private function prepareOptionsWithoutDomain($options)
    {
        self::validateOptionsWithoutDomain($options);
        if (!is_array($options['file'])) {
            $options['file'] = array($options['file']);
        }

        foreach ($options['file'] as $domain) {
            bindtextdomain($domain, $options['directory']);
        }

        // set the first domain as default
        $this->defaultDomain = reset($options['file']);
        $this->domains = $options['file'];

        return $options;
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

        return self::setPlaceholders($translation, $placeholders);
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

        return self::setPlaceholders($translation, $placeholders);
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

        return self::setPlaceholders($translation, $placeholders);
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

        $context = "{$msgctxt}\004";
        $translation = dcngettext($domain, $context . $msgid1, $context . $msgid2, $count, $category);

        if (strpos($translation, $context, 0) === 0) {
            $translation = substr($translation, strlen($context));
        }

        return self::setPlaceholders($translation, $placeholders);
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

    /**
     * Validate required fields in $options
     *
     * @access public
     * @throws \InvalidArgumentException
     */
    public static function validateOptionsWithoutDomain($options)
    {
        if (!isset($options['file'], $options['directory'])) {
            throw new  \InvalidArgumentException('Parameters "file" and "directory" are required.');
        }
    }
}
