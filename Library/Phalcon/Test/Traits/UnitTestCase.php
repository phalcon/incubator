<?php
/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2016 Phalcon Team (https://www.phalconphp.com)      |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file LICENSE.txt.                             |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Stephen Hoogendijk <stephen@tca0.nl>                          |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Test\Traits;

use Phalcon\Di;
use Phalcon\Config;
use Phalcon\Di\FactoryDefault;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Di\DiInterface;
use Phalcon\Escaper;
use Phalcon\Mvc\Url;
use Phalcon\Test\Traits\ResultSet;
use PHPUnit\Framework\TestCase as TestCase;

trait UnitTestCase
{
    use ResultSet;

    /**
     * Holds the configuration variables and other stuff
     * I can use the DI container but for tests like the Translate
     * we do not need the overhead
     *
     * @var Config|null
     */
    protected $config;

    /**
     * @var DiInterface
     */
    protected $di;

    /**
     * This method is called before a test is executed.
     */
    protected function setUpPhalcon()
    {
        $this->checkExtension('phalcon');

        // Reset the DI container
        Di::reset();

        // Instantiate a new DI container
        $di = new Di();

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
                return new Escaper();
            }
        );

        $this->di = $di;
    }

    protected function tearDownPhalcon()
    {
        Di::reset();
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
     * @param string $path
     * @param string $fileName
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
     * Sets the Config object.
     *
     * @param Config $config
     * @return $this
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Returns the Config object if any.
     *
     * @return null|Config
     */
    public function getConfig()
    {
        if (!$this->config instanceof Config && $this->getDI()->has('config')) {
            return $this->getDI()->get('config');
        }

        return $this->config;
    }

    /**
     * Sets the Dependency Injector.
     *
     * @see    Injectable::setDI
     * @param  DiInterface $di
     * @return void
     */
    public function setDI(DiInterface $di) : void
    {
        $this->di = $di;
    }

    /**
     * Returns the internal Dependency Injector.
     *
     * @see    Injectable::getDI
     * @return DiInterface
     */
    public function getDI() : DiInterface
    {
        if (!$this->di instanceof DiInterface) {
            return Di::getDefault();
        }

        return $this->di;
    }
}
