<?php
/**
* ----------------------------------------------
*
*
* @author Steve Lo <info@sd.idv.tw>
* ----------------------------------------------
*/
namespace Phalcon\Session\Adapter;

use Phalcon;

/**
 * Libmemcache session adapter for Phalcon framework
 *
 * @category    Phalcon
 * @package     Phalcon_Session_Adapter_Libmemcached
 */
class Libmemcached extends Phalcon\Session\Adapter implements Phalcon\Session\AdapterInterface
{
    /**
     * Default option for session lifetime
     *
     * @var integer
     */
    const DEFAULT_OPTION_LIFETIME = 8600;

    /**
     * Default option for persistent session
     *
     * @var boolean
     */
    const DEFAULT_OPTION_HASH = \Memcached::HASH_MD5;

    /**
     * Default option for prefix of sessionId's
     *
     * @var string
     */
    const DEFAULT_OPTION_PREFIX = 'sess.';

    /**
     * Contains the memcache instance
     *
     * @var \Phalcon\Cache\Backend\Libmemcached
     */
    protected $memcacheInstance = null;

    protected $options = array();

    /**
     * Class constructor.
     *
     * @param  null|array                $options
     * @throws Phalcon\Session\Exception
     */
    public function __construct($options = null)
    {
        if (is_array($options)) {
            //lifetime settings
            if ($options['frontCache'] instanceof \Phalcon\Cache\FrontendInterface) {
                $this->options['frontCache'] = $options['frontCache'];
            } else {
                $this->options['frontCache'] = new Phalcon\Cache\Frontend\Data(
                    array("lifetime" => self::DEFAULT_OPTION_LIFETIME)
                );
            }

            //server settings
            if (!isset($options["servers"]) || !is_array($options["servers"])) {
                throw new Phalcon\Session\Exception("No configuration server wrong");
            } else {
                $this->options["servers"] = $options["servers"];
            }

            //client settings
            if (!isset($options["client"]) || !is_array($options["client"])) {
                $this->options["client"] = array(
                    \Memcached::OPT_HASH => self::DEFAULT_OPTION_HASH,
                    \Memcached::OPT_PREFIX_KEY => self::DEFAULT_OPTION_PREFIX,
                );
            }
            if (isset($options["useEncryption"]) &&
                    $options["useEncryption"] instanceof \Phalcon\CryptInterface ) {
                $this->options["useEncryption"] = $options["useEncryption"];

            }

        } else {
            throw new Phalcon\Session\Exception("No configuration given or wrong");
        }

        session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destroy'),
            array($this, 'gc')
        );

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean
     */
    public function open()
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
     * @param  string $sessionId
     * @return mixed
     */
    public function read($sessionId)
    {
        $ret = $this->getMemcacheInstance()->get($sessionId);
        $encryption = $this->getOption("useEncryption");
        if ($encryption == null) {
            return $ret;
        }

        return $encryption->decrypt($ret);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $sessionId
     * @param string $data
     */
    public function write($sessionId, $data)
    {

        $encryption = $this->getOption("useEncryption");
        if ($encryption !== null) {
            $data = $encryption->encrypt($data);
        }
        $this->getMemcacheInstance()->save(
            $sessionId,
            $data
        );
    }

    /**
     * Destroys session.
     *
     * @param string $session_id optional, session id
     *
     * @return boolean
     */
    public function destroy($session_id = null)
    {
        if (!$session_id) {
            $session_id = $this->getId();
        } else {
            $session_id = $session_id;
        }
        return $this->getMemcacheInstance()->delete($session_id);
    }

    /**
     * {@inheritdoc}
     */
    public function gc()
    {
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $key
     * @return mixed
     */
    public function getOption($key)
    {
        $options = $this->getOptions();
        if (isset($options[$key])) {
            return $options[$key];
        }

        return null;
    }

    /**
     * Returns the memcache instance.
     *
     * @return \Phalcon\Cache\Backend\Libmemcached
     */
    protected function getMemcacheInstance()
    {
        if ($this->memcacheInstance === null) {
            $this->memcacheInstance = new Phalcon\Cache\Backend\Libmemcached(
                $this->options['frontCache'],
                array(
                    'servers' => $this->options['servers'],
                    'client' => $this->options['client']
                )
            );
        }

        return $this->memcacheInstance;
    }

    /**
     * Sets memcache instance.
     *
     * @param Phalcon\Cache\Backend\Libmemcached $memcacheInstance memcache instance
     *
     * @return $this provides fluent interface
     */
    public function setMemcacheInstance(\Phalcon\Cache\Backend\Libmemcached $memcacheInstance)
    {
        $this->memcacheInstance = $memcacheInstance;
        return $this;
    }
}
