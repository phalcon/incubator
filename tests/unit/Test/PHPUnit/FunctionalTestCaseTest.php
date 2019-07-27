<?php
namespace Phalcon\Test\Test\PHPUnit;

use Codeception\Test\Unit;
use Phalcon\Test\PHPUnit\FunctionalTestCase;
use Phalcon\Mvc\Dispatcher as PhDispatcher;

/**
 * \Phalcon\Test\Test\PHPUnit\FunctionalTestCaseTest. Tests Integration With Trait.
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
    public function testUsesTrait()
    {
        /** @var FunctionalTestCase $testSubject */
        $testSubject = $this->getMockBuilder(FunctionalTestCase::class)
            ->disableOriginalConstructor()
            ->getMock();

        $testSubject->setUp();



        $reflectionProperty = new \ReflectionProperty(
            FunctionalTestCase::class,
            'di'
        );

        $reflectionProperty->setAccessible(true);

        $this->assertInstanceOf(
            PhDispatcher::class,
            $reflectionProperty->getValue($testSubject)->get('dispatcher')
        );
    }
}
