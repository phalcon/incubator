<?php
namespace Phalcon\Tests\Session\Adapter;

class MemcacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Phalcon\Session\Exception
     * @expectedExceptionMessage No configuration given
     */
    public function testCreatingObjectWithoutConfigurationShouldThrowException()
    {
        $memcacheAdapter = new \Phalcon\Session\Adapter\Memcache();
    }

    /**
     * @expectedException \Phalcon\Session\Exception
     * @expectedExceptionMessage No session host given in options
     */
    public function testCreatingObjectWithoutConfigurationHostKeyShouldThrowException()
    {
        $memcacheAdapter = new \Phalcon\Session\Adapter\Memcache(array(
            'port' => 11211,
        ));
    }

    public function testCreatingObjectWithoutRequiredParamsWillFallbackToDefaultOnes()
    {
        $memcacheAdapter = new \Phalcon\Session\Adapter\Memcache(array(
            'host' => '127.0.0.1',
        ));
        $this->assertEquals(
            \Phalcon\Session\Adapter\Memcache::DEFAULT_OPTION_PORT,
            $memcacheAdapter->getOption('port')
        );
        $this->assertEquals(
            \Phalcon\Session\Adapter\Memcache::DEFAULT_OPTION_LIFETIME,
            $memcacheAdapter->getOption('lifetime')
        );
        $this->assertEquals(
            \Phalcon\Session\Adapter\Memcache::DEFAULT_OPTION_PERSISTENT,
            $memcacheAdapter->getOption('persistent')
        );
        $this->assertEquals(
            \Phalcon\Session\Adapter\Memcache::DEFAULT_OPTION_PREFIX,
            $memcacheAdapter->getOption('prefix')
        );
    }

    public function testReadMethodShouldQueryKeyWithPrefix()
    {
        $keyPrefix = 'testprefix';
        $memcacheAdapter = new \Phalcon\Session\Adapter\Memcache(array(
            'host' => '127.0.0.1',
            'prefix' => $keyPrefix,
        ));
        $mockMemcacheInstance = $this->getMockBuilder('Phalcon\Cache\Backend\Memcache')
            ->disableOriginalConstructor()
            ->getMock();
        $mockMemcacheInstance->expects($this->once())
            ->method('get')
            ->will($this->returnArgument(0));
        $memcacheAdapter->setMemcacheInstance($mockMemcacheInstance);
        $sessionid = md5(rand(), true);
        $this->assertEquals($keyPrefix . '_' . $sessionid, $memcacheAdapter->read($sessionid));
    }
} 