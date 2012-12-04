<?php

/**
 * HandlerSocket session handler
 *
 * Table schema :
 * CREATE TABLE `php_session` (
 *   `id`       varchar(32) NOT NULL DEFAULT '',
 *   `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 *   `data`     text,
 *   PRIMARY KEY (`id`),
 *   KEY `modified` (`modified`)
 * ) ENGINE=InnoDB DEFAULT CHARACTER SET utf8;
 */

namespace Phalcon\Session\Adapter;

use \Phalcon\Session\Adapter,
    \Phalcon\Session\AdapterInterface,
    \Phalcon\Session\Exception;

class HandlerSocket extends Adapter implements AdapterInterface
{
    /**
     * Default Values
     */
    const DEFAULT_HOST = 'localhost';
    const DEFAULT_PORT = 9999;
    const DEFAULT_DBNAME = 'session';
    const DEFAULT_DBTABLE = 'php_session';

    /**
     * Database fileds and index
     */
    const DB_FIELDS = 'id,modified,data';
    const DB_GC_INDEX = 'modified';

    /**
     * Available options
     *
     * =====> (string) cookie_path :
     * the path for which the cookie is valid.
     *
     * =====> (string) cookie_domain :
     * the domain for which the cookie is valid.
     *
     * =====> (int) lifetime :
     * session lifetime in seconds
     *
     * =====> (array) server :
     * an array of mysql handlersocket server :
     * 'host' => (string) : the name of the mysql handlersocket server
     * 'port' => (int) : the port of the mysql handlersocket server
     * 'dbname' => (string) : the database name of the mysql handlersocket server
     * 'dbtable' => (string) : the table name of the mysql handlersocket server
     */
    protected $_options = array(
        'cookie_path' => '/',
        'cookie_domain' => '',
        'lifetime' => 3600,
        'server' => array(
            'host' => self::DEFAULT_HOST,
            'port' => self::DEFAULT_PORT,
            'dbname' => self::DEFAULT_DBNAME,
            'dbtable' => self::DEFAULT_DBTABLE
        )
    );

    /**
     * HandlerSocket object
     */
    protected $_hs;

    /**
     * HandlerSocket index number
     */
    private $_hsIndex = 1;

    /**
     * Stores session data results
     */
    private $_fields = array();


    /**
     * Constructor
     *
     * @param array $options associative array of options
     * @return void
     */
    public function __construct($options = array())
    {
        //initialize the handlersocket database
        if (empty($options))
        {
            $this->_init($this->_options);
        }
        else
        {
            $this->_init($options);
        }

        //set object as the save handler
        session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destroy'),
            array($this, 'gc'));

        /*
        //set some important session vars
        ini_set('session.auto_start', 0);
        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 100);
        ini_set('session.gc_maxlifetime', $this->_options['lifetime']);
        ini_set('session.referer_check', '');
        ini_set('session.entropy_file', '/dev/urandom');
        ini_set('session.entropy_length', 16);
        ini_set('session.use_cookies', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.use_trans_sid', 0);
        ini_set('session.hash_function', 1);
        ini_set('session.hash_bits_per_character', 5);

        //disable client/proxy caching
        session_cache_limiter('nocache');

        //set the cookie parameters
        session_set_cookie_params($this->_options['lifetime'],
            $this->_options['cookie_path'],
            $this->_options['cookie_domain']);
        //name the session
        session_name('hs_sess');

        //start it up
        //session_start();
        */
    }

    /**
     * Desstructor
     *
     * @return void
     */
    public function __destruct ()
    {
        session_write_close();
    }

    /**
     * Initialize HandlerSocket.
     *
     * @param array $options associative array of options
     * @return void
     */
    private function _init($options)
    {
        //update options
        $this->_options = $options;

        //generate server connection strings
        if (!isset($this->_options['server'],
        $this->_options['server']['host'],
        $this->_options['server']['port'],
        $this->_options['server']['dbname'],
        $this->_options['server']['dbtable']))
        {
            $this->_options['server'] =
                array('host' => self::DEFAULT_HOST,
                    'port' => self::DEFAULT_PORT,
                    'dbname' => self::DEFAULT_DBNAME,
                    'dbtable' => self::DEFAULT_DBTABLE);
        }

        if (!extension_loaded('handlersocket')) {
            throw new Exception(
                'The handlersocket extension must be loaded for using session !');
        }

        //load handlersocket server
        $this->_hs = new \HandlerSocket(
            $this->_options['server']['host'],
            $this->_options['server']['port']);

        //open handlersocket index
        if (!($this->_hs->openIndex(
            $this->_hsIndex,
            $this->_options['server']['dbname'],
            $this->_options['server']['dbtable'],
            \HandlerSocket::PRIMARY, self::DB_FIELDS)))
        {
            throw new Exception(
                'The HandlerSocket database specified ' .
                    'in the options does not exist.');
        }
    }

    /**
     * Start the session.
     *
     * @param array $options associative array of options
     * @return void
     */
    public function start($options = array())
    {
        $object = new self($options);

        //start it up
        session_start();
    }

    /**
     * Open Session
     *
     * @param string $save_path
     * @param string $name
     * @return true
     */
    public function open($save_path, $name)
    {
        return true;
    }

    /**
     * Close Session
     *
     * @return true
     */
    public function close()
    {
        return true;
    }

    /**
     * Read session data
     *
     * @param string $id
     * @return string
     */
    public function read($id)
    {
        $retval = $this->_hs->executeSingle(
            $this->_hsIndex, '=', array($id), 1, 0);

        if (isset($retval[0], $retval[0][2]))
        {
            $this->_fields['id'] = $retval[0][0];
            $this->_fields['modified'] = $retval[0][1];
            $this->_fields['data'] = '';

            return $retval[0][2];
        }
        else
        {
            return '';
        }
    }

    /**
     * Write session data
     *
     * @param string $id
     * @param string $data
     * @return true
     */
    public function write($id, $data)
    {
        if (isset($this->_fields['id']) && $this->_fields['id'] != $id)
        {
            $this->_fields = array();
        }

        if (empty($this->_fields))
        {
            $this->_hs->executeInsert(
                $this->_hsIndex, array($id, date('Y-m-d H:i:s'), $data));
        }
        else
        {
            $this->_hs->executeUpdate(
                $this->_hsIndex, '=', array($id),
                array($id, date('Y-m-d H:i:s'), $data), 1, 0);
        }

        return true;
    }

    /**
     * Destroy session
     *
     * @param string $id
     * @return true
     */
    public function destroy($id)
    {
        $this->_hs->executeDelete($this->_hsIndex, '=', array($id), 1, 0);

        return true;
    }

    /**
     * Garbage Collection
     *
     * @param int $maxlifetime
     * @return true
     */
    public function gc($maxlifetime)
    {
        $time = date('Y-m-d H:i:s', strtotime("- $maxlifetime seconds"));

        $index = $this->_hsIndex + 1;

        $this->_hs->openIndex(
            $index,
            $this->_options['server']['dbname'],
            $this->_options['server']['dbtable'],
            self::DB_GC_INDEX, '');
        $this->_hs->executeDelete($index, '<', array($time), 1000, 0);

        return true;
    }
}

