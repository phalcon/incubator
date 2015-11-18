<?php

namespace Phalcon\Tests\Http;

use Codeception\TestCase\Test;
use Phalcon\Http\Client\Header;
use UnitTester;

/**
 * Class HeaderTest
 * @package Phalcon\Tests\Http
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