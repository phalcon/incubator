<?php

/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2017 Phalcon Team (https://www.phalconphp.com)      |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file LICENSE.txt.                             |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Sergii Svyrydenko <sergey.v.sviridenko@gmail.com>             |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Validation\Validator;

use Phalcon\Validation\Validator;
use Phalcon\Validation;
use Phalcon\Validation\Message;

/**
 * Validates IBAN Numbers (International Bank Account Numbers)
 *
 * <code>
 * use Phalcon\Validation\Validator\Iban;
 *
 * $validator->add('number', new Iban([
 *     'country_code'            => 'AD',  // optional
 *     'allow_non_sepa'          => false, // optional
 *     'messageNotSupported'     => 'Unknown country within the IBAN',
 *     'messageSepaNotSupported' => 'Countries outside the Single Euro Payments Area (SEPA) are not supported',
 *     'messageFalseFormat'      => 'Field has a false IBAN format',
 *     'messageCheckFailed'      => 'Field has failed the IBAN check',
 * ]));
 * </code>
 *
 * @package Phalcon\Validation\Validator
 */
class Iban extends Validator
{
    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = [
        'messageNotSupported'     => ":field has unknown country within the IBAN",
        'messageSepaNotSupported' =>
            "Countries outside the Single Euro Payments Area (SEPA) are not supported in :field",
        'messageFalseFormat'      => ":field has a false IBAN format",
        'messageCheckFailed'      => ":field has failed the IBAN check",
    ];

    /**
     * Optional country code by ISO 3166-1
     *
     * @var string | null
     */
    protected $countryCode;

    /**
     * Optionally allow IBAN codes from non-SEPA countries. Default true
     *
     * @var bool
     */
    protected $allowNonSepa = true;

    /**
     * The SEPA country codes
     *
     * @var array
     */
    protected $sepaCountries = [
        'AT', 'BE', 'BG', 'CY', 'CZ', 'DK', 'FO', 'GL', 'EE', 'FI', 'FR', 'DE',
        'GI', 'GR', 'HU', 'IS', 'IE', 'IT', 'LV', 'LI', 'LT', 'LU', 'MT', 'MC',
        'NL', 'NO', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'CH', 'GB'
    ];

    /**
     * IBAN regexes by country code
     *
     * @var array
     */
    protected $ibanRegex = [
        'AD' => 'AD[0-9]{2}[0-9]{4}[0-9]{4}[A-Z0-9]{12}',
        'AE' => 'AE[0-9]{2}[0-9]{3}[0-9]{16}',
        'AL' => 'AL[0-9]{2}[0-9]{8}[A-Z0-9]{16}',
        'AT' => 'AT[0-9]{2}[0-9]{5}[0-9]{11}',
        'AZ' => 'AZ[0-9]{2}[A-Z]{4}[A-Z0-9]{20}',
        'BA' => 'BA[0-9]{2}[0-9]{3}[0-9]{3}[0-9]{8}[0-9]{2}',
        'BE' => 'BE[0-9]{2}[0-9]{3}[0-9]{7}[0-9]{2}',
        'BG' => 'BG[0-9]{2}[A-Z]{4}[0-9]{4}[0-9]{2}[A-Z0-9]{8}',
        'BH' => 'BH[0-9]{2}[A-Z]{4}[A-Z0-9]{14}',
        'BR' => 'BR[0-9]{2}[0-9]{8}[0-9]{5}[0-9]{10}[A-Z][A-Z0-9]',
        'BY' => 'BY[0-9]{2}[A-Z0-9]{4}[0-9]{4}[A-Z0-9]{16}',
        'CH' => 'CH[0-9]{2}[0-9]{5}[A-Z0-9]{12}',
        'CR' => 'CR[0-9]{2}[0-9]{3}[0-9]{14}',
        'CY' => 'CY[0-9]{2}[0-9]{3}[0-9]{5}[A-Z0-9]{16}',
        'CZ' => 'CZ[0-9]{2}[0-9]{20}',
        'DE' => 'DE[0-9]{2}[0-9]{8}[0-9]{10}',
        'DO' => 'DO[0-9]{2}[A-Z0-9]{4}[0-9]{20}',
        'DK' => 'DK[0-9]{2}[0-9]{14}',
        'EE' => 'EE[0-9]{2}[0-9]{2}[0-9]{2}[0-9]{11}[0-9]{1}',
        'ES' => 'ES[0-9]{2}[0-9]{4}[0-9]{4}[0-9]{1}[0-9]{1}[0-9]{10}',
        'FI' => 'FI[0-9]{2}[0-9]{6}[0-9]{7}[0-9]{1}',
        'FO' => 'FO[0-9]{2}[0-9]{4}[0-9]{9}[0-9]{1}',
        'FR' => 'FR[0-9]{2}[0-9]{5}[0-9]{5}[A-Z0-9]{11}[0-9]{2}',
        'GB' => 'GB[0-9]{2}[A-Z]{4}[0-9]{6}[0-9]{8}',
        'GE' => 'GE[0-9]{2}[A-Z]{2}[0-9]{16}',
        'GI' => 'GI[0-9]{2}[A-Z]{4}[A-Z0-9]{15}',
        'GL' => 'GL[0-9]{2}[0-9]{4}[0-9]{9}[0-9]{1}',
        'GR' => 'GR[0-9]{2}[0-9]{3}[0-9]{4}[A-Z0-9]{16}',
        'GT' => 'GT[0-9]{2}[A-Z0-9]{4}[A-Z0-9]{20}',
        'HR' => 'HR[0-9]{2}[0-9]{7}[0-9]{10}',
        'HU' => 'HU[0-9]{2}[0-9]{3}[0-9]{4}[0-9]{1}[0-9]{15}[0-9]{1}',
        'IE' => 'IE[0-9]{2}[A-Z]{4}[0-9]{6}[0-9]{8}',
        'IL' => 'IL[0-9]{2}[0-9]{3}[0-9]{3}[0-9]{13}',
        'IS' => 'IS[0-9]{2}[0-9]{4}[0-9]{2}[0-9]{6}[0-9]{10}',
        'IT' => 'IT[0-9]{2}[A-Z]{1}[0-9]{5}[0-9]{5}[A-Z0-9]{12}',
        'KW' => 'KW[0-9]{2}[A-Z]{4}[0-9]{22}',
        'KZ' => 'KZ[0-9]{2}[0-9]{3}[A-Z0-9]{13}',
        'LB' => 'LB[0-9]{2}[0-9]{4}[A-Z0-9]{20}',
        'LI' => 'LI[0-9]{2}[0-9]{5}[A-Z0-9]{12}',
        'LT' => 'LT[0-9]{2}[0-9]{5}[0-9]{11}',
        'LU' => 'LU[0-9]{2}[0-9]{3}[A-Z0-9]{13}',
        'LV' => 'LV[0-9]{2}[A-Z]{4}[A-Z0-9]{13}',
        'MC' => 'MC[0-9]{2}[0-9]{5}[0-9]{5}[A-Z0-9]{11}[0-9]{2}',
        'MD' => 'MD[0-9]{2}[A-Z0-9]{20}',
        'ME' => 'ME[0-9]{2}[0-9]{3}[0-9]{13}[0-9]{2}',
        'MK' => 'MK[0-9]{2}[0-9]{3}[A-Z0-9]{10}[0-9]{2}',
        'MR' => 'MR13[0-9]{5}[0-9]{5}[0-9]{11}[0-9]{2}',
        'MT' => 'MT[0-9]{2}[A-Z]{4}[0-9]{5}[A-Z0-9]{18}',
        'MU' => 'MU[0-9]{2}[A-Z]{4}[0-9]{2}[0-9]{2}[0-9]{12}[0-9]{3}[A-Z]{3}',
        'NL' => 'NL[0-9]{2}[A-Z]{4}[0-9]{10}',
        'NO' => 'NO[0-9]{2}[0-9]{4}[0-9]{6}[0-9]{1}',
        'PK' => 'PK[0-9]{2}[A-Z]{4}[A-Z0-9]{16}',
        'PL' => 'PL[0-9]{2}[0-9]{8}[0-9]{16}',
        'PS' => 'PS[0-9]{2}[A-Z]{4}[A-Z0-9]{21}',
        'PT' => 'PT[0-9]{2}[0-9]{4}[0-9]{4}[0-9]{11}[0-9]{2}',
        'RO' => 'RO[0-9]{2}[A-Z]{4}[A-Z0-9]{16}',
        'RS' => 'RS[0-9]{2}[0-9]{3}[0-9]{13}[0-9]{2}',
        'SA' => 'SA[0-9]{2}[0-9]{2}[A-Z0-9]{18}',
        'SE' => 'SE[0-9]{2}[0-9]{3}[0-9]{16}[0-9]{1}',
        'SI' => 'SI[0-9]{2}[0-9]{5}[0-9]{8}[0-9]{2}',
        'SK' => 'SK[0-9]{2}[0-9]{4}[0-9]{6}[0-9]{10}',
        'SM' => 'SM[0-9]{2}[A-Z]{1}[0-9]{5}[0-9]{5}[A-Z0-9]{12}',
        'TN' => 'TN59[0-9]{2}[0-9]{3}[0-9]{13}[0-9]{2}',
        'TR' => 'TR[0-9]{2}[0-9]{5}[A-Z0-9]{1}[A-Z0-9]{16}',
        'VG' => 'VG[0-9]{2}[A-Z]{4}[0-9]{16}',
    ];

    /**
     * Sets validator options
     *
     * @param  array $options OPTIONAL
     */
    public function __construct(array $options = [])
    {
        if (isset($options['country_code'])) {
            $this->setCountryCode($options['country_code']);
        }

        if (isset($options['allow_non_sepa'])) {
            $this->allowNonSepa = $options['allow_non_sepa'];
        }

        if (!isset($options['messageNotSupported'])) {
            $options['messageNotSupported'] = $this->messageTemplates['messageNotSupported'];
        }

        if (!isset($options['messageSepaNotSupported'])) {
            $options['messageSepaNotSupported'] = $this->messageTemplates['messageSepaNotSupported'];
        }

        if (!isset($options['messageFalseFormat'])) {
            $options['messageFalseFormat'] = $this->messageTemplates['messageFalseFormat'];
        }

        if (!isset($options['messageCheckFailed'])) {
            $options['messageCheckFailed'] = $this->messageTemplates['messageCheckFailed'];
        }

        parent::__construct($options);
    }

    /**
     * Sets an optional country code by ISO 3166-1
     *
     * @param  string | null $countryCode
     */
    public function setCountryCode($countryCode = null)
    {
        if ($countryCode !== null) {
            $countryCode = (string) $countryCode;
        }
        $this->countryCode = $countryCode;
    }

    /**
     * Sets the optional allow non-sepa countries setting
     *
     * @param  bool $allowNonSepa
     */
    public function setAllowNonSepa($allowNonSepa)
    {
        $this->allowNonSepa = (bool) $allowNonSepa;
    }

    /**
     * {@inheritdoc}
     *
     * @param Validation $validation
     * @param string $attribute
     *
     * @return bool
     */
    public function validate(Validation $validation, $attribute)
    {
        $messageCode = $this->getErrorMessageCode($validation, $attribute);
        if (!empty($messageCode)) {
            $label = $this->prepareLabel($validation, $attribute);
            $code = $this->prepareCode($attribute);
            $replacePairs = [":field"=> $label];

            $message = $this->prepareMessage($validation, $attribute, "Iban", $messageCode);

            $validation->appendMessage(
                new Message(
                    strtr($message, $replacePairs),
                    $attribute,
                    "Iban",
                    $code
                )
            );
            return false;
        }

        return true;
    }

    /**
     * Validate code and return error message key or empty string
     *
     * @param Validation $validation
     * @param string $attribute
     *
     * @return string
     */
    protected function getErrorMessageCode(Validation $validation, $attribute)
    {
        $value = $validation->getValue($attribute);

        if ($this->countryCode === null) {
            $this->countryCode = substr($value, 0, 2);
        }

        if (!array_key_exists($this->countryCode, $this->ibanRegex)) {
            return 'messageNotSupported';
        }

        if (!$this->allowNonSepa && !in_array($this->countryCode, $this->sepaCountries)) {
            return 'messageSepaNotSupported';
        }

        if (!preg_match('/^' . $this->ibanRegex[$this->countryCode] . '$/', $value)) {
            return 'messageFalseFormat';
        }

        $format = substr($value, 4) . substr($value, 0, 4);
        $format = str_replace(
            ['A',  'B',  'C',  'D',  'E',  'F',  'G',  'H',  'I',  'J',  'K',  'L',  'M',
                'N',  'O',  'P',  'Q',  'R',  'S',  'T',  'U',  'V',  'W',  'X',  'Y',  'Z'],
            ['10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22',
                '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34', '35'],
            $format
        );

        $temp = intval(substr($format, 0, 1));
        $len  = strlen($format);

        for ($x = 1; $x < $len; ++$x) {
            $temp *= 10;
            $temp += intval(substr($format, $x, 1));
            $temp %= 97;
        }

        if ($temp != 1) {
            return 'messageCheckFailed';
        }

        return '';
    }
}
