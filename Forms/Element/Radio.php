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
  |          Nikita Vershinin <endeveit@gmail.com>                         |
  |          Ludomir Crotet <lcrotet@voyageprive.comm>                     |
  |          Olivier Garbé <ogarbe@voyageprive.com>                        |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Forms\Element {

	/**
	 * Phalcon\Forms\Element\Radio
	 *
	 * Component INPUT[type=radio] for forms
	 */

	class Radio extends \Phalcon\Forms\Element implements \Phalcon\Forms\ElementInterface {

		/**
		 * \Phalcon\Forms\Element constructor
		 *
		 * @param string $name
		 * @param array $attributes
		 */
		public function __construct($name, $attributes=null) {
            parent::__construct($name, $attributes);
        }

		/**
		 * Renders the element widget returning html
		 *
		 * @param array $attributes
		 * @return string
		 */
		public function render($attributes = null){
            $render = '';
            foreach ($attributes->set->items as $key => $item) {
                $render .= \Phalcon\Tag::radioField(array($this->getName(), 'value' => $key)).'&nbsp;'.$item.'&nbsp;';
            }
            return $render;
        }
	}
}
