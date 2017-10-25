<?php

namespace Phalcon\Test\Avatar;

use Phalcon\Avatar\Gravatar;
use Phalcon\Test\Codeception\UnitTestCase as Test;
use Phalcon\Config;

/**
 * \Phalcon\Test\Avatar\GravatarTest
 * Tests for Phalcon\Avatar\Gravatar component
 *
 * @copyright (c) 2011-2016 Phalcon Team
 * @author    Serghei Iakovlev <serghei@phalconphp.com>
 * @link      http://phalconphp.com/
 * @package   Phalcon\Test\Avatar
 * @group     Avatar
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class GravatarTest extends Test
{
    /**
     * @dataProvider incorrectConfigProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Config must be either an array or \Phalcon\Config instance
     * @param mixed $config
     */
    public function testShouldThrowExceptionIfDbIsMissingOrInvalid($config)
    {
        new Gravatar($config);
    }

    public function incorrectConfigProvider()
    {
        return [
            'string'   => [__CLASS__],
            'null'     => [null],
            'true'     => [true],
            'false'    => [false],
            'object'   => [new \stdClass()],
            'float'    => [microtime(true)],
            'int'      => [PHP_INT_MAX],
            'callable' => [function () {}],
            'resource' => [tmpfile()],
        ];
    }

    public function testShouldUseConfigInstance()
    {
        $gravatar = new Gravatar(new Config([]));
        $this->assertInstanceOf('Phalcon\Avatar\Gravatar', $gravatar);
    }

    public function testShouldUseArrayAsConfig()
    {
        $gravatar = new Gravatar([]);
        $this->assertInstanceOf('Phalcon\Avatar\Gravatar', $gravatar);
    }

    public function testShouldSetUseDefaultValues()
    {
        $gravatar = new Gravatar([]);

        $this->assertEquals(Gravatar::RATING_G, $gravatar->getRating());
        $this->assertEquals(80, $gravatar->getSize());
        $this->assertNotTrue($gravatar->isUseSecureURL());
    }

    public function testShouldSetOptionsThroughConfig()
    {
        $gravatar = new Gravatar([
            'default_image'  => 'retro',
            'rating'         => 'x',
            'size'           => 60,
            'use_https'      => true
        ]);

        $this->assertEquals('retro', $gravatar->getDefaultImage());
        $this->assertEquals(Gravatar::RATING_X, $gravatar->getRating());
        $this->assertEquals(60, $gravatar->getSize());
        $this->assertTrue($gravatar->isUseSecureURL());
    }
}
