<?php
namespace Phalcon\Test\Test\Traits;

use Phalcon\Test\Traits\ResultSet;
use Codeception\TestCase\Test;

/**
 * \Phalcon\Test\Test\Traits\ResultSetTest
 * Tests for Phalcon\Test\Traits\ResultSet component
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
class ResultSetTest extends Test
{
    use ResultSet;

    /** @var \Phalcon\Test\Traits\ResultSet  */
    protected $testSubject = NULL;

    public function setUp() {
        $this->testSubject = $this;
    }

    public function testCanMockResultSet() {
        $mockModel = $this->getMockBuilder('\Phalcon\Mvc\Model')
            ->setMockClassName('ClassA')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSecondModel = $this->getMockBuilder('\Phalcon\Mvc\Model')
            ->setMockClassName('ClassB')
            ->disableOriginalConstructor()
            ->getMock();

        $mockThirdModel = $this->getMockBuilder('\Phalcon\Mvc\Model')
            ->setMockClassName('ClassC')
            ->disableOriginalConstructor()
            ->getMock();

        $mockData = [
            $mockModel,
            $mockSecondModel,
            $mockThirdModel,
        ];

        /** @var \Phalcon\Mvc\Model\Resultset $mockResultSet */
        $mockResultSet = $this->testSubject->mockResultSet($mockData);

        //Testing Count
        $this->assertEquals(3, $mockResultSet->count());

        //Testing Rewind/Valid/Current/Key/Next
        foreach($mockResultSet as $currentKey => $testModel) {
            $this->assertSame($mockData[$currentKey], $testModel);
        }

        //Testing getFirst
        $this->assertSame($mockModel, $mockResultSet->getFirst());

        //Testing getLast
        $this->assertSame($mockThirdModel, $mockResultSet->getLast());

        //Testing toArray
        $this->assertSame($mockData, $mockResultSet->toArray());
    }

    public function testCanMockEmptyResultSet() {
        /** @var \Phalcon\Mvc\Model\Resultset $mockResultSet */
        $mockResultSet = $this->testSubject->mockResultset([]);

        $this->assertEquals(0, $mockResultSet->count());
        $this->assertFalse($mockResultSet->getFirst());
        $this->assertFalse($mockResultSet->getLast());
    }

    public function testCanUseOtherResultSetClasses() {
        $mockResultset = $this->mockResultset([], '\Phalcon\Mvc\Model\Resultset\Simple');
        $this->assertInstanceOf('\Phalcon\Mvc\Model\Resultset\Simple', $mockResultset);
    }
}
