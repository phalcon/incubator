<?php
namespace Phalcon\Test\Mvc\Model\MetaData;

class BaseTest extends \PHPUnit_Framework_TestCase
{
    public function testBaseMetaDataObjectImplementsMetaDataInterface()
    {
        $stub = new \Phalcon\Test\Mvc\Model\MetaData\BaseMetaDataStub();
        $this->assertInstanceOf('Phalcon\Mvc\Model\MetaDataInterface', $stub);
    }

    public function testRedisMetaDataAdapterImplementsMetaDataInterface()
    {
        $redisMock = $this->getMockBuilder('Phalcon\Mvc\Model\MetaData\Redis')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertInstanceOf('Phalcon\Mvc\Model\MetaDataInterface', $redisMock);
    }
}

class BaseMetaDataStub extends \Phalcon\Mvc\Model\MetaData\Base
{
    /**
     * Returns cache backend instance.
     *
     * @return \Phalcon\Cache\BackendInterface
     */
    protected function getCacheBackend()
    {
        return new \Phalcon\Cache\Backend\Memory();
    }
}
