<?php
/**
 * Created by PhpStorm.
 * User: fenikkusu
 * Date: 2/10/18
 * Time: 12:11 AM
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