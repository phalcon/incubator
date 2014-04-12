<?php
/**
 * Phalcon Translate Adapter, using ini files.
 * 
 * 1. Organise your translations into ini files:
 * /app/translations/en.ini
 * access.login = Login
 * access.logout = Logout
 * user.profile.first_name = First name
 * user.greeting = Hello, %name%!
 * 
 * /app/translations/bg.ini
 * access.login = Вход
 * access.logout = Изход
 * user.profile.first_name = Име
 * user.greeting = Здравей, %name%!
 * 
 * 2. Initialize the translation adapter, for example in the initialize() 
 * method of your ControllerBase:
 * 
 * $translate = new Phalcon\Translate\Adapter\Ini(
 *    array('file' => '/path/to/translations/bg.ini')
 * );
 * 
 * Optionally, assign it to the view:
 * $this->view->setVar('t', $translate);
 * 
 * 3. Use it as:
 * 
 * $translate->_("acces.login");
 * 
 * or in the view file:
 * $t->_("access.login");
 * 
 * or if you are using Volt:
 * {{ t._("access.login") }}
 * 
 * 
 * @author Venelin Manchev <manchev@prodio.bg>
 */

namespace Phalcon\Translate\Adapter;

use Phalcon\Translate\Adapter;
use Phalcon\Translate\AdapterInterface;
use Phalcon\Translate\Exception;

class Ini implements AdapterInterface
{
    
    /**
     * @var array
     */
    protected $translate;
    
    /**
     * Class constructor.
     *
     * @param  array      $options
     * @throws \Phalcon\Translate\Exception
     */
    public function __construct($options)
    {
        if (!isset($options['file'])) {
            throw new Exception('Parameter "file" is required.');
        }


        if (!file_exists($options['file'])) {
            throw new Exception('Error opening translation file "' . $options['file'] . '".');
        }

        $this->translate = parse_ini_file($options['file']);
    }

    /**
     * Returns the translation string of the given key
     *
     * @param   string $translateKey
     * @param   array $placeholders
     * @return  string
     */
    // @codingStandardsIgnoreStart
    public function _($translateKey, $placeholders = null)
    // @codingStandardsIgnoreEnd
    {
        return $this->query($translateKey, $placeholders);
    }

    /**
     * Returns the translation related to the given key
     *
     * @param   string $index
     * @param   array $placeholders
     * @return  string
     */
    public function query($translateKey, $placeholders = null)
    {

        if (!$this->exists($translateKey)) {
            return $translateKey;
        }
        
        $translation = $this->translate[$translateKey];
        
        if (is_array($placeholders)) {
            foreach ($placeholders as $key => $value) {
                $translation = str_replace('%' . $key . '%', $value, $translation);
            }
        }

        return $translation;
    }

    /**
     * Check whether is defined a translation key in the internal array
     *
     * @param   string $index
     * @return  bool
     */
    public function exists($index)
    {
        return array_key_exists($index, $this->translate);
    }
}
