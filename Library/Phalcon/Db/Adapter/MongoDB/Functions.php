<?php

namespace Phalcon\Db\Adapter\MongoDB;

use MongoDB\BSON\Serializable;
use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\Server;
use Phalcon\Db\Adapter\MongoDB\Exception\InvalidArgumentException;
use stdClass;

class Functions
{

    /**
     * Extracts an ID from an inserted document.
     *
     * This function is used when BulkWrite::insert() does not return a generated
     * ID, which means that the ID should be fetched from an array offset, public
     * property, or in the data returned by bsonSerialize().
     *
     * @internal
     * @see https://jira.mongodb.org/browse/PHPC-382
     *
     * @param array|object $document Inserted document
     *
     * @return mixed
     */
    public static function extractIdFromInsertedDocument($document)
    {
        if ($document instanceof Serializable) {
            return self::extractIdFromInsertedDocument($document->bsonSerialize());
        }

        return is_array($document)?$document['_id']:$document->_id;
    }

    /**
     * Generate an index name from a key specification.
     *
     * @internal
     *
     * @param array|object $document Document containing fields mapped to values,
     *                               which denote order or an index type
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public static function generateIndexName($document)
    {
        if (is_object($document)) {
            $document=get_object_vars($document);
        }

        if (!is_array($document)) {
            throw InvalidArgumentException::invalidType('$document', $document, 'array or object');
        }

        $name='';

        foreach ($document as $field => $type) {
            $name.=($name!=''?'_':'').$field.'_'.$type;
        }

        return $name;
    }

    /**
     * Return whether the first key in the document starts with a "$" character.
     *
     * This is used for differentiating update and replacement documents.
     *
     * @internal
     *
     * @param array|object $document Update or replacement document
     *
     * @return boolean
     * @throws InvalidArgumentException
     */
    public static function isFirstKeyOperator($document)
    {
        if (is_object($document)) {
            $document=get_object_vars($document);
        }

        if (!is_array($document)) {
            throw InvalidArgumentException::invalidType('$document', $document, 'array or object');
        }

        $firstKey=(string)key($document);

        return (isset($firstKey[0])&&$firstKey[0]=='$');
    }

    /**
     * Return whether the aggregation pipeline ends with an $out operator.
     *
     * This is used for determining whether the aggregation pipeline msut be
     * executed against a primary server.
     *
     * @internal
     *
     * @param array $pipeline List of pipeline operations
     *
     * @return boolean
     */
    public static function isLastPipelineOperatorOut(array $pipeline)
    {
        $lastOp=end($pipeline);

        if ($lastOp===false) {
            return false;
        }

        $lastOp=(array)$lastOp;

        return key($lastOp)==='$out';
    }

    /**
     * Converts a ReadConcern instance to a stdClass for use in a BSON document.
     *
     * @internal
     * @see https://jira.mongodb.org/browse/PHPC-498
     *
     * @param ReadConcern $readConcern Read concern
     *
     * @return stdClass
     */
    public static function readConcernAsDocument(ReadConcern $readConcern)
    {
        $document=[];

        if ($readConcern->getLevel()!==null) {
            $document['level']=$readConcern->getLevel();
        }

        return (object)$document;
    }

    /**
     * Return whether the server supports a particular feature.
     *
     * @param Server  $server Server to check
     * @param integer $feature Feature constant (i.e. wire protocol version)
     *
     * @return boolean
     */
    public static function serverSupportsFeature(Server $server, $feature)
    {
        $info          =$server->getInfo();
        $maxWireVersion=isset($info['maxWireVersion'])?(integer)$info['maxWireVersion']:0;
        $minWireVersion=isset($info['minWireVersion'])?(integer)$info['minWireVersion']:0;

        return ($minWireVersion<=$feature&&$maxWireVersion>=$feature);
    }

    /**
     * @param $input
     *
     * @return bool
     */
    public static function isStringArray($input)
    {
        if (!is_array($input)) {
            return false;
        }
        foreach ($input as $item) {
            if (!is_string($item)) {
                return false;
            }
        }

        return true;
    }
}
