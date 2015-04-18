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
  | Authors: Andres Gutierrez <andres@phalconphp.com>                      |
  |          Eduar Carvajal <eduar@phalconphp.com>                         |
  +------------------------------------------------------------------------+
*/
namespace Phalcon\Session\Adapter;

use Phalcon\Session\Adapter;
use Phalcon\Session\AdapterInterface;
use Phalcon\Session\Exception;

/**
 * Phalcon\Session\Adapter\Mongo
 * Mongo adapter for Phalcon\Session
 */
class Mongo extends Adapter implements AdapterInterface
{
    /**
     * Current session data
     *
     * @var string
     */
    protected $data;

    /**
     * Class constructor.
     *
     * @param  array     $options
     * @throws Exception
     */
    public function __construct($options = null)
    {
        if (!isset($options['collection'])) {
            throw new Exception("The parameter 'collection' is required");
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
     * {@inheritdoc}
     *
     * @param  string  $savePath
     * @param  string  $name
     * @return boolean
     */
    public function open($savePath, $name)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $sessionId
     * @return string
     */
    public function read($sessionId)
    {
        $sessionData = $this->getCollection()->findOne(array('_id' => $sessionId));
        if (!isset($sessionData['data'])) {
            return null;
        }

        $this->data = $sessionData['data'];
        return $sessionData['data'];
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $sessionId
     * @param  string $sessionData
     * @return bool
     */
    public function write($sessionId, $sessionData)
    {
        if ($this->data === $sessionData) {
            return true;
        }

        $sessionData = array(
            '_id' => $sessionId,
            'modified' => new \MongoDate(),
            'data' => $sessionData
        );

        $this->getCollection()->save($sessionData);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId = null)
    {
        if (is_null($sessionId)) {
            $sessionId = session_id();
        }

        $this->data = null;

        $remove = $this->getCollection()->remove(array('_id' => $sessionId));

        return (bool)$remove['ok'];
    }

    /**
     * {@inheritdoc}
     * @param string $maxlifetime
     */
    public function gc($maxlifetime)
    {
        $minAge = new \DateTime();
        $minAge->sub(new \DateInterval('PT' . $maxlifetime . 'S'));
        $minAgeMongo = new \MongoDate($minAge->getTimestamp());

        $query = array('modified' => array('$lte' => $minAgeMongo));
        $remove = $this->getCollection()->remove($query);

        return (bool)$remove['ok'];
    }

    /**
     * @return \MongoCollection
     */
    protected function getCollection()
    {
        $options = $this->getOptions();
        return $options['collection'];
    }
}
