<?php
/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2012 Phalcon Team (http://www.phalconphp.com)       |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file docs/LICENSE.txt.                        |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: kenjikobe <kenji.minamoto@gmail.com>                          |
  +------------------------------------------------------------------------+
*/
namespace Phalcon\Session\Adapter;

use Phalcon\Session\Adapter;
use Phalcon\Session\AdapterInterface;
use Phalcon\Session\Exception;

/**
 * Phalcon\Session\Adapter\Redis
 * Database adapter for Phalcon\Session
 */
class Redis extends Adapter implements AdapterInterface
{

    /**
     * weight (integer): 
     * the weight of a host is used in comparison with the others 
     * in order to customize the session distribution on several 
     * hosts. If host A has twice the weight of host B, it will 
     * get twice the amount of sessions. In the example, host1 
     * stores 20% of all the sessions (1/(1+2+2)) while host2 and 
     * host3 each store 40% (2/1+2+2). The target host is determined 
     * once and for all at the start of the session, and doesn't 
     * change. The default weight is 1.
     *
     * @var string
     */
    protected $weight;

    /**
     * prefix (string, defaults to "PHPREDIS_SESSION:"):  
     * used as a prefix to the Redis key in which the session  
     * is stored. The key is composed of the prefix followed 
     * by the session ID.
     *
     * @var string
     */    
    protected $prefix;

    /**
     * timeout (float): the connection timeout to a redis host, expressed 
     * in seconds. If the host is unreachable in that amount of time, the 
     * session storage will be unavailable for the client. The default timeout 
     * is very high (86400 seconds).
     *
     * @var string
     */
    protected $timeout;

    /**
     * persistent (integer, should be 1 or 0): defines if a persistent 
     * connection should be used. (experimental setting)
     *
     * @var string
     */
    protected $persistent;

    /**
     * auth (string, empty by default): used to authenticate with the server 
     * prior to sending commands.
     *
     * @var string
     */
    protected $auth;

    /**
     * database (integer): selects a different database.
     *
     * @var string
     */
    protected $database;

    /**
     * path (string, where de redis-server is listening): this parameter is requiered 
     *
     * @var string
     */
    protected $path;

    /**
     * Class constructor.
     *
     * @param  array    $options
     * @throws \Phalcon\Session\Exception
     */
    public function __construct($options = null)
    {

        if (!isset($options['path'])) {
          throw new Exception("The parameter 'save_path' is required");
        }

        $this->path = $options['path'];

        /*
         * weight (integer)
         */
        if (isset($options['weight'])) {
          $this->weight = $options['weight'];              
        }else{
          $this->weight = '1';              
        }

        $this->path = $this->path . "?weight=$this->weight";

        /*
         * prefix (string, defaults to "PHPREDIS_SESSION:")
         */
        if (isset($options['prefix'])) {
          $this->prefix = $options['prefix'];
          $this->path = $this->path . "&prefix=$this->prefix";
        }

        /* 
         * timeout (float)
         */
        if (isset($options['timeout'])) {
          $this->timeout = $options['timeout'];
          $this->path = $this->path . "&timeout=$this->timeout";                
        }

        /* 
         * persistent (integer, should be 1 or 0)
         */
        if (isset($options['persistent'])) {
          $this->persistent = $options['persistent'];
          $this->path = $this->path . "&persistent=$this->persistent";                 
        }
    
        /* 
         * auth (string, empty by default)
         */
        if (isset($options['auth'])) {
          $this->auth = $options['auth'];
          $this->path = $this->path . "&auth=$this->auth";       
        }

        /* 
         * database (integer): selects a different database.
         */
        if (isset($options['database'])) {
          $this->database = $options['database'];
          $this->path = $this->path . "&database=$this->database";      
        } 

        /*
         * Set session variables
         */
        ini_set('session.save_handler', 'redis'); 
        
        ini_set('session.save_path', $this->path);  

        /* 
         * Sessions have a lifetime expressed in seconds and stored in 
         * the INI variable "session.gc_maxlifetime". You can change it 
         * with ini_set(). The session handler requires a version of Redis 
         * with the SETEX command (at least 2.0)
         */
        if (isset($options['lifetime'])) {
            ini_set('session.gc_maxlifetime', $options['lifetime']);
        }

        if (isset($options['cookie_lifetime'])) {
            ini_set('session.cookie_lifetime', $options['cookie_lifetime']);
        }      
    
        parent::__construct($options);
    }
}
