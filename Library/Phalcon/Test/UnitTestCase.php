<?php
/**
 * UnitTestCase.php
 * Phalcon_Test_UnitTestCase
 * Unit Test Helper
 * PhalconPHP Framework
 *
 * @copyright (c) 2011-2015 Phalcon Team
 * @link          http://www.phalconphp.com
 * @author        Andres Gutierrez <andres@phalconphp.com>
 * @author        Nikolaos Dimopoulos <nikos@phalconphp.com>
 * @author        Stephen Hoogendijk <hoogendijk09@gmail.com>
 *                The contents of this file are subject to the New BSD License that is
 *                bundled with this package in the file docs/LICENSE.txt
 *                If you did not receive a copy of the license and are unable to obtain it
 *                through the world-wide-web, please send an email to license@phalconphp.com
 *                so that we can send you a copy immediately.
 */
namespace Phalcon\Test;

use Phalcon\Config;
use Phalcon\DI\FactoryDefault;
use Phalcon\DI;
use Phalcon\DiInterface;
use Phalcon\Mvc\Url;

/**
 * Class UnitTestCase
 *
 * @package Phalcon\Test
 */
abstract class UnitTestCase extends \PHPUnit_Framework_TestCase
{

    /**
     * Holds the configuration variables and other stuff
     * I can use the DI container but for tests like the Translate
     * we do not need the overhead
     *
     * @var array
     */
    protected $config = array();

    /**
     * @var \Phalcon\DiInterface
     */
    protected $di;

    /**
     * Sets the test up by loading the DI container and other stuff
     *
     * @author Nikos Dimopoulos <nikos@phalconphp.com>
     * @since  2012-09-30
     * @param  \Phalcon\DiInterface $di
     * @param  \Phalcon\Config      $config
     * @return void
     */
    protected function setUp(DiInterface $di = null, Config $config = null)
    {
        $this->checkExtension('phalcon');

        if (!is_null($config)) {
            $this->config = $config;
        }

        if (is_null($di)) {
            // Reset the DI container
            DI::reset();

            // Instantiate a new DI container
            $di = new FactoryDefault();

            // Set the URL
            $di->set(
                'url',
                function () {
                    $url = new Url();
                    $url->setBaseUri('/');

                    return $url;
                }
            );

            $di->set(
                'escaper',
                function () {
                    return new \Phalcon\Escaper();
                }
            );
        }

        $this->di = $di;
    }

    /**
     * Checks if a particular extension is loaded and if not it marks
     * the tests skipped
     *
     * @param mixed $extension
     */
    public function checkExtension($extension)
    {
        $message = function ($ext) {
            sprintf('Warning: %s extension is not loaded', $ext);
        };

        if (is_array($extension)) {
            foreach ($extension as $ext) {
                if (!extension_loaded($ext)) {
                    $this->markTestSkipped($message($ext));
                    break;
                }
            }
        } elseif (!extension_loaded($extension)) {
            $this->markTestSkipped($message($extension));
        }
    }

    /**
     * Returns a unique file name
     *
     * @author Nikos Dimopoulos <nikos@phalconphp.com>
     * @since  2012-09-30
     * @param  string $prefix A prefix for the file
     * @param  string $suffix A suffix for the file
     * @return string
     */
    protected function getFileName($prefix = '', $suffix = 'log')
    {
        $prefix = ($prefix) ? $prefix . '_' : '';
        $suffix = ($suffix) ? $suffix : 'log';

        return uniqid($prefix, true) . '.' . $suffix;
    }

    /**
     * Removes a file from the system
     *
     * @author Nikos Dimopoulos <nikos@phalconphp.com>
     * @since  2012-09-30
     * @param $path
     * @param $fileName
     */
    protected function cleanFile($path, $fileName)
    {
        $file = (substr($path, -1, 1) != "/") ? ($path . '/') : $path;
        $file .= $fileName;

        $actual = file_exists($file);

        if ($actual) {
            unlink($file);
        }
    }

    /**
     * @return \Phalcon\DiInterface
     */
    protected function getDI()
    {
        return $this->di;
    }

    protected function tearDown()
    {
        $di = $this->getDI();
        $di::reset();
        parent::tearDown();
    }
}
