<?php

namespace Phalcon\Mvc\Model\Behavior;

abstract class Behavior
{
	protected $_options = array();

	public function setOptions($options)
	{
		if (is_array($options)) {
			foreach ($options as $option => $value) {
				$this->_options[$option] = $value;
			}
		}
	}

	public function getOptions()
	{
		return $this->_options;
	}

	public function getOption($option)
	{
		return isset($this->_options[$option]) ? $this->_options[$option] : null;
	}
}
