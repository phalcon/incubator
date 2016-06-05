<?php

namespace Phalcon\Tests\Http\Client;

use Codeception\TestCase\Test;
use Phalcon\Http\Client\Header;
use UnitTester;

/**
 * \Phalcon\Tests\Http\Client\HeaderTest
 * Tests for Phalcon\Http\Client\Header
 *
 * @copyright (c) 2011-2016 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Ruslan Khaibullin
 * @package   Phalcon\Http\Client\Header
 * @group     Http
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class HeaderTest extends Test
{
    /**
     * UnitTester Object
     * @var UnitTester
     */
    protected $tester;

    /**
     * executed before each test
     */
    protected function _before()
    {
    }

    /**
     * executed after each test
     */
    protected function _after()
    {
    }

    public function testHeaderParsedCorrectlyBothWithAndWithoutMessage()
    {
        $stringHeaderWithMessage = "HTTP/1.1 200 OK\r\nDate: Fri, 06 Nov 2015 10:30:15 GMT\r\nServer: Apache\r\nX-Server: http-devel, test.test\r\nCache-Control: max-age=0\r\nExpires: Fri, 06 Nov 2015 10:30:15 GMT\r\nX-Server: nb\r\nContent-Type: application/json;charset=UTF-8\r\nTransfer-Encoding: chunked";
        $stringHeaderNoMessage = "HTTP/1.1 550";

        $testData = [
            $stringHeaderWithMessage => [
                "statusCode" => 200,
                "statusMessage" => "OK",
                "status" => "HTTP/1.1 200 OK",
            ],
            $stringHeaderNoMessage => [
                "statusCode" => 550,
                "statusMessage" => "",
                "status" => "HTTP/1.1 550",
            ],
        ];

        foreach ($testData as $stringHeader => $expected) {
            $header = new Header();
            $header->parse($stringHeader);
            $this->assertEquals($header->statusCode, $expected["statusCode"]);
            $this->assertEquals($header->statusMessage, $expected["statusMessage"]);
            $this->assertEquals($header->status, $expected["status"]);
        }
    }
}
