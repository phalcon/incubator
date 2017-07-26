<?php

namespace Phalcon\Db\Adapter\MongoDB\Model;

use Phalcon\Db\Adapter\MongoDB\Exception\BadMethodCallException;
use ArrayAccess;

/**
 * Index information model class.
 *
 * This class models the index information returned by the listIndexes command
 * or, for legacy servers, queries on the "system.indexes" collection. It
 * provides methods to access common index options, and allows access to other
 * options through the ArrayAccess interface (write methods are not supported).
 * For information on keys and index options, see the referenced
 * db.collection.createIndex() documentation.
 *
 * @api
 * @see MongoDB\Collection::listIndexes()
 * @see https://github.com/mongodb/specifications/blob/master/source/enumerate-indexes.rst
 * @see http://docs.mongodb.org/manual/reference/method/db.collection.createIndex/
 */
class IndexInfo implements ArrayAccess
{
    private $info;

    /**
     * Constructor.
     *
     * @param array $info Index info
     */
    public function __construct(array $info)
    {
        $this->info=$info;
    }

    /**
     * Return the collection info as an array.
     *
     * @see http://php.net/oop5.magic#language.oop5.magic.debuginfo
     * @return array
     */
    public function __debugInfo()
    {
        return $this->info;
    }

    /**
     * Return the index key.
     *
     * @return array
     */
    public function getKey()
    {
        return (array)$this->info['key'];
    }

    /**
     * Return the index name.
     *
     * @return string
     */
    public function getName()
    {
        return (string)$this->info['name'];
    }

    /**
     * Return the index namespace (e.g. "db.collection").
     *
     * @return string
     */
    public function getNamespace()
    {
        return (string)$this->info['ns'];
    }

    /**
     * Return the index version.
     *
     * @return integer
     */
    public function getVersion()
    {
        return (integer)$this->info['v'];
    }

    /**
     * Return whether this is a sparse index.
     *
     * @see http://docs.mongodb.org/manual/core/index-sparse/
     * @return boolean
     */
    public function isSparse()
    {
        return !empty($this->info['sparse']);
    }

    /**
     * Return whether this is a TTL index.
     *
     * @see http://docs.mongodb.org/manual/core/index-ttl/
     * @return boolean
     */
    public function isTtl()
    {
        return array_key_exists('expireAfterSeconds', $this->info);
    }

    /**
     * Return whether this is a unique index.
     *
     * @see http://docs.mongodb.org/manual/core/index-unique/
     * @return boolean
     */
    public function isUnique()
    {
        return !empty($this->info['unique']);
    }

    /**
     * Check whether a field exists in the index information.
     *
     * @see http://php.net/arrayaccess.offsetexists
     *
     * @param mixed $key
     *
     * @return boolean
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->info);
    }

    /**
     * Return the field's value from the index information.
     *
     * This method satisfies the Enumerating Indexes specification's requirement
     * that index fields be made accessible under their original names. It may
     * also be used to access fields that do not have a helper method.
     *
     * @see http://php.net/arrayaccess.offsetget
     * @see
     * https://github.com/mongodb/specifications/blob/master/source/enumerate-indexes.rst#getting-full-index-information
     *
     * @param mixed $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->info[ $key ];
    }

    /**
     * Not supported.
     *
     * @see http://php.net/arrayaccess.offsetset
     * @throws BadMethodCallException
     */
    public function offsetSet($key, $value)
    {
        throw BadMethodCallException::classIsImmutable(__CLASS__);
    }

    /**
     * Not supported.
     *
     * @see http://php.net/arrayaccess.offsetunset
     * @throws BadMethodCallException
     */
    public function offsetUnset($key)
    {
        throw BadMethodCallException::classIsImmutable(__CLASS__);
    }
}
