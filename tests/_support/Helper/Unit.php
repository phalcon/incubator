<?php

namespace Helper;

use ReflectionClass;
use Codeception\Module;
use Codeception\TestInterface;

/**
 * Unit Helper
 *
 * Here you can define custom actions
 * all public methods declared in helper class will be available in $I
 *
 * @package Helper
 */
class Unit extends Module
{
    /**
     * @var TestInterface
     */
    protected $test;

    /**
     * Executed before each test.
     *
     * @param TestInterface $test
     */
    public function _before(TestInterface $test)
    {
        $this->test = $test;
    }

    /**
     * @param mixed  $exceptionName
     * @param string $exceptionMessage
     * @param int    $exceptionCode
     */
    public function setExpectedException($exceptionName, $exceptionMessage = '', $exceptionCode = null)
    {
        $this->test->setExpectedException($exceptionName, $exceptionMessage, $exceptionCode);
    }

    public function getProtectedProperty($obj, $prop)
    {
        $reflection = new ReflectionClass($obj);

        $property = $reflection->getProperty($prop);
        $property->setAccessible(true);

        return $property->getValue($obj);
    }

    public function setProtectedProperty($obj, $prop, $value)
    {
        $reflection = new ReflectionClass($obj);

        $property = $reflection->getProperty($prop);
        $property->setAccessible(true);
        $property->setValue($obj, $value);

        $this->assertEquals($value, $property->getValue($obj));
    }
}
