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
  | Authors: Anton Kornilov <kachit@yandex.ru>                             |
  | Maintainer: Wajdi Jurry <jurrywajdi@yahoo.com>
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Validation\Validator;

use MongoDB\BSON\ObjectId;
use Phalcon\Validation;
use Phalcon\Validation\Validator;
use Phalcon\Validation\Message;

/**
 * MongoId validator
 *
 * @package Phalcon\Validation\Validator
 */
class MongoId extends Validator
{
    /**
     * @param Validation $validation
     * @param string $attribute
     * @return bool
     */
    public function validate(Validation $validation, $attribute)
    {
        $value = $validation->getValue($attribute);
        $allowEmpty = $this->hasOption('allowEmpty');

        if ($allowEmpty && empty($value)) {
            return true;
        }

        if ($value instanceof ObjectId || preg_match('/^[a-f\d]{24}$/i', $value)) {
            return true;
        }

        $message = ($this->hasOption('message')) ? $this->getOption('message') : 'MongoId is not valid';
        $validation->appendMessage(new Message($message, $attribute, 'MongoId'));
        return false;
    }
}
