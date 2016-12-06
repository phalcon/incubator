<?php
/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2016 Phalcon Team (https://www.phalconphp.com)      |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file LICENSE.txt.                             |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Gorka Guridi <gorka.guridi@gmail.com>                         |
  +------------------------------------------------------------------------+
*/
namespace Phalcon\Translate\Adapter;

use Phalcon\Translate\Adapter\Base;
use Phalcon\Translate\AdapterInterface;
use Phalcon\Translate\Exception;

/**
 * Implements a mongo adapter for translations.
 * 
 * A generic collection with a source to store the translations must be created
 * and passed as a parameter.
 */
class Mongo extends Base implements AdapterInterface, \ArrayAccess
{
    /**
     * {@inheritDoc}
     */
    public function __construct($options)
    {
        if (!isset($options['collection'])) {
            throw new Exception("Parameter 'collection' is required");
        }
        $this->setCollection($options['collection']);
        
        if (!isset($options['language'])) {
            throw new Exception("Parameter 'language' is required");
        }
        $this->setLanguage($options['language']);
        
        if (isset($options['formatter'])) {
            $this->setFormatter($options['formatter']);
        }
    }
    
    /**
     * Sets the collection object.
     * 
     * @param \Phalcon\Mvc\Collection|string $collection
     *   Translations collection class to use. Can be an instance or a string.
     */
    protected function setCollection($collection)
    {
        $this->collection = $collection;
    }
    
    /**
     * Sets the language to use.
     * 
     * @param string $language
     */
    protected function setLanguage($language)
    {
        $this->language = $language;
    }
    
    /**
     * Sets the formatter to use if necessary.
     * 
     * @param \MessageFormatter $formatter
     *   Message formatter.
     */
    protected function setFormatter(\MessageFormatter $formatter)
    {
        $this->formatter = $formatter;
    }
    
    /**
     * Gets the translations set.
     * 
     * @param string $translateKey
     *   Key of the collection set.
     * 
     * @return \Phalcon\Mvc\Collection
     */
    protected function getTranslations($translateKey)
    {
        return $this->collection::findFirst([[
            'key' => $translateKey,
        ]]);
    }

    /**
     * {@inheritDoc}
     */
    public function _($translateKey, $placeholders = null)
    {
        return $this->query($translateKey, $placeholders);
    }

    /**
     * {@inheritDoc}
     */
    public function query($translateKey, $placeholders = null)
    {
        $translations = $this->getTranslations($translateKey);        
        $translation = $translations->{$this->language} ?? $translateKey;
        if (!empty($placeholders))
        {
            return $this->format($translation, $placeholders);
        }
        return $translation;
    }
    
    /**
     * Formats a translation.
     * 
     * @param string $translation
     *   Translated text.
     * 
     * @param array $placeholders
     *   Placeholders to apply.
     * 
     * @return string
     */
    protected function format($translation, $placeholders = [])
    {
        if ($this->formatter)
        {
            return $this->formatter::formatMessage($this->language, $translation, $placeholders);
        }
        
        foreach ($placeholders as $key => $value) {
            $translation = str_replace("%$key%", $value, $translation);
        }
        return $translation;
    }

    /**
     * {@inheritDoc}
     */
    public function exists($translateKey)
    {
        $translations = $this->getTranslations($translateKey);   
        return isset($translations->{$this->language});
    }
    
    /**
     * {@inheritDoc}
     */
    public function offsetExists($translateKey)
    {
        return $this->exists($translateKey);
    }
    
    /**
     * {@inheritDoc}
     */
    public function offsetSet($translateKey, $message)
    {
        $translations = $this->getTranslations($translateKey);
        $translations->{$this->language} = $message;
        return $translations->save();
    }
    
    /**
     * {@inheritDoc}
     */
    public function offsetGet($translateKey)
    {
        $translations = $this->getTranslations($translateKey);
        return $translations->{$this->language} ?? '';
    }
    
    /**
     * {@inheritDoc}
     */
    public function offsetUnset($translateKey)
    {
        $translations = $this->getTranslations($translateKey);
        unset($translations->{$this->language});
        return $translations->save();
    }
}