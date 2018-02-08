<?php
namespace Phalcon\Test\Test;

use Codeception\Test\Unit;
use Phalcon\Test\FunctionalTestCase;
use Phalcon\Test\PHPUnit\FunctionalTestCase as PHPUnitTestCase;

/**
 * \Phalcon\Test\Test\FunctionalTestCaseTest. Test That Old Placement Still Works Correctly.
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
    /** @var UnitTester */
    protected $tester;

    public function testUnitPlaceholder()
    {
        $this->tester->amGoingTo('Confirm That Place Holder Is Fine');

        $testSubject = $this->getMockBuilder(FunctionalTestCase::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->assertInstanceOf(PHPUnitTestCase::class, $testSubject);
    }
}
