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
     * @param array $options
     * @throws Exception
     */
    public function __construct($options = null)
    {
        if (!isset($options['collection'])) {
            throw new Exception("The parameter 'collection' is required");
        }

        @session_set_save_handler(
            [$this, 'open'],
            [$this, 'close'],
            [$this, 'read'],
            [$this, 'write'],
            [$this, 'destroy'],
            [$this, 'gc']
        );

        parent::__construct($options);
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
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $sessionId
     * @return string
     */
    public function read($sessionId)
    {
        $sessionData = $this->getCollection()->findOne(
            [
                '_id' => $sessionId,
            ]
        );

        if (!isset($sessionData['data'])) {
            return '';
        }

        $this->data = $sessionData['data'];

        return $sessionData['data'];
    }

    /**
     * {@inheritdoc}
     *
     * @param string $sessionId
     * @param string $sessionData
     * @return bool
     */
    public function write($sessionId, $sessionData)
    {
        if ($this->data === $sessionData) {
            return true;
        }

        $sessionData = [
            '_id' => $sessionId,
            'modified' => new \MongoDB\BSON\UTCDateTime(),
            'data' => $sessionData,
        ];


        $this->getCollection()->updateOne(
            ['_id' => $sessionId],
            ['$set' => $sessionData],
            ['upsert' => true]
        );


        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId = null)
    {
        if (is_null($sessionId)) {
            $sessionId = $this->getId();
        }

        $this->data = null;

        $remove = $this->getCollection()->remove(
            [
                '_id' => $sessionId,
            ]
        );

        return (bool)$remove['ok'];
    }

    /**
     * {@inheritdoc}
     * @param string $maxLifetime
     */
    public function gc($maxLifetime)
    {
        $minAge = new \DateTime();

        $minAge->sub(
            new \DateInterval(
                'PT' . $maxLifetime . 'S'
            )
        );

        $minAgeMongo = new \MongoDB\BSON\UTCDateTime(
            $minAge->getTimestamp()
        );

        $query = [
            'modified' => [
                '$lte' => $minAgeMongo,
            ],
        ];

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
