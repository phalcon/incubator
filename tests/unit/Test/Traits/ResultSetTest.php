<?php
/**
 * Created by PhpStorm.
 * User: Charles.Martin
 * Date: 12/13/2016
 * Time: 12:41 PM
 */

namespace unit\Test\Traits;

use Phalcon\Test\Traits\ResultSet;
use Codeception\TestCase\Test;

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
