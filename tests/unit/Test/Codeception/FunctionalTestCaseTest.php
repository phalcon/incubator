<?php
namespace Phalcon\Test\Test\Codeception;

use Codeception\Lib\Di;
use Codeception\Test\Metadata;
use Codeception\Test\Unit;
use Phalcon\Test\Codeception\FunctionalTestCase;
use Phalcon\Mvc\Dispatcher as PhDispatcher;

/**
 * \Phalcon\Test\Test\Codeception\FunctionalTestCaseTest. Tests Integration With Trait.
 *
 * @copyright (c) 2011-2016 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Phoenix Osiris <phoenix@twistersfury.com>
 * @package   Phalcon\Test\Test\Traits
 * @group     Acl
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class FunctionalTestCaseTest extends Unit
{

// @todo fix interface error
//    public function testUsesTrait()
//    {
//        $mockService = $this->getMockBuilder(Di::class)
//                            ->disableOriginalConstructor()
//                            ->getMock();
//
//        $mockMeta = $this->getMockBuilder(Metadata::class)
//            ->disableOriginalConstructor()
//            ->setMethods(['getService'])
//            ->getMock();
//
//        $mockMeta->method('getService')
//            ->willReturn($mockService);
//
//        /** @var FunctionalTestCase|\PHPUnit_Framework_MockObject_MockObject $testSubject */
//        $testSubject = $this->getMockBuilder(FunctionalTestCase::class)
//            ->disableOriginalConstructor()
//            ->setMethods(['getMetadata'])
//            ->getMock();
//
//        $testSubject->method('getMetadata')
//            ->willReturn($mockMeta);
//
//        $testSubject->setUp();
//
//        $reflectionProperty = new \ReflectionProperty(FunctionalTestCase::class, 'di');
//        $reflectionProperty->setAccessible(true);
//
//        $this->assertInstanceOf(
//            PhDispatcher::class,
//            $reflectionProperty->getValue($testSubject)->get('dispatcher')
//        );
//    }
}
