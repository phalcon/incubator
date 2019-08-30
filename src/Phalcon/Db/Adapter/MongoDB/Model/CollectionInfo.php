<?php

namespace Phalcon\Db\Adapter\MongoDB\Model;

/**
 * Collection information model class.
 *
 * This class models the collection information returned by the listCollections
 * command or, for legacy servers, queries on the "system.namespaces"
 * collection. It provides methods to access options for the collection.
 *
 * @api
 * @see MongoDB\Database::listCollections()
 * @see https://github.com/mongodb/specifications/blob/master/source/enumerate-collections.rst
 */
class CollectionInfo
{
    private $info;

    /**
     * Constructor.
     *
     * @param array $info Collection info
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
     * Return the maximum number of documents to keep in the capped collection.
     *
     * @return integer|null
     */
    public function getCappedMax()
    {
        return isset($this->info['options']['max'])?(integer)$this->info['options']['max']:null;
    }

    /**
     * Return the maximum size (in bytes) of the capped collection.
     *
     * @return integer|null
     */
    public function getCappedSize()
    {
        return isset($this->info['options']['size'])?(integer)$this->info['options']['size']:null;
    }

    /**
     * Return the collection name.
     *
     * @return string
     */
    public function getName()
    {
        return (string)$this->info['name'];
    }

    /**
     * Return the collection options.
     *
     * @return array
     */
    public function getOptions()
    {
        return isset($this->info['options'])?(array)$this->info['options']:[];
    }

    /**
     * Return whether the collection is a capped collection.
     *
     * @return boolean
     */
    public function isCapped()
    {
        return !empty($this->info['options']['capped']);
    }
}
