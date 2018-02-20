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
  | Authors: Phoenix Osiris <phoenix@twistersfury.com>                     |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Test\Test\Traits;

use Phalcon\Test\Codeception\UnitTestCase as Unit;
use Phalcon\Config;
use Phalcon\Test\Traits\UnitTestCase;
use PHPUnit_Framework_MockObject_MockObject;

class UnitTestCaseTest extends Unit
{
    /** @var UnitTestCase|\PHPUnit_Framework_MockObject_MockObject */
    protected $testSubject = null;

    public function _before()
    {
        $this->testSubject = $this->getMockBuilder(
            UnitTestCase::class
        )->getMockForTrait();
    }

    public function testConfig()
    {
        $this->tester->amGoingTo('Confirm Testing Fallback Works');

        /** @var Config|PHPUnit_Framework_MockObject_MockObject $mockConfig */
        $mockConfig = $this->getMockBuilder(Config::class)->getMock();

        $this->assertNull($this->testSubject->getConfig());

        $this->di->set('config', $mockConfig);

        $this->assertSame($this->testSubject, $this->testSubject->setConfig($mockConfig));
        $this->assertSame($mockConfig, $this->testSubject->getConfig());
    }
}