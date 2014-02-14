<?php
namespace Phalcon\Tests\Logger\Adapter;

class MemcacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests that adapter will properly save value in memcache.
     */
    public function testInternalLoggingShouldSetValueInMemcacheBackend()
    {
        $memcache = $this->getMockBuilder('Phalcon\Cache\Backend\Memcache')
            ->disableOriginalConstructor()
            ->setMethods(array('save', 'get'))
            ->getMock();

        $memcache->expects($this->once())
            ->method('get')
            ->will($this->returnValue(false));

        $memcache->expects($this->once())
            ->method('save')
        ->will(
                $this->returnCallback(
                    function ($cacheKey, $json) {
                        $decoded = json_decode($json);
                        $this->assertEquals('ALERT', $decoded->type);
                        $this->assertEquals('Some log message', $decoded->message);
                        $this->assertInternalType('int', $decoded->timestamp);
                    })
            );

        $formatter = new \Phalcon\Logger\Formatter\Json();
        $logger = new \Phalcon\Logger\Adapter\Memcache($memcache);
        $logger->setFormatter($formatter);
        $logger->log('Some log message', \Phalcon\Logger::ALERT);
    }

    /**
     * Tests that adapter will properly save value in memcache when older item already exists.
     */
    public function testInternalLoggingWithPreviousItemsShouldSetValueInMemcacheBackend()
    {
        $memcache = $this->getMockBuilder('Phalcon\Cache\Backend\Memcache')
            ->disableOriginalConstructor()
            ->setMethods(array('get', 'save'))
            ->getMock();

        $memcache->expects($this->once())
            ->method('get')
            ->will(
                $this->returnValue(
                    json_encode(array(
                           'type' => 'ALERT',
                           'message' => 'Some log message',
                           'timestamp' => 417225600,
                        )
                    )
                )
            );

        $memcache->expects($this->once())
            ->method('save')
            ->will(
                $this->returnCallback(
                    function ($cacheKey, $log) {
                        $parts = explode(PHP_EOL, $log);

                        $log1 = json_decode($parts[0]);
                        $this->assertEquals('ALERT', $log1->type);
                        $this->assertEquals('Some log message', $log1->message);
                        $this->assertEquals(417225600, $log1->timestamp);

                        $log2 = json_decode($parts[1]);
                        $this->assertEquals('CRITICAL', $log2->type);
                        $this->assertEquals('Some other log message', $log2->message);
                        $this->assertInternalType('int', $log2->timestamp);
                    })
                );

        $formatter = new \Phalcon\Logger\Formatter\Json();
        $logger = new \Phalcon\Logger\Adapter\Memcache($memcache);
        $logger->setFormatter($formatter);
        $logger->log('Some other log message', \Phalcon\Logger::CRITICAL);
    }

    /**
     * Tests that adapter will throw exception upon calling not supported formatter.
     *
     * @expectedException \Phalcon\Logger\Exception
     * @expectedExceptionMessage Only Json formatter is supported with memcache logger adapter.
     */
    public function testSettingOtherFormatterThenJsonWillThrowException()
    {
        $memcache = $this->getMockBuilder('Phalcon\Cache\Backend\Memcache')
            ->disableOriginalConstructor()
            ->getMock();

        $logger = new \Phalcon\Logger\Adapter\Memcache($memcache);
        $formatter = new \Phalcon\Logger\Formatter\Line();
        $logger->setFormatter($formatter);
        $logger->log('log message', \Phalcon\Logger::EMERGENCE);
    }

    /**
     * Test untested getter methods.
     */
    public function testAdaptersGetterMethodsShouldReturnExpectedValues()
    {
        $memcache = $this->getMockBuilder('Phalcon\Cache\Backend\Memcache')
            ->disableOriginalConstructor()
            ->getMock();

        $logger = new \Phalcon\Logger\Adapter\Memcache($memcache, 60, 'other.cache.key');
        $this->assertInstanceOf('Phalcon\Cache\Backend\Memcache', $logger->getMemcache());
        $this->assertEquals(60, $logger->getTtl());
        $this->assertEquals('other.cache.key', $logger->getCacheKey());
    }
} 