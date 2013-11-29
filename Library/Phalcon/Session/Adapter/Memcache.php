<?php
/**
 * Phalcon Framework
 *
 * This source file is subject to the New BSD License that is bundled
 * with this package in the file docs/LICENSE.txt.
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@phalconphp.com so we can send you a copy immediately.
 *
 * @category    Phalcon
 * @package     Phalcon_Session_Adapter_Memcache
 * @copyright   Copyright (c) 2013 meets-ecommerce GmbH (http://meets-ecommerce.de)
 * @author      Daniel Matuschewsky <dm@meets-ecommerce.de>
 */

namespace Phalcon\Session\Adapter;

use Phalcon;

/**
 * Memcache session adapter for Phalcon framework
 *
 * @category    Phalcon
 * @package     Phalcon_Session_Adapter_Memcache
 */
class Memcache extends Phalcon\Session\Adapter implements Phalcon\Session\AdapterInterface
{
    /**
     * Default option for memcache port
     *
     * @var int
     */
    const DEFAULT_OPTION_PORT       = 11211;

    /**
     * Default option for session lifetime
     *
     * @var int
     */
    const DEFAULT_OPTION_LIFETIME   = 8600;

    /**
     * Default option for persistent session
     *
     * @var bool
     */
    const DEFAULT_OPTION_PERSISTENT = false;

    /**
     * Default option for prefix of sessionId's
     *
     * @var string
     */
    const DEFAULT_OPTION_PREFIX     = '';

    /**
     * Contains the memcache instance
     *
     * @var Phalcon\Cache\Backend\Memcache
     */
    private $_memcacheInstance = null;

    /**
     * Constructor
     *
     * @param  null|array                $options
     * @throws Phalcon\Session\Exception
     */
    public function __construct($options = null)
    {
        if (is_array($options)) {
            if (!isset($options["host"])) {
                throw new Phalcon\Session\Exception("No session host given in options");
            }
            if (!isset($options["port"])) {
                $options["port"]  = self::DEFAULT_OPTION_PORT;
            }
            if (!isset($options["lifetime"])) {
                $options["lifetime"] = self::DEFAULT_OPTION_LIFETIME;
            }
            if (!isset($options["persistent"])) {
                $options["persistent"] = self::DEFAULT_OPTION_PERSISTENT;
            }
            if (!isset($options["prefix"])) {
                $options["prefix"] = self::DEFAULT_OPTION_PREFIX;
            }
        } else {
            throw new Phalcon\Session\Exception("No configuration given");
        }

        session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destroy'),
            array($this, 'gc')
        );

        parent::__construct($options);
    }

    /**
     * Opens the connection
     *
     * @return bool
     */
    public function open()
    {
        return true;
    }

    /**
     * Closes the connection
     *
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * Reads data from session object
     *
     * @param  string $sessionId
     * @return mixed
     */
    public function read($sessionId)
    {
        return $this->_getMemcacheInstance()->get(
            $this->_getSessionId($sessionId),
            $this->getOption('lifetime')
        );
    }

    /**
     * Writes data into session object
     *
     * @param string $sessionId
     * @param string $data
     */
    public function write($sessionId, $data)
    {
        $this->_getMemcacheInstance()->save(
            $this->_getSessionId($sessionId),
            $data,
            $this->getOption('lifetime')
        );
    }

    /**
     * Destroys the session
     *
     * @return bool
     */
    public function destroy()
    {
        return $this->_getMemcacheInstance()->delete($this->_getSessionId($this->getId()));
    }

    /**
     * Garbage collector
     */
    public function gc()
    {
    }

    /**
     * Returns option value by key
     *
     * @param  string $key
     * @return null
     */
    public function getOption($key)
    {
       if (isset($this->_options[$key])) {
           return $this->_options[$key];
       }

       return null;
    }

    /**
     * Returns the memcache instance
     *
     * @return Phalcon\Cache\Backend\Memcache
     */
    private function _getMemcacheInstance()
    {
        if ($this->_memcacheInstance === null) {
            $this->_memcacheInstance = new Phalcon\Cache\Backend\Memcache(
                new Phalcon\Cache\Frontend\Data(array("lifetime" => $this->getOption("lifetime"))),
                array(
                    'host'       => $this->getOption('host'),
                    'port'       => $this->getOption('port'),
                    'persistent' => $this->getOption('persistent')
                )
            );
        }

        return $this->_memcacheInstance;
    }

    /**
     * Returns the sessionId with prefix
     *
     * @param $sessionId
     * @return string
     */
    private function _getSessionId($sessionId)
    {
        return (strlen($this->getOption('prefix')) > 0)
            ? $this->getOption('prefix') . '_' . $sessionId
            : $sessionId;
    }

}
