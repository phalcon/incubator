<?php

namespace Phalcon\Test\Annotations\Extended\Adapter;

use ReflectionMethod;
use Phalcon\Test\Codeception\UnitTestCase as Test;
use Phalcon\Annotations\Reflection;
use Phalcon\Annotations\Extended\Adapter\Apc;

class ApcTest extends Test
{
    /**
     * executed before each test
     */
    protected function _before()
    {
        if (!extension_loaded('apc')) {
            $this->markTestSkipped('Warning: apc extension is not loaded');
        }

        if (!ini_get('apc.enabled') || (PHP_SAPI === 'cli' && !ini_get('apc.enable_cli'))) {
            $this->markTestSkipped('Warning: apc.enable_cli must be set to "On"');
        }

        if (extension_loaded('apcu') && version_compare(phpversion('apcu'), '5.1.6', '=')) {
            $this->markTestSkipped('Warning: APCu v5.1.6 was broken. See: https://github.com/krakjoe/apcu/issues/203');
        }
    }

    /** @test */
    public function shouldReadFromApcWithoutAnyAdditionalParameter()
    {
        $reflection = $this->getReflection();
        $annotations = new Apc();

        $this->tester->haveInApc('_PHAN' . 'read-1', $reflection);
        $this->assertEquals($reflection, $annotations->read('read-1'));
    }

    /** @test */
    public function shouldReadFromApcWithPrefix()
    {
        $reflection = $this->getReflection();
        $annotations = new Apc(['prefix' => 'prefix-']);

        $this->tester->haveInApc('_PHAN' . 'prefix-read-2', $reflection);
        $this->assertEquals($reflection, $annotations->read('read-2'));
    }

    /** @test */
    public function shouldWriteToTheApcWithoutAnyAdditionalParameter()
    {
        $reflection = $this->getReflection();
        $annotations = new Apc();

        $this->assertTrue($annotations->write('write-1', $reflection));
        $this->assertEquals($reflection, $this->tester->grabValueFromApc('_PHAN' . 'write-1'));
    }

    /** @test */
    public function shouldWriteToTheApcWithPrefix()
    {
        $reflection = $this->getReflection();
        $annotations = new Apc(['prefix' => 'prefix-']);

        $this->assertTrue($annotations->write('write-2', $reflection));
        $this->assertEquals($reflection, $this->tester->grabValueFromApc('_PHAN' . 'prefix-write-2'));
    }

    /** @test */
    public function shouldFlushTheApcStorageWithoutAnyAdditionalParameter()
    {
        $reflection = $this->getReflection();
        $annotations = new Apc();

        $this->tester->haveInApc('_PHAN' . 'flush-1', $reflection);
        $this->tester->haveInApc('_ANOTHER' . 'flush-1', $reflection);

        $this->assertTrue($annotations->flush());
        $this->tester->dontSeeInApc('_PHAN' . 'flush-1');
        $this->tester->seeInApc('_ANOTHER' . 'flush-1', $reflection);
    }

    /** @test */
    public function shouldFlushTheApcStorageWithPrefix()
    {
        $reflection = $this->getReflection();
        $annotations = new Apc(['prefix' => 'prefix-']);

        $this->tester->haveInApc('_PHAN' . 'prefix-flush-2', $reflection);
        $this->tester->haveInApc('_ANOTHER' . 'prefix-flush-2', $reflection);

        $this->assertTrue($annotations->flush());
        $this->tester->dontSeeInApc('_PHAN' . 'prefix-flush-2');
        $this->tester->seeInApc('_ANOTHER' . 'prefix-flush-2', $reflection);
    }

    /** @test */
    public function shouldReadAndWriteFromApcWithoutAnyAdditionalParameter()
    {
        $reflection = $this->getReflection();
        $annotations = new Apc();

        $this->assertTrue($annotations->write('read-write-1', $reflection));
        $this->assertEquals($reflection, $annotations->read('read-write-1'));
        $this->assertEquals($reflection, $this->tester->grabValueFromApc('_PHAN' . 'read-write-1'));
    }

    /** @test */
    public function shouldReadAndWriteFromApcWithPrefix()
    {
        $reflection = $this->getReflection();
        $annotations = new Apc(['prefix' => 'prefix-']);

        $this->assertTrue($annotations->write('read-write-2', $reflection));
        $this->assertEquals($reflection, $annotations->read('read-write-2'));
        $this->assertEquals($reflection, $this->tester->grabValueFromApc('_PHAN' . 'prefix-read-write-2'));
    }

    /**
     * @test
     * @dataProvider providerKey
     * @param mixed       $key
     * @param string      $prefix
     * @param string|null $statsKey
     * @param string      $expected
     */
    public function shouldGetValueFromApcByUsingPrefixedIdentifier($key, $prefix, $statsKey, $expected)
    {
        if ($statsKey === null) {
            $options = ['prefix' => $prefix];
        } else {
            $options = ['prefix' => $prefix, 'statsKey' => $statsKey];
        }

        $annotations = new Apc($options);
        $reflectedMethod = new ReflectionMethod(get_class($annotations), 'getPrefixedIdentifier');
        $reflectedMethod->setAccessible(true);

        $this->assertEquals($expected, $reflectedMethod->invoke($annotations, $key));
    }

    public function providerKey()
    {
        return [
            ['key1',  '',       null,    '_PHANkey1'      ],
            ['key1',  '-some-', '_PHAN', '_PHAN-some-key1'],
            [1,       '',       null,    '_PHAN1'         ],
            [1,       2,        '_PHAN', '_PHAN21'        ],
            ['_key1', '',       null,    '_PHAN_key1'     ],
            ['_key1', '/',      '_PHAN', '_PHAN/_key1'    ],
            ['key1',  '#',      null,    '_PHAN#key1'     ],
            ['key1',  '',       '',      'key1'           ],
            ['key1',  '',       '_XXX',  '_XXXkey1'       ],
            ['key1',  'xxx-',   '',      'xxx-key1'       ],
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
}
