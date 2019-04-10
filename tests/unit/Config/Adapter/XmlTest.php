<?php

namespace Phalcon\Test\Config\Adapter;

use Codeception\Specify;
use Phalcon\Test\Codeception\UnitTestCase as Test;
use Phalcon\Config\Adapter\Xml;

/**
 * \Phalcon\Test\Config\Adapter\XmlTest
 * Tests for Phalcon\Config\Adapter\Xml component
 *
 * @copyright (c) 2011-2016 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Serghei Iakovlev <serghei@phalconphp.com>
 * @package   Phalcon\Test\Config\Adapter
 * @group     config
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class XmlTest extends Test
{
    use Specify;

    /**
     * executed before each test
     */
    protected function _before()
    {
        if (!extension_loaded('SimpleXML')) {
            $this->markTestSkipped("SimpleXML extension not loaded");
        }
    }

    /**
     * Tests toArray method
     *
     * @author Serghei Iakovlev <serghei@phalconphp.com>
     * @since  2016-03-04
     */
    public function testConfigToArray()
    {
        $this->specify(
            "Transform Config to the array does not returns the expected result",
            function () {
                $expected = [
                    'phalcon' => [
                        'baseuri' => '/phalcon/'
                    ],
                    'models' => [
                        'metadata' => 'memory',
                    ],
                    'nested' => [
                        'config' => [
                            'parameter' => 'here',
                        ],
                    ],
                ];

                $config = new Xml(
                    PATH_DATA . 'config/config.xml'
                );

                expect($config->toArray())->equals($expected);
            }
        );
    }
}
