<?php

/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2017 Phalcon Team (https://www.phalconphp.com)      |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file LICENSE.txt.                             |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Sergii Svyrydenko <sergey.v.sviridenko@gmail.com>             |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Traits;

use Phalcon\Di;
use Phalcon\Events\Manager as EventsManager;

/**
 * Phalcon\Traits\EventManagerAwareTrait
 *
 * Trait for event processing
 *
 * @package Phalcon\Traits
 */

trait EventManagerAwareTrait
{
    /**
     * @var EventsManager
     */
    protected $eventsManager = null;

    /**
     * set event manager
     *
     * @param EventsManager $eventsManager
     */
    public function setEventsManager(EventsManager $manager)
    {
        $this->eventsManager = $manager;
    }

    /**
     * return event manager
     *
     * @return EventsManager | null
     */
    public function getEventsManager()
    {
        if (!empty($this->eventsManager)) {
            $manager =  $this->eventsManager;
        } elseif (Di::getDefault()->has('eventsManager')) {
            $manager = Di::getDefault()->get('eventsManager');
        }

        if (isset($manager) && $manager instanceof EventsManager) {
            return $manager;
        }

        return null;
    }

    /**
     * Checking if event manager is defined - fire event
     *
     * @param string $event
     * @param object $source
     * @param mixed $data
     * @param boolean $cancelable
     *
     */
    public function fire($event, $source, $data = null, $cancelable = true)
    {
        if ($manager = $this->getEventsManager()) {
            $manager->fire($event, $source, $data, $cancelable);
        }
    }
}
