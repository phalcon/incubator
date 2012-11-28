<?php

namespace Phalcon\Cache\Backend;

use Phalcon\Cache\Backend,
	Phalcon\Cache\BackendInterface,
	Phalcon\Cache\Exception;

class Redis extends Backend implements BackendInterface
{

	/**
	 * Phalcon\Cache\Backend\Redis constructor
	 *
	 * @param Phalcon\Cache\FrontendInterface $frontend
	 * @param array $options
	 */
	public function __construct($frontend, $options=null)
	{
		if (!isset($options['redis'])) {
			throw new Exception("Parameter 'redis' is required");
		}

		parent::__construct($frontend, $options);
	}

	/**
	 * Get a cached content from the
	 *
	 * @param string $keyName
	 * @param int $lifetime
	 */
	public function get($keyName, $lifetime=null)
	{
		$options = $this->getOptions();

		$value = $options['redis']->get($keyName);
		if ($value===false) {
			return null;
		}

		$frontend = $this->getFrontend();

		$this->setLastKey($keyName);

		return $frontend->afterRetrieve($value);
	}

	/**
	 * Stores cached content into the APC backend and stops the frontend
	 *
	 * @param string $keyName
	 * @param string $content
	 * @param long $lifetime
	 * @param boolean $stopBuffer
	 */
	public function save($keyName=null, $content=null, $lifetime=null, $stopBuffer=true)
	{

		if ($keyName===null) {
			$lastKey = $this->_lastKey;
		} else {
			$lastKey = $keyName;
		}

		if (!$lastKey) {
			throw new Exception('The cache must be started first');
		}

		$options = $this->getOptions();
		$frontend = $this->getFrontend();

		if ($content===null) {
			$content = $frontend->getContent();
		}

		//Get the lifetime from the frontend
		if ($lifetime===null) {
			$lifetime = $frontend->getLifetime();
		}

		$options['redis']->setex($lastKey, $lifetime, $frontend->beforeStore($content));

		//Stop the buffer, this only applies for Phalcon\Cache\Frontend\Output
		if ($stopBuffer) {
			$frontend->stop();
		}

		//Print the buffer, this only applies for Phalcon\Cache\Frontend\Output
		if ($frontend->isBuffering()) {
			echo $content;
		}

		$this->_started = false;
	}

	/**
	 * Deletes a value from the cache by its key
	 *
	 * @param string $keyName
	 * @return boolean
	 */
	public function delete($keyName){
		$options = $this->getOptions();
		return $options['redis']->delete($keyName) > 0;
	}

	/**
	 * Query the existing cached keys
	 *
	 * @param string $prefix
	 * @return array
	 */
	public function queryKeys($prefix=null){
		$options = $this->getOptions();
		if ($prefix === null) {
			return $options['redis']->getKeys('*');
		} else {
			return $options['redis']->getKeys($prefix.'*');
		}
	}

	/**
	 * Checks if cache exists.
	 *
	 * @param string $keyName
	 * @param string $lifetime
	 * @return boolean
	 */
	public function exists($keyName=null, $lifetime=null){
		$options = $this->getOptions();
		return $options['redis']->exists($keyName);
	}

}