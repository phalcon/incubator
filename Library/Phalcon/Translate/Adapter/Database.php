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

use Phalcon\Translate\Adapter;
use Phalcon\Translate\AdapterInterface;
use Phalcon\Translate\Exception;

class Database extends Adapter implements AdapterInterface
{

    /**
     * @var array
     */
    protected $options;

    /**
     * Class constructor.
     *
     * @param  array                        $options
     * @throws \Phalcon\Translate\Exception
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

        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $index
     * @param  array  $placeholders
     * @return string
     */
    public function query($index, $placeholders = null)
    {
        $options = $this->options;

        $translation = $options['db']->fetchOne(
            sprintf(
                "SELECT value FROM %s WHERE language = '%s' AND key_name = ?",
                $options['table'],
                $options['language']
            ),
            null,
            array($index)
        );

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
     * {@inheritdoc}
     *
     * @param  string  $index
     * @return boolean
     */
    public function exists($index)
    {
        $options = $this->options;

        $exists = $options['db']->fetchOne(
            "SELECT COUNT(*) FROM " . $options['table'] . " WHERE key_name = ?0",
            null,
            array($index)
        );

        return $exists[0] > 0;
    }
}
