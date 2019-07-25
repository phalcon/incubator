<?php

namespace Phalcon\Translate\Interpolator;

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
    public function replacePlaceholders(string $translation, array $placeholders = null): string
    {
        if (is_array($placeholders) && count($placeholders)) {
            try {
                // TODO (?) : keep an internal cache of the MessageFormatter objects (key = locale.translation)
                $fmt = new MessageFormatter($this->locale, $translation);
            } catch (IntlException $e) {
                $fmt = null;
            } finally {
                // for php 7.x the original exception message is "Constructor failed"
                // for php 5.6 the constructor returns null, see this wont fix bug https://bugs.php.net/bug.php?id=58631
                // make it a bit more understandable
                if (is_null($fmt)) {
                    throw new Exception(
                        "Unable to instantiate a MessageFormatter. Check locale and string syntax.",
                        0,
                        isset($e) ? $e : null
                    );
                }
            }

            $translation = $fmt->format($placeholders);
            if ($translation === false) {
                throw new Exception($fmt->getErrorMessage(), $fmt->getErrorCode());
            }
        }
        return $translation;
    }
}
