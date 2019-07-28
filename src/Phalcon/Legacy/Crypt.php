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
  | Authors: Andres Gutierrez <andres@phalconphp.com>                      |
  |          Eduar Carvajal <eduar@phalconphp.com>                         |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Legacy;

use Phalcon\Legacy\Crypt\Exception;

/**
 * Phalcon\Legacy\Crypt
 *
 * Port of Phalcon 2.0.x Phalcon\Crypt.
 * Provides encryption facilities to phalcon applications.
 *
 *<code>
 *  $crypt = new \Phalcon\Legacy\Crypt();
 *
 *  $key = 'le password';
 *  $text = 'This is a secret text';
 *
 *  $encrypted = $crypt->encrypt($text, $key);
 *
 *  echo $crypt->decrypt($encrypted, $key);
 *</code>
 *
 * @link https://github.com/phalcon/incubator/issues/563
 * @package Phalcon\Legacy
 */
class Crypt implements CryptInterface
{
    protected $key;

    protected $padding = 0;

    protected $mode = MCRYPT_MODE_CBC;

    protected $cipher = "rijndael-256";

    const PADDING_DEFAULT = 0;

    const PADDING_ANSI_X_923 = 1;

    const PADDING_PKCS7 = 2;

    const PADDING_ISO_10126 = 3;

    const PADDING_ISO_IEC_7816_4 = 4;

    const PADDING_ZERO = 5;

    const PADDING_SPACE = 6;

