<?php
/**
 * Phalcon Framework
 *
 * This source file is subject to the New BSD License that is bundled
 * with this package in the file docs/LICENSE.txt.
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@phalconphp.com so we can send you a copy immediately.
 *
 * @author Nikita Vershinin <endeveit@gmail.com>
 */
namespace Phalcon\Paginator\Pager\Range;

use Phalcon\Paginator\Pager\Range;

/**
 * \Phalcon\Paginator\Pager\Range\Jumping
 *
 * Ranges, «jumping» over the pages, e.g.: when on
 *  [1] [2] 3
 *  next range will be:
 *  4 [5] [6]
 */
class Jumping extends Range
{

	/**
	 * {@inheritdoc}
	 *
	 * @return array
	 */
	public function getRange()
	{
		$page = $this->pager->getCurrentPage();
		$startPage = $page - ($page - 1) % $this->chunkLength;
		$endPage = ($startPage + $this->chunkLength) - 1;

		if ($endPage > $this->pager->getLastPage()) {
			$endPage = $this->pager->getLastPage();
		}

		return range($startPage, $endPage);
	}

}
