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
  | Authors: Serghei Iakovlev <serghei@phalconphp.com>                     |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Loader;

use Phalcon\Loader;

/**
 * Phalcon\Loader\Extended
 *
 * This component extends Phalcon\Loader and adds ability to
 * set multiple directories a namespace.
 *
 * <code>
 * use Phalcon\Loader\Extended as Loader;
 *
 * // Creates the autoloader
 * $loader = new Loader();
 *
 * // Register some namespaces
 * $loader->registerNamespaces(
 *     [
 *         'Example\Base' => 'vendor/example/base/',
 *         'Some\Adapters' => [
 *             'vendor/example/adapters/src/',
 *             'vendor/example/adapters/test/',
 *         ]
 *     ]
 * );
 *
 * // Register autoloader
 * $loader->register();
 *
 * // Requiring this class will automatically include file vendor/example/adapters/src/Some.php
 * $adapter = Example\Adapters\Some();
 *
 * // Requiring this class will automatically include file vendor/example/adapters/test/Another.php
 * $adapter = Example\Adapters\Another();
 * </code>
 *
 * @package Phalcon\Loader
 */
class Extended extends Loader
{
    /**
     * Register namespaces and their related directories
     *
     * @param array $namespaces
     * @param bool $merge
     * @return $this
     */
    public function registerNamespaces(array $namespaces, $merge = false)
    {
        $preparedNamespaces = $this->prepareNamespace($namespaces);

        if ($merge) {
            $currentNamespaces = $this->_namespaces;
            if (is_array($currentNamespaces)) {
                foreach ($preparedNamespaces as $name => $paths) {
                    if (!isset($currentNamespaces[$name])) {
                        $currentNamespaces[$name] = [];
                    }

                    $currentNamespaces[$name] = array_merge($currentNamespaces[$name], $paths);
                }

                $this->_namespaces = $currentNamespaces;
            } else {
                $this->_namespaces = $preparedNamespaces;
            }
        } else {
            $this->_namespaces = $preparedNamespaces;
        }

        return $this;
    }

    protected function prepareNamespace(array $namespace)
    {
        $prepared = [];
        foreach ($namespace as $name => $path) {
            if (!is_array($path)) {
                $path = [$path];
            }

            $prepared[$name] = $path;
        }

        return $prepared;
    }

