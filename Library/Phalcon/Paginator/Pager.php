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
namespace Phalcon\Paginator;

use Phalcon\Paginator\AdapterInterface;

/**
 * \Phalcon\Paginator\Pager
 *
 * Pager object is a navigation menu renderer based on doctrine1 pager object.
 * @link https://github.com/doctrine/doctrine1/blob/master/lib/Doctrine/Pager.php
 */
class Pager implements \IteratorAggregate, \Countable
{

	/**
	 * Phalcon's paginate result.
	 *
	 * @var \stdClass
	 */
	protected $paginateResult = null;

	/**
	 * Object that detects pages range.
	 *
	 * @var \Phalcon\Paginator\Range
	 */
	protected $range = null;

	/**
	 * Array with options.
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * Class constructor.
	 *
	 * @param \Phalcon\Paginator\AdapterInterface $adapter
	 * @param array $options {
	 * @type string $rangeType How to make the range: Jumping or Sliding
	 * @type integer $rangeChunkLength Range window size
	 * }
	 */
	public function __construct(AdapterInterface $adapter, array $options = array())
	{
		$this->paginateResult = $adapter->getPaginate();
		$this->options = $options;
	}

	/**
	 * Return true if it's necessary to paginate or false if not.
	 *
	 * @return boolean
	 */
	public function haveToPaginate()
	{
		return $this->paginateResult->total_pages > 1;
	}

	/**
	 * Returns the current page.
	 *
	 * @return integer
	 */
	public function getCurrentPage()
	{
		return $this->paginateResult->current;
	}

	/**
	 * Returns the first page.
	 *
	 * @return integer
	 */
	public function getFirstPage()
	{
		return $this->paginateResult->first;
	}

	/**
	 * Returns the previous page.
	 *
	 * @return integer
	 */
	public function getPreviousPage()
	{
		return $this->paginateResult->before;
	}

	/**
	 * Returns the next page.
	 *
	 * @return integer
	 */
	public function getNextPage()
	{
		return $this->paginateResult->next;
	}

	/**
	 * Returns the last page.
	 *
	 * @return integer
	 */
	public function getLastPage()
	{
		return $this->paginateResult->last;
	}

	/**
	 * Returns the layout object.
	 *
	 * @return \Phalcon\Paginator\Layout
	 * @throws \RuntimeException
	 */
	public function getLayout()
	{
		if (!array_key_exists('rangeClass', $this->options)) {
			$this->options['rangeClass'] = 'Phalcon\Paginator\Pager\Range\Sliding';
		}

		if (!array_key_exists('rangeLength', $this->options)) {
			$this->options['rangeLength'] = 10;
		}

		if (!array_key_exists('layoutClass', $this->options)) {
			$this->options['layoutClass'] = 'Phalcon\Paginator\Pager\Layout';
		}

		if (!array_key_exists('urlMask', $this->options)) {
			throw new \RuntimeException('You must provide option "urlMask"');
		}

		$range = null;
		try {
			$range = new $this->options['rangeClass']($this, $this->options['rangeLength']);
		} catch (\Exception $e) {
			throw new \RuntimeException(sprintf('Unable to find range class "%s"', $this->options['rangeClass']));
		}

		$layout = null;
		try {
			$layout = new $this->options['layoutClass']($this, $range, $this->options['urlMask']);
		} catch (\Exception $e) {
			throw new \RuntimeException(sprintf('Unable to find layout "%s"', $this->options['layoutClass']));
		}

		return $layout;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		if (!$this->paginateResult->items instanceof \Iterator) {
			return new \ArrayIterator($this->paginateResult->items);
		}

		return $this->paginateResult->items;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return integer
	 */
	public function count()
	{
		return intval($this->paginateResult->total_items);
	}

}
