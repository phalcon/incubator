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
}