    /**
     * Autoloads the registered classes
     *
     * @param string $className
     * @return bool
     */
    public function autoLoad($className)
    {
        if (!is_string($className) || empty($className)) {
            return false;
        }

        if (is_object($this->_eventsManager)) {
            $this->_eventsManager->fire('loader:beforeCheckClass', $this, $className);
        }

        /**
         * First we check for static paths for classes
         */
        if (is_array($this->_classes) && isset($this->_classes[$className])) {
            $filePath = $this->_classes[$className];

            if (is_file($filePath)) {
                if (is_object($this->_eventsManager)) {
                    $this->_foundPath = $filePath;
                    $this->_eventsManager->fire('loader:pathFound', $this, $filePath);
                }

                require $filePath;
                return true;
            }
        }

        $ds = DIRECTORY_SEPARATOR;
        $ns = "\\";

        /**
         * Checking in namespaces
         */
        if (is_array($this->_namespaces)) {
            foreach ($this->_namespaces as $nsPrefix => $directories) {
                /**
                 * The class name must start with the current namespace
                 */
                if (0 === strpos($className, $nsPrefix)) {
                    $fileName = substr($className, strlen($nsPrefix . $ns));
                    $fileName = str_replace($ns, $ds, $fileName);

                    if ($fileName) {
                        foreach ($directories as $directory) {
                            /**
                             * Add a trailing directory separator if the user forgot to do that
                             */
                            $fixedDirectory = rtrim($directory, $ds) . $ds;

                            foreach ($this->_extensions as $extension) {
                                $filePath = $fixedDirectory . $fileName . "." . $extension;

                                /**
                                 * Check if a events manager is available
                                 */
                                if (is_object($this->_eventsManager)) {
                                    $this->_checkedPath = $filePath;
                                    $this->_eventsManager->fire('loader:beforeCheckPath', $this);
                                }

                                /**
                                 * This is probably a good path, let's check if the file exists
                                 */
                                if (is_file($filePath)) {
                                    if (is_object($this->_eventsManager)) {
                                        $this->_foundPath = $filePath;
                                        $this->_eventsManager->fire('loader:pathFound', $this, $filePath);
                                    }

                                    /**
                                     * Simulate a require
                                     */
                                    require $filePath;

                                    /**
                                     * Return true mean success
                                     */
                                    return true;
                                }
                            }
                        }
                    }
                }
            }
        }

        /**
         * Checking in prefixes
         */
        if (is_array($this->_prefixes)) {
            foreach ($this->_prefixes as $prefix => $directory) {
                /**
                 * The class name starts with the prefix?
                 */
                if (0 === strpos($className, $prefix)) {
                        /**
                     * Get the possible file path
                     */
                    $fileName = str_replace($prefix . $ns, "", $className);
                    $fileName = str_replace($prefix . "_", "", $fileName);
                    $fileName = str_replace("_", $ds, $fileName);

                    if ($fileName) {
                        /**
                         * Add a trailing directory separator if the user forgot to do that
                         */
                        $fixedDirectory = rtrim($directory, $ds) . $ds;

                        foreach ($this->_extensions as $extension) {
                            $filePath = $fixedDirectory . $fileName . "." . $extension;

                            if (is_object($this->_eventsManager)) {
                                $this->_checkedPath = $filePath;
                                $this->_eventsManager->fire('loader:beforeCheckPath', $this, $filePath);
                            }

                            if (is_file($filePath)) {
                                /**
                                 * Call 'pathFound' event
                                 */
                                if (is_object($this->_eventsManager)) {
                                    $this->_foundPath = $filePath;
                                    $this->_eventsManager->fire('loader:pathFound', $this, $filePath);
                                }

                                require $filePath;
                                return true;
                            }
                        }
                    }
                }
            }
        }

        /**
         * Change the pseudo-separator by the directory separator in the class name
         */
        $dsClassName = str_replace("_", $ds, $className);

        /**
         * And change the namespace separator by directory separator too
         */
        $nsClassName = str_replace("\\", $ds, $dsClassName);

        /**
         * Checking in directories
         */
        if (is_array($this->_directories)) {
            foreach ($this->_directories as $directory) {
                /**
                 * Add a trailing directory separator if the user forgot to do that
                 */
                $fixedDirectory = rtrim($directory, $ds) . $ds;

                foreach ($this->_extensions as $extension) {
                    /**
                     * Create a possible path for the file
                     */
                    $filePath = $fixedDirectory . $nsClassName . "." . $extension;

                    if (is_object($this->_eventsManager)) {
                        $this->_checkedPath = $filePath;
                        $this->_eventsManager->fire('loader:beforeCheckPath', $this, $filePath);
                    }

                    /**
                     * Check in every directory if the class exists here
                     */
                    if (is_file($filePath)) {
                        /**
                         * Call 'pathFound' event
                         */
                        if (is_object($this->_eventsManager)) {
                            $this->_foundPath = $filePath;
                            $this->_eventsManager->fire('loader:pathFound', $this, $filePath);
                        }

                        /**
                         * Simulate a require
                         */
                        require $filePath;

                        /**
                         * Return true meaning success
                         */
                        return true;
                    }
                }
            }
        }

        /**
         * Call 'afterCheckClass' event
         */
        if (is_object($this->_eventsManager)) {
            $this->_eventsManager->fire('loader:afterCheckClass', $this, $className);
        }

        /**
         * Cannot find the class, return false
         */
        return false;
    }
}
