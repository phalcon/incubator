<?php
/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2015 Phalcon Team (http://www.phalconphp.com)       |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file docs/LICENSE.txt.                        |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Stephen Hoogendijk <stephen@tca0.nl>                          |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Test;

use Phalcon\Escaper as PhEscaper;
use Phalcon\Mvc\Dispatcher as PhDispatcher;
use Phalcon\Mvc\Application as PhApplication;
use Phalcon\DiInterface;

abstract class FunctionalTestCase extends ModelTestCase
{
    protected $application;

    /**
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        // Set the dispatcher
        $this->di->setShared(
            'dispatcher',
            function () {
                $dispatcher = new PhDispatcher();
                $dispatcher->setControllerName('test');
                $dispatcher->setActionName('empty');
                $dispatcher->setParams([]);

                return $dispatcher;
            }
        );

        $this->di->set(
            'escaper',
            function () {
                return new PhEscaper();
            }
        );

        if ($this->di instanceof DiInterface) {
            $this->application = new PhApplication($this->di);
        }
    }

    /**
     * Ensures that each test has it's own DI and all globals are purged
     *
     * @return void
     */
    protected function tearDown()
    {
        $this->di->reset();
        $this->application = null;

        $_SESSION = [];
        $_GET =  [];
        $_POST = [];
        $_COOKIE = [];
        $_REQUEST = [];
        $_FILES = [];
    }

    /**
     * Dispatches a given url and sets the response object accordingly
     *
     * @param  string $url The request url
     * @return void
     */
    protected function dispatch($url)
    {
        $this->di->setShared('response', $this->application->handle($url));
    }

    /**
     * Assert that the last dispatched controller matches the given controller class name
     *
     * @param  string $expected The expected controller name
     * @throws \PHPUnit_Framework_ExpectationFailedException
     */
    public function assertController($expected)
    {
        $actual = $this->di->getShared('dispatcher')->getControllerName();
        if ($actual != $expected) {
            throw new \PHPUnit_Framework_ExpectationFailedException(
                sprintf(
                    'Failed asserting Controller name "%s", actual Controller name is "%s"',
                    $expected,
                    $actual
                )
            );
        }

        $this->assertEquals($expected, $actual);
    }

    /**
     * Assert that the last dispatched action matches the given action name
     *
     * @param  string $expected The expected action name
     * @throws \PHPUnit_Framework_ExpectationFailedException
     */
    public function assertAction($expected)
    {
        $actual = $this->di->getShared('dispatcher')->getActionName();
        if ($actual != $expected) {
            throw new \PHPUnit_Framework_ExpectationFailedException(
                sprintf(
                    'Failed asserting Action name "%s", actual Action name is "%s"',
                    $expected,
                    $actual
                )
            );
        }
        $this->assertEquals($expected, $actual);
    }

    /**
     * Assert that the response headers contains the given array
     * <code>
     * $expected = array('Content-Type' => 'application/json')
     * </code>
     *
     * @param  array $expected The expected headers
     * @throws \PHPUnit_Framework_ExpectationFailedException
     */
    public function assertHeader(array $expected)
    {
        foreach ($expected as $expectedField => $expectedValue) {
            $actualValue = $this->di->getShared('response')->getHeaders()->get($expectedField);
            if ($actualValue != $expectedValue) {
                throw new \PHPUnit_Framework_ExpectationFailedException(
                    sprintf(
                        'Failed asserting "%s" has a value of "%s", actual "%s" header value is "%s"',
                        $expectedField,
                        $expectedValue,
                        $expectedField,
                        $actualValue
                    )
                );
            }
            $this->assertEquals($expectedValue, $actualValue);
        }
    }

    /**
     * Asserts that the response code matches the given one
     *
     * @param  string $expected the expected response code
     * @throws \PHPUnit_Framework_ExpectationFailedException
     */
    public function assertResponseCode($expected)
    {
        // convert to string if int
        if (is_integer($expected)) {
            $expected = (string) $expected;
        }

        $actualValue = $this->di->getShared('response')->getHeaders()->get('Status');

        if (empty($actualValue) || stristr($actualValue, $expected) === false) {
            throw new \PHPUnit_Framework_ExpectationFailedException(
                sprintf(
                    'Failed asserting response code is "%s", actual response status is "%s"',
                    $expected,
                    $actualValue
                )
            );
        }

        $this->assertContains($expected, $actualValue);
    }

    /**
     * Asserts that the dispatch is forwarded
     *
     * @throws \PHPUnit_Framework_ExpectationFailedException
     */
    public function assertDispatchIsForwarded()
    {
        /* @var $dispatcher \Phalcon\Mvc\Dispatcher */
        $dispatcher = $this->di->getShared('dispatcher');
        $actual = $dispatcher->wasForwarded();

        if (!$actual) {
            throw new \PHPUnit_Framework_ExpectationFailedException('Failed asserting dispatch was forwarded');
        }

        $this->assertTrue($actual);
    }

    /**
     * Assert location redirect
     *
     * @param  string $location
     * @throws \PHPUnit_Framework_ExpectationFailedException
     */
    public function assertRedirectTo($location)
    {
        $actualLocation = $this->di->getShared('response')->getHeaders()->get('Location');

        if (!$actualLocation) {
            throw new \PHPUnit_Framework_ExpectationFailedException('Failed asserting response caused a redirect');
        }

        if ($actualLocation !== $location) {
            throw new \PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting response redirects to "%s". It redirects to "%s".',
                $location,
                $actualLocation
            ));
        }

        $this->assertEquals($location, $actualLocation);
    }

    /**
     * Convenience method to retrieve response content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->di->getShared('response')->getContent();
    }

    /**
     * Assert response content contains string
     *
     * @param string $string
     */
    public function assertResponseContentContains($string)
    {
        $this->assertContains($string, $this->getContent());
    }
}
