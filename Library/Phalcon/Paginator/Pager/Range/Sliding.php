<?php
/**
 * Phalcon Framework
 * This source file is subject to the New BSD License that is bundled
 * with this package in the file docs/LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@phalconphp.com so we can send you a copy immediately.
 *
 * @author Nikita Vershinin <endeveit@gmail.com>
 */
namespace Phalcon\Paginator\Pager\Range;

use Phalcon\Paginator\Pager\Range;

/**
 * \Phalcon\Paginator\Pager\Range\Sliding
 * «Smooth» ranges, e.g.: when on
 *  [1] [2] 3
 *  next range will be:
 *  [3] 4 [5]
 */
class Sliding extends Range
{

    /**
     * {@inheritdoc}
     * @return array
     */
    public function getRange()
    {
        $page = $this->pager->getCurrentPage();
        $pages = $this->pager->getLastPage();

        $chunk = $this->chunkLength;

        if ($chunk > $pages) {
            $chunk = $pages;
        }

        $chunkStart = $page - (floor($chunk / 2));
        $chunkEnd = $page + (ceil($chunk / 2) - 1);

        if ($chunkStart < 1) {
            $adjust = 1 - $chunkStart;
            $chunkStart = 1;
            $chunkEnd = $chunkEnd + $adjust;
        }

        if ($chunkEnd > $pages) {
            $adjust = $chunkEnd - $pages;
            $chunkStart = $chunkStart - $adjust;
            $chunkEnd = $pages;
        }

        return range($chunkStart, $chunkEnd);
    }

}
