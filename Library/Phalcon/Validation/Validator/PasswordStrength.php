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
  | Authors: David Hubner <david.hubner@gmail.com>                         |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Validation\Validator;

use Phalcon\Validation;

/**
 * Validates password strength
 *
 * <code>
 * new \Phalcon\Validation\Validator\PasswordStrength([
 *     'minScore' => {[1-4] - minimal password score},
 *     'message' => {string - validation message},
 *     'allowEmpty' => {bool - allow empty value}
 * ])
 * </code>
 *
 * @package Phalcon\Validation\Validator
 */
class PasswordStrength extends Validation\Validator
{

    const MIN_VALID_SCORE = 2;

    /**
     * Value validation
     *
     * @param   \Phalcon\Validation $validation - validation object
     * @param   string $attribute - validated attribute
     * @return  bool
     */
    public function validate(Validation $validation, $attribute): bool
    {
        $allowEmpty = $this->getOption('allowEmpty');
        $value = $validation->getValue($attribute);

        if ($allowEmpty && ((is_scalar($value) && (string) $value === '') || is_null($value))) {
            return true;
        }

        $minScore = ($this->hasOption('minScore') ? $this->getOption('minScore') : self::MIN_VALID_SCORE);

        if (is_string($value) && $this->countScore($value) >= $minScore) {
            return true;
        }

        $message = ($this->hasOption('message') ? $this->getOption('message') : 'Password too weak');

        $validation->appendMessage(
            new Validation\Message($message, $attribute, 'PasswordStrengthValidator')
        );

        return false;
    }

    /**
     * Calculates password strength score
     *
     * @param   string $value - password
     * @return  int (1 = very weak, 2 = weak, 3 = medium, 4+ = strong)
     */
    private function countScore($value)
    {
        $score = 0;
        $hasLower = preg_match('![a-z]!', $value);
        $hasUpper = preg_match('![A-Z]!', $value);
        $hasNumber = preg_match('![0-9]!', $value);

        if ($hasLower && $hasUpper) {
            ++$score;
        }
        if (($hasNumber && $hasLower) || ($hasNumber && $hasUpper)) {
            ++$score;
        }
        if (preg_match('![^0-9a-zA-Z]!', $value)) {
            ++$score;
        }

        $length = mb_strlen($value);

        if ($length >= 16) {
            $score += 2;
        } elseif ($length >= 8) {
            ++$score;
        } elseif ($length <= 4 && $score > 1) {
            --$score;
        } elseif ($length > 0 && $score === 0) {
            ++$score;
        }

        return $score;
    }
}
