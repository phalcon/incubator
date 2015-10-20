<?php
/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2015 Phalcon Team (http://www.phalconphp.com)       |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file docs/LICENSE.txt.                        |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Anton Kornilov <kachit@yandex.ru>                             |
  +------------------------------------------------------------------------+
*/

/**
 * MongoId validator
 *
 * @package Phalcon\Validation\Validator
 */
namespace Phalcon\Validation\Validator;

use Phalcon\Validation;
use Phalcon\Validation\Validator;
use Phalcon\Validation\Message;
use Phalcon\Validation\Exception;

class MongoId extends Validator
{
    /**
     * @param Validation $validation
     * @param string $attribute
     * @return bool
     * @throws Exception
     */
    public function validate(Validation $validation, $attribute)
    {
        if (!extension_loaded('mongo')) {
            throw new Exception('Mongo extension is not available');
        }

        $value = $validation->getValue($attribute);
        $allowEmpty = $this->hasOption('allowEmpty');
        $result = ($allowEmpty && empty($value)) ? true : \MongoId::isValid($value);

        if (!$result) {
            $message = ($this->hasOption('message')) ? $this->getOption('message') : 'MongoId is not valid';
            $validation->appendMessage(new Message($message, $attribute, 'MongoId'));
        }
        return $result;
    }
}
