<?php

namespace Phalcon\Annotations\Adapter;

/**
 * \Phalcon\Annotations\Adapter\BaseTest
 * Tests for class \Phalcon\Annotations\Adapter\Base
 *
 * @category Phalcon
 * @package  Phalcon\Annotations\Adapter
 * @author   Ilya Gusev <mail@igusev.ru>
 * @license  New BSD License
 * @link     http://phalconphp.com/
 */
class BaseTest extends \PHPUnit_Framework_TestCase
{

    protected $classname = '\Phalcon\Annotations\Adapter\Base';

    public function getOptions()
    {
        return array(
            array(array('lifetime' => 23), array('lifetime' => 23, 'prefix' => '')),
            array(array('prefix' => 'test_'), array('lifetime' => 8600, 'prefix' => 'test_')),
            array(array('randomValue' => 'test_'), array('randomValue' => 'test_', 'lifetime' => 8600, 'prefix' => '')),
            array(array(123 => 'test_'), array(123 => 'test_', 'lifetime' => 8600, 'prefix' => '')),
            array(array('lifetime' => 24, 'prefix' => 'test_'), array('lifetime' => 24, 'prefix' => 'test_')),
            array(array(), array('lifetime' => 8600, 'prefix' => '')),
            array(null, array('lifetime' => 8600, 'prefix' => ''))
        );
    }

    /**
     * @dataProvider getOptions
     */
    public function testConstructor($options, $expected)
    {
        $mock = $this->getMockForAbstractClass($this->classname, array('options' => $options), '', true,
            true, true, array(), true);
        $reflectedProperty = new \ReflectionProperty(get_class($mock), 'options');
        $reflectedProperty->setAccessible(true);
        $this->assertEquals($expected, $reflectedProperty->getValue($mock));
    }


}
