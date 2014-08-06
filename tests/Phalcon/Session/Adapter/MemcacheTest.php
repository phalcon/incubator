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

    /**
     * @expectedException \PHPUnit_Framework_Error_Notice
     * @expectedExceptionMessage Memcache::connect(): Server 192.0.2.0 (tcp 11211
     *
     */
    public function testExecutingReadMethodWithoutMemcacheServerShouldGetExpectedKey()
    {
        $keyPrefix = 'testprefix';
        $memcacheAdapter = new \Phalcon\Session\Adapter\Memcache(array(
            'host' => '192.0.2.0',
            'prefix' => $keyPrefix,
        ));
        $sessionid = md5(rand(), true);
        $this->assertEquals($keyPrefix . '_' . $sessionid, $memcacheAdapter->read($sessionid));
    }

    public function testExecutingWriteMethodShouldNotRaiseErrors()
    {
        $keyPrefix = 'testprefix';
        $memcacheAdapter = new \Phalcon\Session\Adapter\Memcache(array(
            'host' => '127.0.0.1',
            'prefix' => $keyPrefix,
        ));
        $mockMemcacheInstance = $this->getMockBuilder('Phalcon\Cache\Backend\Memcache')
            ->disableOriginalConstructor()
            ->setMethods(array('save'))
            ->getMock();
        $mockMemcacheInstance->expects($this->once())
            ->method('save')
            ->will($this->returnValue(null));
        $memcacheAdapter->setMemcacheInstance($mockMemcacheInstance);
        $sessionid = md5(rand(), true);
        $this->assertNull($memcacheAdapter->write($sessionid, 'data'));
    }

    public function testExecutingDestroyMethodWithPassedSessionIdShouldDeleteExpectedKey()
    {
        $keyPrefix = 'testprefix';
        $memcacheAdapter = new \Phalcon\Session\Adapter\Memcache(array(
            'host' => '127.0.0.1',
            'prefix' => $keyPrefix,
        ));
        $mockMemcacheInstance = $this->getMockBuilder('Phalcon\Cache\Backend\Memcache')
            ->disableOriginalConstructor()
            ->setMethods(array('delete'))
            ->getMock();
        $mockMemcacheInstance->expects($this->once())
            ->method('delete')
            ->will($this->returnArgument(0));
        $memcacheAdapter->setMemcacheInstance($mockMemcacheInstance);
        $sessionid = md5(rand(), true);
        $this->assertEquals($keyPrefix . '_' . $sessionid, $memcacheAdapter->destroy($sessionid));
    }

    public function testExecutingDestroyMethodWithoutPassedSessionIdShouldDeleteExpectedKey()
    {
        $keyPrefix = 'testprefix';
        $memcacheAdapter = new \Phalcon\Session\Adapter\Memcache(array(
            'host' => '127.0.0.1',
            'prefix' => $keyPrefix,
        ));
        $mockMemcacheInstance = $this->getMockBuilder('Phalcon\Cache\Backend\Memcache')
            ->disableOriginalConstructor()
            ->setMethods(array('delete'))
            ->getMock();
        $mockMemcacheInstance->expects($this->once())
            ->method('delete')
            ->will($this->returnArgument(0));
        $memcacheAdapter->setMemcacheInstance($mockMemcacheInstance);
        $this->assertEquals($keyPrefix . '_', $memcacheAdapter->destroy());
    }

    public function testGettingNonExistentOptionShouldReturnNull()
    {
        $keyPrefix = 'testprefix';
        $memcacheAdapter = new \Phalcon\Session\Adapter\Memcache(array(
            'host' => '127.0.0.1',
            'prefix' => $keyPrefix,
        ));
        $this->assertNull($memcacheAdapter->getOption('nonExistentOption'));
    }

    public function testExecutingOverridenMethods()
    {
        $keyPrefix = 'testprefix';
        $memcacheAdapter = new \Phalcon\Session\Adapter\Memcache(array(
            'host' => '127.0.0.1',
            'prefix' => $keyPrefix,
        ));
        $this->assertTrue($memcacheAdapter->open());
        $this->assertTrue($memcacheAdapter->close());
        $this->assertNull($memcacheAdapter->gc());
    }
} 