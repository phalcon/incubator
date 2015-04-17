<?php
/**
 * HandlerSocket session handler
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

use Phalcon\Session\Adapter;
use Phalcon\Session\AdapterInterface;
use Phalcon\Session\Exception;

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
     * =====> (string) cookie_path :
     * the path for which the cookie is valid.
     * =====> (string) cookie_domain :
     * the domain for which the cookie is valid.
     * =====> (int) lifetime :
     * session lifetime in seconds
     * =====> (array) server :
     * an array of mysql handlersocket server :
     * 'host' => (string) : the name of the mysql handlersocket server
     * 'port' => (int) : the port of the mysql handlersocket server
     * 'dbname' => (string) : the database name of the mysql handlersocket server
     * 'dbtable' => (string) : the table name of the mysql handlersocket server
     */
    protected $options = array(
        'cookie_path'   => '/',
        'cookie_domain' => '',
        'lifetime'      => 3600,
        'server'        => array(
            'host'    => self::DEFAULT_HOST,
            'port'    => self::DEFAULT_PORT,
            'dbname'  => self::DEFAULT_DBNAME,
            'dbtable' => self::DEFAULT_DBTABLE
        )
    );

    /**
     * HandlerSocket object
     *
     * @var \HandlerSocket
     */
    protected $hs;

    /**
     * HandlerSocket index number
     *
     * @var integer
     */
    protected $hsIndex = 1;

    /**
     * Stores session data results
     *
     * @var array
     */
    protected $fields = array();

    /**
     * Class constructor.
     *
     * @param  array                      $options associative array of options
     * @throws \Phalcon\Session\Exception
     */
    public function __construct($options = array())
    {
        // initialize the handlersocket database
        if (empty($options)) {
            $this->init($this->options);
        } else {
            $this->init($options);
        }

        //set object as the save handler
        session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destroy'),
            array($this, 'gc')
        );
    }

    /**
     * Destructor
     *
     * @return void
     */
    public function __destruct()
    {
        session_write_close();
    }

    /**
     * {@inheritdoc}
     *
     * @param  array $options associative array of options
     * @return void
     */
    public function start($options = array())
    {
        $object = new self($options);

        //start it up
        session_start();
    }

    /**
     *{@inheritdoc}
     *
     * @param  string  $save_path
     * @param  string  $name
     * @return boolean
     */
    public function open($save_path, $name)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $id
     * @return string
     */
    public function read($id)
    {
        $retval = $this->hs->executeSingle($this->hsIndex, '=', array($id), 1, 0);

        if (!isset($retval[0], $retval[0][2])) {
           return '';
        }

        $this->fields['id']       = $retval[0][0];
        $this->fields['modified'] = $retval[0][1];
        $this->fields['data']     = '';

        return $retval[0][2];
    }

    /**
     * {@inheritdoc}
     *
     * @param  string  $id
     * @param  string  $data
     * @return boolean
     */
    public function write($id, $data)
    {
        if (isset($this->fields['id']) && $this->fields['id'] != $id) {
            $this->fields = array();
        }

        if (empty($this->fields)) {
            $this->hs->executeInsert($this->hsIndex, array($id, date('Y-m-d H:i:s'), $data));
        } else {
            $this->hs->executeUpdate($this->hsIndex, '=', array($id), array($id, date('Y-m-d H:i:s'), $data), 1, 0);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param  string  $id
     * @return boolean
     */
    public function destroy($id)
    {
        $this->hs->executeDelete($this->hsIndex, '=', array($id), 1, 0);

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param  integer $maxlifetime
     * @return boolean
     */
    public function gc($maxlifetime)
    {
        $time  = date('Y-m-d H:i:s', strtotime("- $maxlifetime seconds"));
        $index = $this->hsIndex + 1;

        $this->hs->openIndex(
            $index,
            $this->options['server']['dbname'],
            $this->options['server']['dbtable'],
            self::DB_GC_INDEX,
            ''
        );

        $this->hs->executeDelete($index, '<', array($time), 1000, 0);

        return true;
    }

    /**
     * Initialize HandlerSocket.
     *
     * @param  array                      $options associative array of options
     * @throws \Phalcon\Session\Exception
     */
    protected function init($options)
    {
        if (empty($options['server'])) {
            $options['server'] = array();
        }

        if (empty($options['server']['host'])) {
            $options['server']['host'] = self::DEFAULT_HOST;
        }

        if (empty($options['server']['port'])) {
            $options['server']['port'] = self::DEFAULT_PORT;
        }

        if (empty($options['server']['dbname'])) {
            $options['server']['dbname'] = self::DEFAULT_DBNAME;
        }

        if (empty($options['server']['dbtable'])) {
            $options['server']['dbtable'] = self::DEFAULT_DBTABLE;
        }

        //update options
        $this->options = $options;

        if (!extension_loaded('handlersocket')) {
            throw new Exception('The handlersocket extension must be loaded for using session!');
        }

        // load handlersocket server
        $this->hs = new \HandlerSocket($options['server']['host'], $options['server']['port']);

        // open handlersocket index
        $result = $this->hs->openIndex(
            $this->hsIndex,
            $options['server']['dbname'],
            $options['server']['dbtable'],
            \HandlerSocket::PRIMARY,
            self::DB_FIELDS
        );

        if (!$result) {
            throw new Exception('The HandlerSocket database specified in the options does not exist.');
        }
    }
}