    /**
     * Changes the padding scheme used
     *
     * @param int $scheme
     * @return CryptInterface
     */
    public function setPadding($scheme)
    {
        $this->padding = $scheme;

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @param string $cipher
     * @return CryptInterface
     */
    public function setCipher($cipher)
    {
        $this->cipher = $cipher;

        return $this;
    }

    /**
     * Returns the current cipher
     *
     * @return string
     */
    public function getCipher()
    {
        return $this->cipher;
    }

    /**
     * Sets the encrypt/decrypt mode
     *
     * @param string $mode
     * @return CryptInterface
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * Returns the current encryption mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Sets the encryption key
     *
     * @param string $key
     * @return CryptInterface
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Returns the encryption key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Pads texts before encryption
     *
     * @link http://www.di-mgt.com.au/cryptopad.html
     *
     * @param string $text
     * @param string $mode
     * @param int $blockSize
     * @param int $paddingType
     * @return string
     * @throws Exception
     */
    protected function cryptPadText($text, $mode, $blockSize, $paddingType)
    {
        $paddingSize = 0;
        $padding = null;

        if ($mode == MCRYPT_MODE_CBC || $mode == MCRYPT_MODE_ECB) {
            $paddingSize = $blockSize - (strlen($text) % $blockSize);

            if ($paddingSize >= 256) {
                throw new Exception("Block size is bigger than 256");
            }

            switch ($paddingType) {
                case self::PADDING_ANSI_X_923:
                    $padding = str_repeat(chr(0), $paddingSize - 1) . chr($paddingSize);

                    break;

                case self::PADDING_PKCS7:
                    $padding = str_repeat(
                        chr($paddingSize),
                        $paddingSize
                    );

                    break;

                case self::PADDING_ISO_10126:
                    $padding = "";

                    foreach (range(0, $paddingSize - 2) as $i) {
                        $padding .= chr(
                            rand()
                        );
                    }

                    $padding .= chr($paddingSize);

                    break;

                case self::PADDING_ISO_IEC_7816_4:
                    $padding = chr(0x80) . str_repeat(chr(0), $paddingSize - 1);

                    break;

                case self::PADDING_ZERO:
                    $padding = str_repeat(
                        chr(0),
                        $paddingSize
                    );

                    break;

                case self::PADDING_SPACE:
                    $padding = str_repeat(" ", $paddingSize);

                    break;

                default:
                    $paddingSize = 0;

                    break;
            }
        }

        if (!$paddingSize) {
            return $text;
        }

        if ($paddingSize > $blockSize) {
            throw new Exception("Invalid padding size");
        }

        return $text . substr($padding, 0, $paddingSize);
    }

    /**
     * Removes $paddingType padding from text
     * If the function detects that the text was not padded, it will return it unmodified
     *
     * @param string $text Message to be unpadded
     * @param string $mode Encryption mode; unpadding is applied only in CBC or ECB mode
     * @param int $blockSize Cipher block size
     * @param int $paddingType Padding scheme
     * @return string
     */
    protected function cryptUnpadText($text, $mode, $blockSize, $paddingType)
    {
        $paddingSize = 0;
        $length = strlen($text);

        if ($length > 0 && ($length % $blockSize == 0) && ($mode == MCRYPT_MODE_CBC || $mode == MCRYPT_MODE_ECB)) {
            switch ($paddingType) {
                case self::PADDING_ANSI_X_923:
                    $last = substr($text, $length - 1, 1);
                    $ord = (int) ord($last);

                    if ($ord <= $blockSize) {
                        $paddingSize = $ord;

                        $padding = str_repeat(chr(0), $paddingSize - 1) . $last;

                        if (substr($text, $length - $paddingSize) != $padding) {
                            $paddingSize = 0;
                        }
                    }

                    break;

                case self::PADDING_PKCS7:
                    $last = substr($text, $length - 1, 1);
                    $ord = (int) ord($last);

                    if ($ord <= $blockSize) {
                        $paddingSize = $ord;

                        $padding = str_repeat(
                            chr($paddingSize),
                            $paddingSize
                        );

                        if (substr($text, $length - $paddingSize) != $padding) {
                            $paddingSize = 0;
                        }
                    }

                    break;

                case self::PADDING_ISO_10126:
                    $last = substr($text, $length - 1, 1);
                    $paddingSize = (int) ord($last);

                    break;

                case self::PADDING_ISO_IEC_7816_4:
                    $i = $length - 1;

                    while ($i > 0 && $text[$i] == 0x00 && $paddingSize < $blockSize) {
                        $paddingSize++;
                        $i--;
                    }

                    if ($text[$i] == 0x80) {
                        $paddingSize++;
                    } else {
                        $paddingSize = 0;
                    }

                    break;

                case self::PADDING_ZERO:
                    $i = $length - 1;

                    while ($i >= 0 && $text[$i] == 0x00 && $paddingSize <= $blockSize) {
                        $paddingSize++;
                        $i--;
                    }

                    break;

                case self::PADDING_SPACE:
                    $i = $length - 1;

                    while ($i >= 0 && $text[$i] == 0x20 && $paddingSize <= $blockSize) {
                        $paddingSize++;
                        $i--;
                    }

                    break;

                default:
                    break;
            }

            if ($paddingSize && $paddingSize <= $blockSize) {
                if ($paddingSize < $length) {
                    return substr($text, 0, $length - $paddingSize);
                }

                return "";
            }
        }

        return $text;
    }

    /**
     * Encrypts a text
     *
     * <code>
     * $encrypted = $crypt->encrypt("Ultra-secret text", "encrypt password");
     * </code>
     *
     * @param string $text
     * @param mixed $key
     * @return string
     * @throws Exception
     */
    public function encrypt($text, $key = null)
    {
        if (!function_exists("mcrypt_get_iv_size")) {
            throw new Exception("mcrypt extension is required");
        }

        if ($key === null) {
            $encryptKey = $this->key;
        } else {
            $encryptKey = $key;
        }

        if (empty($encryptKey)) {
            throw new Exception("Encryption key cannot be empty");
        }

        $ivSize = mcrypt_get_iv_size($this->cipher, $this->mode);
        if (strlen($encryptKey) > $ivSize) {
            throw new Exception("Size of key is too large for this algorithm");
        }

        $iv = strval(
            mcrypt_create_iv($ivSize, MCRYPT_RAND)
        );

        $blockSize = intval(
            mcrypt_get_block_size($this->cipher, $this->mode)
        );

        if ($this->padding != 0 && ($this->mode == MCRYPT_MODE_CBC || $this->mode == MCRYPT_MODE_ECB)) {
            $padded = $this->cryptPadText(
                $text,
                $this->mode,
                $blockSize,
                $this->padding
            );
        } else {
            $padded = $text;
        }

        return $iv . mcrypt_encrypt(
            $this->cipher,
            $encryptKey,
            $padded,
            $this->mode,
            $iv
        );
    }

    /**
     * Decrypts a text
     *
     * <code>
     * echo $crypt->decrypt($encrypted, "decrypt password");
     * </code>
     *
     * @param string $text
     * @param string $key
     * @return string
     * @throws Exception
     */
    public function decrypt($text, $key = null)
    {
        if (!function_exists("mcrypt_get_iv_size")) {
            throw new Exception("mcrypt extension is required");
        }

        if ($key === null) {
            $decryptKey = $this->key;
        } else {
            $decryptKey = $key;
        }

        if (empty($decryptKey)) {
            throw new Exception("Decryption key cannot be empty");
        }

        $ivSize = mcrypt_get_iv_size($this->cipher, $this->mode);
        $keySize = strlen($decryptKey);
        if ($keySize > $ivSize) {
            throw new Exception("Size of key is too large for this algorithm");
        }

        if ($keySize > strlen($text)) {
            throw new Exception(
                "Size of IV is larger than text to decrypt. Are you trying to decrypt an uncrypted text?"
            );
        }

        $data = substr($text, $ivSize);

        $decrypted = mcrypt_decrypt(
            $this->cipher,
            $decryptKey,
            $data,
            $this->mode,
            substr($text, 0, $ivSize)
        );

        $blockSize = mcrypt_get_block_size($this->cipher, $this->mode);

        if ($this->mode == MCRYPT_MODE_CBC || $this->mode == MCRYPT_MODE_ECB) {
            return $this->cryptUnpadText(
                $decrypted,
                $this->mode,
                $blockSize,
                $this->padding
            );
        }

        return $decrypted;
    }

    /**
     * Encrypts a text returning the result as a base64 string
     *
     * @param string $text
     * @param mixed $key
     * @param bool $safe
     * @return string
     */
    public function encryptBase64($text, $key = null, $safe = false)
    {
        if ($safe) {
            return strtr(
                base64_encode(
                    $this->encrypt($text, $key)
                ),
                "+/",
                "-_"
            );
        }

        return base64_encode(
            $this->encrypt($text, $key)
        );
    }

    /**
     * Decrypt a text that is coded as a base64 string
     *
     * @param string $text
     * @param mixed $key
     * @param bool $safe
     * @return string
     */
    public function decryptBase64($text, $key = null, $safe = false)
    {
        if ($safe) {
            return $this->decrypt(
                base64_decode(
                    strtr(
                        $text,
                        "-_",
                        "+/"
                    )
                ),
                $key
            );
        }

        return $this->decrypt(
            base64_decode($text),
            $key
        );
    }

    /**
     * Returns a list of available cyphers
     *
     * @return array
     */
    public function getAvailableCiphers()
    {
        return mcrypt_list_algorithms();
    }

    /**
     * Returns a list of available modes
     *
     * @return array
     */
    public function getAvailableModes()
    {
        return mcrypt_list_modes();
    }
}
