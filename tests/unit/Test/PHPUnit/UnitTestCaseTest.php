<?php
namespace Phalcon\Test\Test\PHPUnit;

use Codeception\Test\Unit;
use Phalcon\Test\PHPUnit\UnitTestCase;
use Phalcon\DiInterface;

/**
 * \Phalcon\Test\Test\PHPUnit\UnitTestCaseTest. Tests Integration With Trait.
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
class UnitTestCaseTest extends Unit
{
    public function testUsesTrait()
    {
        /** @var UnitTestCase $testSubject */
        $testSubject = $this->getMockBuilder(UnitTestCase::class)
            ->disableOriginalConstructor()
            ->getMock();

        $testSubject->setUp();



        $reflectionProperty = new \ReflectionProperty(
            UnitTestCase::class,
            'di'
        );

        $reflectionProperty->setAccessible(true);

        $this->assertInstanceOf(
            DiInterface::class,
            $reflectionProperty->getValue($testSubject)
        );
    }
}
