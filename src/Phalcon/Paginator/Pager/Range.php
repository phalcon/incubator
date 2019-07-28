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
 * \Phalcon\Paginator\Pager\Range
 * Base class for ranges objects.
 */
abstract class Range
{
    /**
     * Pager object.
     *
     * @var \Phalcon\Paginator\Pager
     */
    protected $pager = null;

    /**
     * Window size.
     *
     * @var integer
     */
    protected $chunkLength = 0;

    /**
     * Class constructor.
     *
     * @param \Phalcon\Paginator\Pager $pager
     * @param integer                  $chunkLength
     */
    public function __construct(Pager $pager, $chunkLength)
    {
        $this->pager = $pager;

        $this->chunkLength = abs(
            intval($chunkLength)
        );

        if ($this->chunkLength == 0) {
            $this->chunkLength = 1;
        }
    }

    /**
     * Calculate and returns an array representing the range around the current page.
     *
     * @return array
     */
    abstract public function getRange();
}
