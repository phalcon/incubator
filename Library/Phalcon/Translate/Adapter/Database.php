<?php
/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2016 Phalcon Team (http://www.phalconphp.com)       |
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

use Phalcon\Db;
use Phalcon\Translate\Adapter;
use Phalcon\Translate\AdapterInterface;
use Phalcon\Translate\Exception;

class Database extends Base implements AdapterInterface, \ArrayAccess
{
    /**
     * @var array
     */
    protected $options;

    /**
     * Statement for Exist
     * @var array
     */
    protected $stmtExists;

    /**
     * Statement for Read
     * @var array
     */
    protected $stmtSelect;

    /**
     * Use ICU MessageFormatter to parse message
     *
     * @var boolean
     */
    protected $useIcuMessageFormatter = false;

    /**
     * Class constructor.
     *
     * @param  array $options
     * @throws \Phalcon\Translate\Exception
     */
    public function __construct(array $options)
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

        if (isset($options['useIcuMessageFormatter'])) {
            if (!class_exists('\MessageFormatter')) {
                throw new Exception('"MessageFormatter" class is required');
            }

            $this->useIcuMessageFormatter = (boolean) $options['useIcuMessageFormatter'];
        }

        $this->stmtSelect = sprintf(
            'SELECT value FROM %s WHERE language = :language AND key_name = :key_name',
            $options['table']
        );

        $this->stmtExists = sprintf(
            'SELECT COUNT(*) AS `count` FROM %s WHERE language = :language AND key_name = :key_name',
            $options['table']
        );

        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $translateKey
     * @param  array  $placeholders
     * @return string
     */
    public function query($translateKey, $placeholders = null)
    {
        $options     = $this->options;
        $translation = $options['db']->fetchOne(
            $this->stmtSelect,
            Db::FETCH_ASSOC,
            ['language' => $options['language'], 'key_name' => $translateKey]
        );
        $value       = empty($translation['value']) ? $translateKey : $translation['value'];

        if (is_array($placeholders) && !empty($placeholders)) {
            if (true === $this->useIcuMessageFormatter) {
                $value = \MessageFormatter::formatMessage(
                    $options['language'], $translateKey, $placeholders
                );
            } else {
                foreach ($placeholders as $placeHolderKey => $placeHolderValue) {
                    $value = str_replace('%' . $placeHolderKey . '%', $placeHolderValue, $value);
                }
            }
        }

        return $value;
    }

    /**
     * Returns the translation string of the given key
     *
     * @param  string $translateKey
     * @param  array  $placeholders
     * @return string
     */
    // @codingStandardsIgnoreStart
    public function _($translateKey, $placeholders = null)
    {
        return $this->query($translateKey, $placeholders);
    }
    // @codingStandardsIgnoreEnd

    /**
     * {@inheritdoc}
     *
     * @param  string  $translateKey
     * @return boolean
     */
    public function exists($translateKey)
    {
        $options = $this->options;

        $result = $options['db']->fetchOne(
            $this->stmtExists,
            Db::FETCH_ASSOC,
            ['language' => $options['language'], 'key_name' => $translateKey]
        );

        return !empty($result['count']);
    }

    /**
     * Adds a translation for given key (No existence check!)
     *
     * @param  string  $translateKey
     * @param  string  $message
     * @return boolean
     */
    public function add($translateKey, $message)
    {
        $options = $this->options;
        $data = ['language' => $options['language'], 'key_name' => $translateKey, 'value' => $message];

        return $options['db']->insert($options['table'], array_values($data), array_keys($data));
    }

    /**
     * Update a translation for given key (No existence check!)
     *
     * @param  string  $translateKey
     * @param  string  $message
     * @return boolean
     */
    public function update($translateKey, $message)
    {
        $options = $this->options;

        return $options['db']->update($options['table'], ['value'], [$message], [
            'conditions' => 'key_name = ? AND language = ?',
            'bind' => ['key' => $translateKey, 'lang' => $options['language']]
        ]);
    }

    /**
     * Deletes a translation for given key (No existence check!)
     *
     * @param  string  $translateKey
     * @return boolean
     */
    public function delete($translateKey)
    {
        $options = $this->options;

        return $options['db']->delete(
            $options['table'],
            'key_name = :key AND language = :lang',
            ['key' => $translateKey, 'lang' => $options['language']]
        );
    }

    /**
     * Sets (insert or updates) a translation for given key
     *
     * @param  string  $translateKey
     * @param  string  $message
     * @return boolean
     */
    public function set($translateKey, $message)
    {
        return $this->exists($translateKey) ?
            $this->update($translateKey, $message) : $this->add($translateKey, $message);
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $translateKey
     * @return string
     */
    public function offsetExists($translateKey)
    {
        return $this->exists($translateKey);
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $translateKey
     * @param  string $message
     * @return string
     */
    public function offsetSet($translateKey, $message)
    {
        return $this->update($translateKey, $message);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $translateKey
     * @return string
     */
    public function offsetGet($translateKey)
    {
        return $this->query($translateKey);
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $translateKey
     * @return string
     */
    public function offsetUnset($translateKey)
    {
        return $this->delete($translateKey);
    }
}
