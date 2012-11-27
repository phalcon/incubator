<?php

namespace Phalcon\Mvc\Model\Behavior;

class ActAs extends \Phalcon\Mvc\Model
{
	protected $_actAs = array();

	public function initialize()
	{
		if (!is_array($this->_actAs)) {
			throw new \Phalcon\Mvc\Model\BehaviorException("Property _actAs has to be an array");
		}

		foreach ($this->_actAs as $behavior => $options) {
			if (is_numeric($behavior)) {
				$behavior = $options;
			}

			switch (strtolower($behavior)) {
				case 'timestampable':
					$listener = new \Phalcon\Model\Behavior\Timestampable();
					break;
				default:
					throw new \Phalcon\Mvc\Model\BehaviorException("Behavior '$behavior' not supported");
					break;
			}

			$listener->setOptions($options);
			$this->_attach($listener);
		}
	}

	protected function _attach($listener)
	{
		$manager = $this->getEventsManager();
		$manager->attach('model', $listener);
		$this->setEventsManager($manager);
	}

}
