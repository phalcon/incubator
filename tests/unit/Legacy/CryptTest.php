<?php

namespace Phalcon\Test\Legacy;

use UnitTester;
use Codeception\Specify;
use Phalcon\Legacy\Crypt;
use Codeception\TestCase\Test;

/**
 * \Phalcon\Test\Legacy\CryptTest
 * Tests for Phalcon\Legacy\Crypt component
 *
 * @copyright (c) 2011-2016 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Serghei Iakovlev <serghei@phalconphp.com>
 * @package   Phalcon\Test\Legacy
 * @group     crypt
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class CryptTest extends Test
{
    use Specify;

    /**
     * UnitTester Object
     * @var UnitTester
     */
    protected $tester;

    public function _before()
    {
        parent::_before();
        if (!extension_loaded('mcrypt')) {
            $this->markTestSkipped('Warning: mcrypt extension is not loaded');
        }
    }


    /**
     * Tests the Crypt constants
     *
     * @author Serghei Iakovlev <serghei@phalconphp.com>
     * @since  2015-12-20
     */
    public function testCryptConstants()
    {
        $this->specify(
            "Crypt constants are not correct",
            function ($const, $expected) {
                expect($const)->equals($expected);
            }, ['examples' => [
                [Crypt::PADDING_DEFAULT,        0],
                [Crypt::PADDING_ANSI_X_923,     1],
                [Crypt::PADDING_PKCS7,          2],
                [Crypt::PADDING_ISO_10126,      3],
                [Crypt::PADDING_ISO_IEC_7816_4, 4],
                [Crypt::PADDING_ZERO,           5],
                [Crypt::PADDING_SPACE,          6],
            ]]
        );
    }

    /**
     * Tests the encryption
     *
     * @author Nikolaos Dimopoulos <nikos@phalconphp.com>
     * @since  2014-10-17
     */
    public function testCryptEncryption()
    {
        $this->specify(
            "encryption does not return correct results",
            function ($key, $test) {
                $modes = [
                    MCRYPT_MODE_ECB,
                    MCRYPT_MODE_CBC,
                    MCRYPT_MODE_CFB,
                    MCRYPT_MODE_OFB,
                    MCRYPT_MODE_NOFB
                ];

                $crypt = new Crypt();
                foreach ($modes as $mode) {
                    $crypt->setMode($mode);
                    $crypt->setKey(substr($key, 0, 16));

                    $encryption = $crypt->encrypt($test);
                    expect(rtrim($crypt->decrypt($encryption), "\0"))->equals($test);

                    $encryption = $crypt->encrypt($test, substr($key, 0, 16));
                    expect(rtrim($crypt->decrypt($encryption, substr($key, 0, 16)), "\0"))->equals($test);
                }
            }, ['examples' => [
                [md5(uniqid()),            str_repeat('x', mt_rand(1, 255))],
                [time().time(),            str_shuffle(join('', range('a', 'z')))],
                ['le$ki12432543543543543', null],
            ]]
        );
    }
    /**
     * Tests the padding
     *
     * @author Nikolaos Dimopoulos <nikos@phalconphp.com>
     * @since  2014-10-17
     */
    public function testCryptPadding()
    {
        $this->specify(
            "padding not return correct results",
            function () {
                $texts = [''];
                $key = '0123456789ABCDEF0123456789ABCDEF';
                $modes = [MCRYPT_MODE_ECB, MCRYPT_MODE_CBC, MCRYPT_MODE_CFB];
                $pads = [
                    Crypt::PADDING_ANSI_X_923,
                    Crypt::PADDING_PKCS7,
                ];

                for ($i = 1; $i < 128; ++$i) {
                    $texts[] = str_repeat('A', $i);
                }

                $crypt = new Crypt();
                $crypt->setCipher(MCRYPT_RIJNDAEL_256)
                    ->setKey(substr($key, 0, 16));

                foreach ($pads as $padding) {
                    $crypt->setPadding($padding);

                    foreach ($modes as $mode) {
                        $crypt->setMode($mode);

                        foreach ($texts as $text) {
                            $encrypted = $crypt->encrypt($text);
                            expect($crypt->decrypt($encrypted))->equals($text);
                        }
                    }
                }
            }
        );
    }
    /**
     * Tests the encryption base 64
     *
     * @author Nikolaos Dimopoulos <nikos@phalconphp.com>
     * @since  2014-10-17
     */
    public function testCryptEncryptBase64()
    {
        $this->specify(
            "encryption base 64does not return correct results",
            function () {
                $crypt = new Crypt();
                $crypt->setPadding(Crypt::PADDING_ANSI_X_923);
                $key      = substr('phalcon notice 13123123', 0, 16);
                $expected = 'https://github.com/phalcon/cphalcon/issues?state=open';

                $encrypted = $crypt->encryptBase64($expected, substr($key, 0, 16));
                expect($crypt->decryptBase64($encrypted, $key))->equals($expected);

                $encrypted = $crypt->encryptBase64($expected, $key, true);
                expect($crypt->decryptBase64($encrypted, $key, true))->equals($expected);
            }
        );
    }
}
