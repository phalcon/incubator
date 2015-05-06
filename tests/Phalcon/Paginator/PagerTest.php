<?php
namespace Phalcon\Tests\Paginator;

class PagerTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatingPagerObjectWithoutOptionsShouldConstructObject()
    {
        $adapter = $this->getMockBuilder('Phalcon\Paginator\Adapter\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $pager = new \Phalcon\Paginator\Pager($adapter);
        $this->assertInstanceOf('Phalcon\Paginator\Pager', $pager);
    }

    public function testCallingGetPagesInRangeMethodWithDefaultOptionsShouldReturnExpectedArray()
    {
        // stub paginate
        $paginate = new \stdClass();
        $paginate->total_pages = 20;
        $paginate->current = 5;
        $paginate->last = 20;

        $adapter = $this->getMockBuilder('Phalcon\Paginator\Adapter\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('getPaginate'))
            ->getMock();
        $adapter->expects($this->once())
            ->method('getPaginate')
            ->will($this->returnValue($paginate));

        $pager = new \Phalcon\Paginator\Pager($adapter);
        $this->assertEquals(
            array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10),
            $pager->getPagesInRange()
        );
    }

    public function testCallingGetPagesInRangeMethodWithSliderOnEndShouldReturnExpectedArray()
    {
        // stub paginate
        $paginate = new \stdClass();
        $paginate->total_pages = 20;
        $paginate->current = 20;
        $paginate->last = 20;

        $adapter = $this->getMockBuilder('Phalcon\Paginator\Adapter\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('getPaginate'))
            ->getMock();
        $adapter->expects($this->once())
            ->method('getPaginate')
            ->will($this->returnValue($paginate));

        $pager = new \Phalcon\Paginator\Pager($adapter, array('rangeLength' => 5));
        $this->assertEquals(
            array(16, 17, 18, 19, 20),
            $pager->getPagesInRange()
        );
    }

    public function testCallingGetPagesInRangeMethodWithSliderOnStartShouldReturnExpectedArray()
    {
        // stub paginate
        $paginate = new \stdClass();
        $paginate->total_pages = 20;
        $paginate->current = 1;
        $paginate->last = 20;

        $adapter = $this->getMockBuilder('Phalcon\Paginator\Adapter\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('getPaginate'))
            ->getMock();
        $adapter->expects($this->once())
            ->method('getPaginate')
            ->will($this->returnValue($paginate));

        $pager = new \Phalcon\Paginator\Pager($adapter, array('rangeLength' => 5));
        $this->assertEquals(
            array(1, 2, 3, 4, 5),
            $pager->getPagesInRange()
        );
    }

    public function testGetLayoutMethodShouldReturnObjectOfLayoutType()
    {
        // stub paginate
        $paginate = new \stdClass();
        $paginate->total_pages = 20;
        $paginate->current = 1;
        $paginate->last = 20;

        $adapter = $this->getMockBuilder('Phalcon\Paginator\Adapter\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('getPaginate'))
            ->getMock();
        $adapter->expects($this->once())
            ->method('getPaginate')
            ->will($this->returnValue($paginate));

        $pager = new \Phalcon\Paginator\Pager(
            $adapter,
            array(
                'rangeLength' => 5,
                'urlMask' => 'xxx',
            )
        );
        $this->assertInstanceOf('Phalcon\Paginator\Pager\Layout', $pager->getLayout());
    }

    public function testPagerGetterMethodsShouldReturnExpectedValues()
    {
        // stub paginate
        $paginate = new \stdClass();
        $paginate->total_pages = 20;
        $paginate->current = 10;
        $paginate->last = 20;
        $paginate->total_items = 100;
        $paginate->first = 1;
        $paginate->before = $paginate->current - 1;
        $paginate->next = $paginate->current + 1;
        $paginate->items = new \ArrayIterator(array(1, 2, 4, 5));

        $adapter = $this->getMockBuilder('Phalcon\Paginator\Adapter\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('getPaginate'))
            ->getMock();
        $adapter->expects($this->once())
            ->method('getPaginate')
            ->will($this->returnValue($paginate));

        $pager = new \Phalcon\Paginator\Pager(
            $adapter,
            array(
                'rangeLength' => 5,
                'urlMask' => 'xxx',
            )
        );
        $this->assertEquals($paginate->current, $pager->getCurrentPage());
        $this->assertEquals($paginate->total_items, $pager->count());
        $this->assertEquals(1, $pager->getFirstPage());
        $this->assertTrue($pager->haveToPaginate());
        $this->assertEquals($paginate->before, $pager->getPreviousPage());
        $this->assertEquals($paginate->next, $pager->getNextPage());
        $this->assertInstanceOf('Iterator', $pager->getIterator());
    }

    public function testPagerGetIteratorMethodWillCreateIteratorIfArrayIsPassed()
    {
        // stub paginate
        $paginate = new \stdClass();
        $paginate->total_pages = 20;
        $paginate->current = 10;
        $paginate->items = array(1, 2, 4, 5);

        $adapter = $this->getMockBuilder('Phalcon\Paginator\Adapter\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('getPaginate'))
            ->getMock();
        $adapter->expects($this->once())
            ->method('getPaginate')
            ->will($this->returnValue($paginate));

        $pager = new \Phalcon\Paginator\Pager(
            $adapter,
            array(
                'rangeLength' => 5,
                'urlMask' => 'xxx',
            )
        );
        $this->assertInstanceOf('Iterator', $pager->getIterator());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage You must provide option "urlMask"
     */
    public function testGetLayoutMethodWithoutUrlMaskOptionShouldThrowException()
    {
        // stub paginate
        $paginate = new \stdClass();
        $paginate->total_pages = 20;
        $paginate->current = 1;

        $adapter = $this->getMockBuilder('Phalcon\Paginator\Adapter\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('getPaginate'))
            ->getMock();
        $adapter->expects($this->once())
            ->method('getPaginate')
            ->will($this->returnValue($paginate));

        $pager = new \Phalcon\Paginator\Pager(
            $adapter,
            array(
                'rangeLength' => 5,
            )
        );
        $pager->getLayout();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unable to find range class "UnknownRangeClass"
     */
    public function testGetLayoutMethodShouldWithInvalidRangeClassShouldThrowException()
    {
        // stub paginate
        $paginate = new \stdClass();
        $paginate->total_pages = 20;
        $paginate->current = 1;

        $adapter = $this->getMockBuilder('Phalcon\Paginator\Adapter\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('getPaginate'))
            ->getMock();
        $adapter->expects($this->once())
            ->method('getPaginate')
            ->will($this->returnValue($paginate));

        $pager = new \Phalcon\Paginator\Pager(
            $adapter,
            array(
                'rangeLength' => 5,
                'rangeClass' => 'UnknownRangeClass',
                'urlMask' => 'xxx',
            )
        );
        $pager->getLayout();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unable to find layout "UnknownLayoutClass"
     */
    public function testGetLayoutMethodShouldWithInvalidLayoutClassShouldThrowException()
    {
        // stub paginate
        $paginate = new \stdClass();
        $paginate->total_pages = 20;
        $paginate->current = 1;


        $adapter = $this->getMockBuilder('Phalcon\Paginator\Adapter\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('getPaginate'))
            ->getMock();
        $adapter->expects($this->once())
            ->method('getPaginate')
            ->will($this->returnValue($paginate));

        $pager = new \Phalcon\Paginator\Pager(
            $adapter,
            array(
                'rangeLength' => 5,
                'layoutClass' => 'UnknownLayoutClass',
                'urlMask' => 'xxx',
            )
        );
        $pager->getLayout();
    }
}
