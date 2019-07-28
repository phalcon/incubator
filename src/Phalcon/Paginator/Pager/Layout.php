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
  | Authors: Nikita Vershinin <endeveit@gmail.com>                         |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Paginator\Pager;

use Phalcon\Paginator\Pager;

/**
 * \Phalcon\Paginator\Pager\Layout
 * Pager base layout.
 */
class Layout
{
    /**
     * Pager object.
     *
     * @var Pager
     */
    protected $pager = null;

    /**
     * Ranges generator.
     *
     * @var Range
     */
    protected $range = null;

    /**
     * @link https://github.com/doctrine/doctrine1/blob/master/lib/Doctrine/Pager/Layout.php#L46
     * @var string
     */
    protected $template = '[<a href="{%url}">{%page}</a>]';

    /**
     * @link https://github.com/doctrine/doctrine1/blob/master/lib/Doctrine/Pager/Layout.php#L52
     * @var string
     */
    protected $selectedTemplate = '<strong> {%page} </strong>';

    /**
     * @link https://github.com/doctrine/doctrine1/blob/master/lib/Doctrine/Pager/Layout.php#L57
     * @var string
     */
    protected $separatorTemplate = '';

    /**
     * @link https://github.com/doctrine/doctrine1/blob/master/lib/Doctrine/Pager/Layout.php#L62
     * @var string
     */
    protected $urlMask;

    /**
     * @link https://github.com/doctrine/doctrine1/blob/master/lib/Doctrine/Pager/Layout.php#L67
     * @var array
     */
    protected $maskReplacements = [];

    /**
     * Class constructor.
     *
     * @param Pager $pager
     * @param Range $range
     * @param string                         $urlMask
     */
    public function __construct(Pager $pager, Range $range, $urlMask)
    {
        $this->pager = $pager;
        $this->range = $range;
        $this->urlMask = $urlMask;
    }

    /**
     * @link https://github.com/doctrine/doctrine1/blob/master/lib/Doctrine/Pager/Layout.php#L198
     * @param  string $template
     * @return void
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @link https://github.com/doctrine/doctrine1/blob/master/lib/Doctrine/Pager/Layout.php#L224
     * @param  string $selectedTemplate
     * @return void
     */
    public function setSelectedTemplate($selectedTemplate)
    {
        $this->selectedTemplate = $selectedTemplate;
    }

    /**
     * @link https://github.com/doctrine/doctrine1/blob/master/lib/Doctrine/Pager/Layout.php#L249
     * @param  string $separatorTemplate
     * @return void
     */
    public function setSeparatorTemplate($separatorTemplate)
    {
        $this->separatorTemplate = $separatorTemplate;
    }

    /**
     * @link https://github.com/doctrine/doctrine1/blob/master/lib/Doctrine/Pager/Layout.php#L262
     * @param  string  $oldMask
     * @param  string  $newMask
     * @param  boolean $asValue
     * @return void
     */
    public function addMaskReplacement($oldMask, $newMask, $asValue = false)
    {
        if (($oldMask = trim($oldMask)) != 'page_number') {
            $this->maskReplacements[$oldMask] = [
                'newMask' => $newMask,
                'asValue' => ($asValue !== false)
            ];
        }
    }

    /**
     * @link https://github.com/doctrine/doctrine1/blob/master/lib/Doctrine/Pager/Layout.php#L285
     * @param  string $oldMask
     * @return void
     */
    public function removeMaskReplacement($oldMask)
    {
        if (isset($this->maskReplacements[$oldMask])) {
            unset(
                $this->maskReplacements[$oldMask]
            );
        }
    }

    /**
     * Displays the pager on screen based on templates and options defined.
     *
     * @link https://github.com/doctrine/doctrine1/blob/master/lib/Doctrine/Pager/Layout.php#L315
     * @param  array  $options
     * @return string
     */
    public function getRendered(array $options = [])
    {
        $range = $this->range->getRange();
        $result = '';

        for ($i = 0, $l = count($range); $i < $l; $i++) {
            $options['page_number'] = $range[$i];

            $result .= $this->processPage($options);

            if ($i < $l - 1) {
                $result .= $this->separatorTemplate;
            }
        }

        return $result;
    }

    /**
     * Simply calls display, and returns the output.
     */
    public function __toString()
    {
        return $this->getRendered();
    }

    /**
     * @link https://github.com/doctrine/doctrine1/blob/master/lib/Doctrine/Pager/Layout.php#L353
     * @param  array  $options
     * @return string
     */
    protected function processPage(array $options = [])
    {
        if (!isset($this->maskReplacements['page']) && !isset($options['page'])) {
            $options['page'] = $options['page_number'];
        }

        return $this->parseTemplate($options);
    }

    /**
     * @link https://github.com/doctrine/doctrine1/blob/master/lib/Doctrine/Pager/Layout.php#L390
     * @param  array  $options
     * @return string
     */
    protected function parseTemplate(array $options = [])
    {
        $str = $this->parseUrlTemplate($options);
        $replacements = $this->parseReplacementsTemplate($options);

        return strtr($str, $replacements);
    }

    /**
     * @link https://github.com/doctrine/doctrine1/blob/master/lib/Doctrine/Pager/Layout.php#L406
     * @param  array  $options
     * @return string
     */
    protected function parseUrlTemplate(array $options = [])
    {
        $str = '';

        // If given page is the current active one
        if ($options['page_number'] == $this->pager->getCurrentPage()) {
            $str = $this->parseMaskReplacements(
                $this->selectedTemplate
            );
        }

        // Possible attempt where Selected == Template
        if ($str == '') {
            $str = $this->parseMaskReplacements(
                $this->template
            );
        }

        return $str;
    }

    /**
     * @link https://github.com/doctrine/doctrine1/blob/master/lib/Doctrine/Pager/Layout.php#L432
     * @param  array  $options
     * @return string
     */
    protected function parseReplacementsTemplate(array $options = [])
    {
        $options['url'] = $this->parseUrl($options);
        $replacements = [];

        foreach ($options as $k => $v) {
            $replacements['{%' . $k . '}'] = $v;
        }

        return $replacements;
    }

    /**
     * @link https://github.com/doctrine/doctrine1/blob/master/lib/Doctrine/Pager/Layout.php#L454
     * @param  array  $options
     * @return string
     */
    protected function parseUrl(array $options = [])
    {
        $str = $this->parseMaskReplacements($this->urlMask);

        $replacements = [];

        foreach ($options as $k => $v) {
            $replacements['{%' . $k . '}'] = $v;
        }

        return strtr($str, $replacements);
    }

    /**
     * @link https://github.com/doctrine/doctrine1/blob/master/lib/Doctrine/Pager/Layout.php#L475
     * @param  string $str
     * @return string
     */
    protected function parseMaskReplacements($str)
    {
        $replacements = [];

        foreach ($this->maskReplacements as $k => $v) {
            $replacements['{%' . $k . '}'] = ($v['asValue'] === true) ? $v['newMask'] : '{%' . $v['newMask'] . '}';
        }

        return strtr($str, $replacements);
    }
}
