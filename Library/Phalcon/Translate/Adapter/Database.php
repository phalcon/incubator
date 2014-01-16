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
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Translate\Adapter;

use Phalcon\Translate\Adapter,
	Phalcon\Translate\AdapterInterface,
	Phalcon\Translate\Exception;

class Database extends Adapter implements AdapterInterface
{

	protected $_options;

	/**
	 * Phalcon\Translate\Adapter\Database constructor
	 *
	 * @param array $options
	 */
	public function __construct($options)
	{

		if (!isset($options['db'])) {
			throw new Exception("Parameter 'db' is required");
		}

		if (!isset($options['table'])) {
			throw new Exception("Parameter 'table' is required");
		}
		
                if (!isset($options['language'])) {
                    throw new Exception("Parameter 'language' is required");
                }

		$this->_options = $options;
	}

	/**
	 * Returns the translation related to the given key
	 *
	 * @param	string $index
	 * @param	array $placeholders
	 * @return	string
	 */
	public function query($index, $placeholders=null)
	{

		$options = $this->_options;

		$translation = $options['db']->fetchOne("SELECT value FROM " . $options['table'] . " WHERE language = '" . $options['language'] . "' AND key_name = ?", null, array($index));
		if (!$translation) {
			return $index;
		}

		if ($placeholders == null) {
			return $translation['value'];
		}

		if (is_array($placeholders)) {
			foreach ($placeholders as $key => $value) {
				$translation['value'] = str_replace('%' . $key . '%', $value, $translation['value']);
			}
		}

		return $translation['value'];
	}

	/**
	 * Check whether is defined a translation key in the database
	 *
	 * @param 	string $index
	 * @return	bool
	 */
	public function exists($index)
	{
		$options = $this->_options;

		$exists = $options['db']->fetchOne("SELECT COUNT(*) FROM " . $options['table'] . " WHERE key_name = ?", null, array($index));
		return $exists[0] > 0;
	}

}
