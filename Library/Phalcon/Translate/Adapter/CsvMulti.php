<?php

namespace Phalcon\Translate\Adapter;

use Phalcon\Translate\Exception;
use Phalcon\Translate\AdapterInterface;
use Phalcon\Translate\Adapter\Csv;

class CsvMulti extends Csv implements AdapterInterface, \ArrayAccess {
  /**
  * @var array
  */
  private $locales = array();
  
  /**
  * @var string
  */
  protected $_locale = null;
  
  /**
  * @var string
  */
  protected $_indexes = array();
  
  /**
  * Load translates from file
  *
  * @param string file
  * @param int length
  * @param string delimiter
  * @param string enclosure
  */
  private function _load($file, $length, $delimiter, $enclosure) {
    
    $fileHandler = fopen($file, "rb");
    
    if(gettype($fileHandler) !== "resource"){
      throw new \Exception("Error opening translation file '" . $file . "'");
    }
    
    $line = 0;
    $locales = array();
    while($data = fgetcsv($fileHandler, $length, $delimiter, $enclosure)) {
      
        if($line++ == 0) {
            // first csv line
            // register the horizontal locales sort order
            // the first element (must be empty) is removed
            foreach(array_slice($data, 1) as $pos => $locale) {
              $this->locales[$pos] = $locale;
            }
        } else {
            // the first row is the translation index (label)
            $index = array_shift($data);
            // store this index internally
            $this->_indexes[] = $index;
            // the first element is removed as well, so the pos is according to the first line
            foreach($data as $pos => $translation) {
              $this->_translate[$this->locales[$pos]][$index] = $translation;
            }
        }
      
    }
    
    fclose($fileHandler);
    
  }
  
  
  /**
   * Sets locale information, according to one from the header row of the source csv
   * Set it to false for enabling the "no translation mode"
   * <code>
   * // Set locale to Dutch
   * $adapter->setLocale('nl_NL');
   * </code>
   */
  public function setLocale($locale) {
      
    if($locale !== false && !array_key_exists($locale, $this->_translate)) {
      throw new \Exception("The locale '{$locale}' is not available in the data source.");
      return false;
    } else  {
      return $this->_locale = $locale;
    }
    
  }
  
  /**
   * Returns the translation related to the given key and the previsouly set locale
   */
  public function query($index, $placeholders = null) {
      
    if(!$this->exists($index)) {
        throw new \Exception("They key '{$index}' was not found.");
    }
    
    if($this->_locale === false) {
        // "no translation mode"
        $translation = $index;
    } else {
        $translation = $this->_translate[$this->_locale][$index];
    }
    
    return $this->replacePlaceholders($translation, $placeholders);
  }

  /**
   * Check whether is defined a translation key in the internal array
   */
  public function exists($index) {
      
    if(is_null($this->_locale)) {
      throw new Exception('The locale must have been defined.');
    }
    return in_array($index, $this->getIndexes());
  }
  
  /**
   * Returns all the translation keys
   */
  public function getIndexes() {
    return $this->_indexes;
  }
}
