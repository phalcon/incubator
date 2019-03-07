<?php

namespace Phalcon\Translate\Interpolator;

use Phalcon\Translate\InterpolatorInterface;
use Phalcon\Translate\Exception;
use MessageFormatter;
use IntlException;

class Intl implements InterpolatorInterface
{
    private $locale;
    
    public function __construct($locale)
    {
        $this->locale = $locale;
    }
    
    /**
     * Replaces placeholders by the values passed
     * Use the MessageFormatter class,
     * See http://php.net/manual/en/class.messageformatter.php
    */
    public function replacePlaceholders($translation, $placeholders = null)
    {   
        try {
            // TODO (?) : keep an internal cache of the message formatter (key = locale.translation)
            $fmt = new MessageFormatter($this->locale, $translation);
        } catch (IntlException $e) {
            // the original exception message is "Constructor failed"
            // make it a bit more understandable
            throw new Exception("Unable to instantiate a MessageFormatter. Check locale and string syntax.", 0, $e);
        }
        
        $translation = $fmt->format($placeholders);
        if($translation === false) {
            throw new Exception($fmt->getErrorMessage(), $fmt->getErrorCode());
        }
        return $translation;
    }
}
