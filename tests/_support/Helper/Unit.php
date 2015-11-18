<?php

namespace Helper;

use Codeception\Module;
use Codeception\TestCase;
use Mockery;

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
     * @var \Codeception\TestCase
     */
    protected $test;

    /**
     * Executed before each test.
     *
     * @param \Codeception\TestCase $test
     */
    public function _before(TestCase $test)
    {
        $this->test = $test;
    }

    /**
     * Executed after each test.
     *
     * @param \Codeception\TestCase $test
     */
    public function _after(TestCase $test)
    {
        Mockery::close();
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
}
