<?php
/**
 * FunctionalTestCase.php
 * Phalcon_Test_FunctionalTestCase
 * Functional Test Helper
 * PhalconPHP Framework
 *
 * @copyright (c) 2011-2012 Phalcon Team
 * @link          http://www.phalconphp.com
 * @author        Andres Gutierrez <andres@phalconphp.com>
 * @author        Nikolaos Dimopoulos <nikos@phalconphp.com>
 *                The contents of this file are subject to the New BSD License that is
 *                bundled with this package in the file docs/LICENSE.txt
 *                If you did not receive a copy of the license and are unable to obtain it
 *                through the world-wide-web, please send an email to license@phalconphp.com
 *                so that we can send you a copy immediately.
 */

namespace Phalcon\Test;

use Phalcon\Escaper as PhEscaper;
use Phalcon\Mvc\Dispatcher as PhDispatcher;
use Phalcon\Mvc\Application as PhApplication;

abstract class FunctionalTestCase extends ModelTestCase
{
	protected $application;

	/**
	 * Sets the test up by loading the DI container and other stuff
	 * @param \Phalcon\DiInterface $di
         * @param \Phalcon\Config $config
	 * @return void
	 */
	protected function setUp(\Phalcon\DiInterface $di = null, \Phalcon\Config $config = null)
	{
		parent::setUp($di, $config);

		// Set the dispatcher
		$this->di->setShared(
			'dispatcher',
			function () {
				$dispatcher = new PhDispatcher();
				$dispatcher->setControllerName('test');
				$dispatcher->setActionName('empty');
				$dispatcher->setParams(array());
				return $dispatcher;
			}
		);

		$this->di->set(
			'escaper',
			function () {
				return new PhEscaper();
			}
		);

		if ($this->di instanceof \Phalcon\DiInterface) {
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

		$_SESSION = array();
		$_GET = array();
		$_POST = array();
		$_COOKIE = array();
	}

	/**
	 * Dispatches a given url and sets the response object accordingly
	 *
	 * @param string $url The request url
	 * @return void
	 */
	protected function dispatch($url)
	{
		$this->di->setShared('response', $this->application->handle($url));
	}

	/**
	 * Assert that the last dispatched controller matches the given controller class name
	 *
	 * @param string $expected The expected controller name
	 * @return void
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
	 * @param string $expected The expected action name
	 * @return void
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
	 * @param string $expected The expected headers
	 * @return void
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
	 * @param string $expected the expected response code
	 * @return void
	 */
	public function assertResponseCode($expected)
	{
		$actualValue = $this->di->getShared('response')->getHeaders()->get('Status');
		if (empty($actualValue) || stristr($actualValue, $expected)) {
			throw new \PHPUnit_Framework_ExpectationFailedException(
				sprintf(
					'Failed asserting response code "%s", actual response code is "%s"',
					$expected,
					$actualValue
				)
			);
		}
		$this->assertContains($expected, $actualValue);
	}

	/**
	 * Asserts that the dispatched url resulted in a redirection
	 *
	 * @return void
	 */
	public function assertRedirection()
	{
		$actual = $this->di->getShared('dispatcher')->wasForwarded();
		if (!$actual) {
			throw new \PHPUnit_Framework_ExpectationFailedException(
				'Failed asserting response caused a redirect'
			);
		}
		$this->assertTrue($actual);
	}
        
        public function assertRedirectTo($location)
        {
            $actualLocation = $this->di->getShared('response')->getHeaders()->get('Location');
            if (!$actualLocation) {
                throw new \PHPUnit_Framework_ExpectationFailedException('Failed asserting response caused a redirect');
            }
            if ($actualLocation !== $location) {
                throw new \PHPUnit_Framework_ExpectationFailedException(sprintf('Failed asserting response redirects to "%s". It redirects to "%s".', $location, $actualLocation));
            }
            
            $this->assertEquals($location, $actualLocation);
        }
        
        /**
         * Convenience method to retireve response content 
         * @return string
         */
        public function getContent()
        {
            return $this->di->getShared('response')->getContent();
        }
        
        /**
         * Assert response content contains $string
         * @param string $string
         */
        public function assertResponseContentContains($string)
        {
            $this->assertContains($string, $this->getContent());
        }
}
