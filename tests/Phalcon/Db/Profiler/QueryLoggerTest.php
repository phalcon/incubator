<?php
namespace Phalcon\Tests\Db\Profiler;

class QueryLoggerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test QueryLogger class works as expected.
     */
    public function testQueryLoggerShouldLogWithoutErrors()
    {
        $expectedLogItem = new \stdClass();
        $expectedLogItem->sql = "SELECT * FROM users WHERE id = :id";
        $expectedLogItem->vars = array(':1' => 1);
        $expectedLogItem->execution_time = 0.0003;
        $expectedLogItem->bind_types = array(':1' => \PDO::PARAM_INT);
        $expectedLogItem->host = null;

        // mocking hell :)
        // profiler
        $profilerMock = $this->getMockBuilder('Phalcon\Db\Profiler')
            ->disableOriginalConstructor()
            ->setMethods(array('startProfile', 'stopProfile', 'getLastProfile'))
            ->getMock();
        $profilerMock->expects($this->once())
            ->method('startProfile')
            ->will($this->returnValue(true));
        $profilerMock->expects($this->once())
            ->method('stopProfile')
            ->will($this->returnValue(true));
        // profiler item
        $profilerItemMock = $this->getMockBuilder('Phalcon\Db\Profiler\Item')
            ->disableOriginalConstructor()
            ->setMethods(array('getSQLStatement', 'getSQLVariables', 'getTotalElapsedSeconds', 'getSQLBindTypes'))
            ->getMock();
        $profilerItemMock->expects($this->once())
            ->method('getSQLStatement')
            ->will($this->returnValue($expectedLogItem->sql));
        $profilerItemMock->expects($this->once())
            ->method('getSQLVariables')
            ->will($this->returnValue($expectedLogItem->vars));
        $profilerItemMock->expects($this->once())
            ->method('getTotalElapsedSeconds')
            ->will($this->returnValue($expectedLogItem->execution_time));
        $profilerItemMock->expects($this->once())
            ->method('getSQLBindTypes')
            ->will($this->returnValue($expectedLogItem->bind_types));
        // add profiler item to profiler
        $profilerMock->expects($this->exactly(4))
            ->method('getLastProfile')
            ->will($this->returnValue($profilerItemMock));
        // PDO
        $pdoMock = $this->getMockBuilder('Phalcon\Db\Adapter\Pdo')
            ->disableOriginalConstructor()
            ->getMock();
        // event
        $eventMock = $this->getMockBuilder('Phalcon\Events\Event')
            ->disableOriginalConstructor()
            ->setMethods(array('getType'))
            ->getMock();
        $eventMock->expects($this->at(0))
            ->method('getType')
            ->will($this->returnValue('beforeQuery'));
        $eventMock->expects($this->at(1))
            ->method('getType')
            ->will($this->returnValue('afterQuery'));
        // logger
        $loggerMock = $this->getMockBuilder('Phalcon\Logger')
            ->disableOriginalConstructor()
            ->setMethods(array('log'))
            ->getMock();
        $loggerMock->expects($this->once())
            ->method('log')
            ->will(
                $this->returnCallback(
                    function ($priority, $logItem) use ($expectedLogItem) {
                        $expectedLogItemJson = json_encode($expectedLogItem, JSON_FORCE_OBJECT);
                        if ($expectedLogItemJson !== $logItem) {
                            throw new \PHPUnit_Framework_ExpectationFailedException(
                                sprintf(
                                    'Failed asserting that JSON of expected log item "%s" matches log item JSON "%s"',
                                    $expectedLogItemJson,
                                    $logItem
                                )
                            );
                        }
                    }
                )
            );

        $queryLogger = new \Phalcon\Db\Profiler\QueryLogger();
        $queryLogger->setLogger($loggerMock);
        $queryLogger->setProfiler($profilerMock);
        $queryLogger->setPriority(\Phalcon\Logger::DEBUG);

        // test beforeQuery
        $queryLogger->beforeQuery($eventMock, $pdoMock);
        $this->assertInstanceOf('Phalcon\Db\Profiler\QueryLogger', $queryLogger);

        // test afterQuery
        $queryLogger->afterQuery($eventMock, $pdoMock);
        $this->assertInstanceOf('Phalcon\Db\Profiler\QueryLogger', $queryLogger);

        $this->assertInstanceOf('Phalcon\Logger', $queryLogger->getLogger());
        $this->assertInstanceOf('Phalcon\Db\Profiler', $queryLogger->getProfiler());
        $this->assertEquals(\Phalcon\Logger::DEBUG, $queryLogger->getPriority());
    }

    /**
     * Tests that if beforeQuery method is called with wrong event type \LogicException will be raised.
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage Method is expected to be called only on "beforeQuery" event.
     */
    public function testQueryLoggerBeforeQueryListenerMethodWillThrowExceptionOnInvalidEvent()
    {
        // PDO
        $pdoMock = $this->getMockBuilder('Phalcon\Db\Adapter\Pdo')
            ->disableOriginalConstructor()
            ->getMock();
        // event
        $eventMock = $this->getMockBuilder('Phalcon\Events\Event')
            ->disableOriginalConstructor()
            ->setMethods(array('getType'))
            ->getMock();
        $eventMock->expects($this->at(0))
            ->method('getType')
            ->will($this->returnValue('afterQuery'));

        $queryLogger = new \Phalcon\Db\Profiler\QueryLogger();
        $queryLogger->beforeQuery($eventMock, $pdoMock);
    }

    /**
     * Tests that if afterQuery method is called with wrong event type \LogicException will be raised.
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage Method is expected to be called only on "afterQuery" event.
     */
    public function testQueryLoggerAfterQueryListenerMethodWillThrowExceptionOnInvalidEvent()
    {
        // PDO
        $pdoMock = $this->getMockBuilder('Phalcon\Db\Adapter\Pdo')
            ->disableOriginalConstructor()
            ->getMock();
        // event
        $eventMock = $this->getMockBuilder('Phalcon\Events\Event')
            ->disableOriginalConstructor()
            ->setMethods(array('getType'))
            ->getMock();
        $eventMock->expects($this->at(0))
            ->method('getType')
            ->will($this->returnValue('beforeQuery'));

        $queryLogger = new \Phalcon\Db\Profiler\QueryLogger();
        $queryLogger->afterQuery($eventMock, $pdoMock);
    }
}
