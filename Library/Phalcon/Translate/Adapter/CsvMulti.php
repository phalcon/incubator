<?php

namespace Lib\Translate\Adapter;

use Phalcon\Translate\Exception;
use Phalcon\Translate\AdapterInterface;
use Phalcon\Translate\Adapter\Csv as Csv;

class CsvMulti extends Csv implements AdapterInterface, \ArrayAccess {
  /**
  * @var array
  */
  private $locales_map = array();
  
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
      throw new Exception("Error opening translation file '" . $file . "'");
    }
    
    $line = 0;
    $locales_map = array();
    while($data = fgetcsv($fileHandler, $length, $delimiter, $enclosure)) {
      
      // register the horizontal locales sort order
      if($line++ == 0) {
        // the first element is removed
        foreach(array_slice($data, 1) as $pos => $locale) {
          $this->locales_map[$pos] = $locale;
        }
      } else {
        
        // the first col is the translation index
        $index = array_shift($data);
        $this->_indexes[] = $index;
        // the first element is removed as well, so the pos is according to the first line
        foreach($data as $pos => $translation) {
          $this->_translate[$this->locales_map[$pos]][$index] = $translation;
        }
        
      }
      
    }
    
    fclose($fileHandler);
    
  }
  
  
  /**
   * Sets locale information
   *
   * <code>
   * // Set locale to Dutch
   * $gettext->setLocale('nl_NL');
   *
   * // Try different possible locale names for german
   * $gettext->setLocale('de_DE@euro', 'de_DE', 'de', 'ge');
   * </code>
   */
  public function setLocale($locale) {
    
    if($locale !== false && !array_key_exists($locale, $this->_translate)) {
      throw new \Exception("The locale '{$locale}' is not available in the date source");
      return false;
    } else  {
      return $this->_locale = $locale;
    }
  }
  
  /**
   * Returns the translation related to the given key
   */
  public function query($index, $placeholders = null) {
    
    // no translation mode
    if($this->_locale === false) {
      return $index;
    }
    
    if($this->exists($index)) {
      $translation = $this->_translate[$this->_locale][$index];
    } else {
      $translation = $index;
    }
    
    return $this->replacePlaceholders($translation, $placeholders);
  }

  /**
   * Check whether is defined a translation key in the internal array
   */
  public function exists($index) {
    if(is_null($this->_locale)) {
      throw new Exception('The locale must have been defined');
    }
    return array_key_exists($index, $this->_translate[$this->_locale]);
  }
  
  public function getIndexes() {
    return $this->_indexes;
  }
    
    
}
