<?php

/**
 * reCaptcha validator
 *
 * @package Phalcon\Validation\Validator
 */
namespace Phalcon\Validation\Validator;

use Phalcon\Http\Request;
use Phalcon\Validation\Message;
use Phalcon\Validation\Validator;

/**
 * Phalcon\Validation\Validator\ReCaptcha
 *
 * Verifies a value to a reCAPTCHA challenge
 *
 *<code>
 *$validator->add('g-recaptcha-response', new \Phalcon\Validation\Validator(array(
 *   'message' => 'The captcha is not valid',
 *   'secret'  => 'your_site_key'
 *)));
 *</code>
 *
 * @link https://developers.google.com/recaptcha/intro
 * @package Phalcon\Validation\Validator
 */
class ReCaptcha extends Validator
{
    /**
     * API request URL
     */
    CONST RECAPTCHA_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Response error code reference
     * @var array $messages
     */
    protected $messages = array(
        'missing-input-secret'   => 'The secret parameter is missing.',
        'invalid-input-secret'   => 'The secret parameter is invalid or malformed.',
        'missing-input-response' => 'The response parameter is missing.',
        'invalid-input-response' => 'The response parameter is invalid or malformed.',
    );

    /**
     * @param \Phalcon\Validation $validation
     * @param string              $attribute
     *
     * @return bool
     */
    public function validate(\Phalcon\Validation $validation, $attribute)
    {
        $secret   = $this->getOption('secret');
        $value    = $validation->getValue($attribute);
        $request  = $validation->getDI()->get('request');
        $remoteIp = $request->getClientAddress(false);

        if (!empty($value)) {

            $curl = curl_init(self::RECAPTCHA_URL);
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS     => array(
                    'secret'   => $secret,
                    'response' => $value,
                    'remoteip' => $remoteIp)
            ));
            $response = json_decode(curl_exec($curl), true);
            curl_close($curl);
        }

        if (empty($response['success'])) {

            $label = $this->getOption('label');
            if (empty($label)) {
                $label = $validation->getLabel($attribute);
            }

            $message      = $this->getOption('message');
            $replacePairs = array(':field', $label);
            if (empty($message) && !empty($response['error-codes'])) {
                $message = $this->messages[$response['error-codes']];
            }

            if (empty($message)) {
                $message = $validation->getDefaultMessage('ReCaptcha');
            }

            $validation->appendMessage(new Message(strtr($message, $replacePairs), $attribute, 'ReCaptcha'));
            return false;
        }
        return true;
    }
}