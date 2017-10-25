<?php

namespace Phalcon\Test\Annotations\Extended\Adapter;

use ReflectionMethod;
use ReflectionProperty;
use Phalcon\Test\Codeception\UnitTestCase as Test;
use Phalcon\Annotations\Reflection;
use Phalcon\Annotations\Extended\Adapter\Memory;

class MemoryTest extends Test
{
    /** @test */
    public function shouldReadFromMemoryWithoutAnyAdditionalParameter()
    {
        $reflection = $this->getReflection();
        $annotations = new Memory();

        $this->haveInMemory($annotations, 'read-1', $reflection);
        $this->assertEquals($reflection, $annotations->read('read-1'));
    }

    /** @test */
    public function shouldWriteToTheMemoryWithoutAnyAdditionalParameter()
    {
        $reflection = $this->getReflection();
        $annotations = new Memory();

        $this->assertTrue($annotations->write('write-1', $reflection));
        $this->assertEquals($reflection, $this->grabValueFromMemory($annotations, 'write-1'));
    }

    /** @test */
    public function shouldFlushTheMemoryStorageWithoutAnyAdditionalParameter()
    {
        $reflection = $this->getReflection();
        $annotations = new Memory();

        $this->haveInMemory($annotations, 'flush-1', $reflection);

        $this->assertTrue($annotations->flush());
        $this->dontSeeInMemory($annotations, 'flush-1');
    }

    /** @test */
    public function shouldReadAndWriteFromMemoryWithoutAnyAdditionalParameter()
    {
        $reflection = $this->getReflection();
        $annotations = new Memory();

        $this->assertTrue($annotations->write('read-write-1', $reflection));
        $this->assertEquals($reflection, $annotations->read('read-write-1'));
        $this->assertEquals($reflection, $this->grabValueFromMemory($annotations, 'read-write-1'));
    }

    /**
     * @test
     * @dataProvider providerKey
     * @param mixed       $key
     * @param string      $expected
     */
    public function shouldGetValueFromMemoryByUsingPrefixedIdentifier($key, $expected)
    {
        $annotations = new Memory();
        $reflectedMethod = new ReflectionMethod(get_class($annotations), 'getPrefixedIdentifier');
        $reflectedMethod->setAccessible(true);

        $this->assertEquals($expected, $reflectedMethod->invoke($annotations, $key));
    }

    public function providerKey()
    {
        return [
            ['Key1', 'key1'],
            ['KEY',  'key' ],
            [1,      '1'   ],
            ['____', '____'],
        ];
    }

    protected function getReflection()
    {
        return Reflection::__set_state([
            '_reflectionData' => [
                'class'      => [],
                'methods'    => [],
                'properties' => [],
            ]
        ]);
    }

    protected function haveInMemory($object, $key, $value)
    {
        $reflectedProperty = new ReflectionProperty(get_class($object), 'data');
        $reflectedProperty->setAccessible(true);

        $data = $reflectedProperty->getValue($object);
        $data[$key] = $value;

        $reflectedProperty->setValue($object, $data);
    }

    protected function grabValueFromMemory($object, $key)
    {
        $reflectedProperty = new ReflectionProperty(get_class($object), 'data');
        $reflectedProperty->setAccessible(true);

        $data = $reflectedProperty->getValue($object);

        return $data[$key];
    }

    protected function dontSeeInMemory($object, $key, $value = false)
    {
        $reflectedProperty = new ReflectionProperty(get_class($object), 'data');
        $reflectedProperty->setAccessible(true);

        $data = $reflectedProperty->getValue($object);

        if ($value === false) {
            $this->assertArrayNotHasKey($key, $data);
        } else {
            $this->assertSame($value, $data[$key]);
        }
    }
}
