<?php

namespace Phalcon\Db\Adapter\MongoDB\GridFS;

use MongoDB\BSON\ObjectId;
use MongoDB\Driver\Cursor;
use MongoDB\Driver\Manager;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use Phalcon\Db\Adapter\MongoDB\Operation\Find;
use Phalcon\Db\Adapter\MongoDB\Exception\InvalidArgumentException;
use Phalcon\Db\Adapter\MongoDB\GridFS\Exception\FileNotFoundException;
use stdClass;

/**
 * Bucket provides a public API for interacting with the GridFS files and chunks
 * collections.
 *
 * @api
 */
class Bucket
{
    private static $defaultChunkSizeBytes=261120;
    private static $streamWrapperProtocol='gridfs';

    private $collectionWrapper;
    private $databaseName;
    private $options;

    /**
     * Constructs a GridFS bucket.
     *
     * Supported options:
     *
     *  * bucketName (string): The bucket name, which will be used as a prefix
     *    for the files and chunks collections. Defaults to "fs".
     *
     *  * chunkSizeBytes (integer): The chunk size in bytes. Defaults to
     *    261120 (i.e. 255 KiB).
     *
     *  * readPreference (MongoDB\Driver\ReadPreference): Read preference.
     *
     *  * writeConcern (MongoDB\Driver\WriteConcern): Write concern.
     *
     * @param Manager $manager Manager instance from the driver
     * @param string  $databaseName Database name
     * @param array   $options Bucket options
     *
     * @throws InvalidArgumentException
     */
    public function __construct(Manager $manager, $databaseName, array $options = [])
    {
        $options+=[
            'bucketName'    =>'fs',
            'chunkSizeBytes'=>self::$defaultChunkSizeBytes,
        ];

        if (isset($options['bucketName'])&&!is_string($options['bucketName'])) {
            throw InvalidArgumentException::invalidType('"bucketName" option', $options['bucketName'], 'string');
        }

        if (isset($options['chunkSizeBytes'])&&!is_integer($options['chunkSizeBytes'])) {
            throw InvalidArgumentException::invalidType(
                '"chunkSizeBytes" option',
                $options['chunkSizeBytes'],
                'integer'
            );
        }

        if (isset($options['readPreference'])&&!$options['readPreference'] instanceof ReadPreference) {
            throw InvalidArgumentException::invalidType(
                '"readPreference" option',
                $options['readPreference'],
                'MongoDB\Driver\ReadPreference'
            );
        }

        if (isset($options['writeConcern'])&&!$options['writeConcern'] instanceof WriteConcern) {
            throw InvalidArgumentException::invalidType(
                '"writeConcern" option',
                $options['writeConcern'],
                'MongoDB\Driver\WriteConcern'
            );
        }

        $this->databaseName=(string)$databaseName;
        $this->options     =$options;

        $collectionOptions=array_intersect_key($options, ['readPreference'=>1,'writeConcern'=>1]);

        $this->collectionWrapper=new CollectionWrapper(
            $manager,
            $databaseName,
            $options['bucketName'],
            $collectionOptions
        );
        $this->registerStreamWrapper();
    }

    /**
     * Delete a file from the GridFS bucket.
     *
     * If the files collection document is not found, this method will still
     * attempt to delete orphaned chunks.
     *
     * @param mixed $id File ID
     *
     * @throws FileNotFoundException
     */
    public function delete($id)
    {
        $file=$this->collectionWrapper->findFileById($id);
        $this->collectionWrapper->deleteFileAndChunksById($id);

        if ($file===null) {
            throw FileNotFoundException::byId($id, $this->getFilesNamespace());
        }
    }

    /**
     * Writes the contents of a GridFS file to a writable stream.
     *
     * @param mixed    $id File ID
     * @param resource $destination Writable Stream
     *
     * @throws FileNotFoundException
     */
    public function downloadToStream($id, $destination)
    {
        $file=$this->collectionWrapper->findFileById($id);

        if ($file===null) {
            throw FileNotFoundException::byId($id, $this->getFilesNamespace());
        }

        $stream=new ReadableStream($this->collectionWrapper, $file);
        $stream->downloadToStream($destination);
    }

    /**
     * Writes the contents of a GridFS file, which is selected by name and
     * revision, to a writable stream.
     *
     * Supported options:
     *
     *  * revision (integer): Which revision (i.e. documents with the same
     *    filename and different uploadDate) of the file to retrieve. Defaults
     *    to -1 (i.e. the most recent revision).
     *
     * Revision numbers are defined as follows:
     *
     *  * 0 = the original stored file
     *  * 1 = the first revision
     *  * 2 = the second revision
     *  * etc…
     *  * -2 = the second most recent revision
     *  * -1 = the most recent revision
     *
     * @param string   $filename Filename
     * @param resource $destination Writable Stream
     * @param array    $options Download options
     *
     * @throws FileNotFoundException
     */
    public function downloadToStreamByName($filename, $destination, array $options = [])
    {
        $options+=['revision'=>-1];

        $file=$this->collectionWrapper->findFileByFilenameAndRevision($filename, $options['revision']);

        if ($file===null) {
            throw FileNotFoundException::byFilenameAndRevision(
                $filename,
                $options['revision'],
                $this->getFilesNamespace()
            );
        }

        $stream=new ReadableStream($this->collectionWrapper, $file);
        $stream->downloadToStream($destination);
    }

    /**
     * Drops the files and chunks collections associated with this GridFS
     * bucket.
     */
    public function drop()
    {
        $this->collectionWrapper->dropCollections();
    }

    /**
     * Finds documents from the GridFS bucket's files collection matching the
     * query.
     *
     * @see Find::__construct() for supported options
     *
     * @param array|object $filter Query by which to filter documents
     * @param array        $options Additional options
     *
     * @return Cursor
     */
    public function find($filter, array $options = [])
    {
        return $this->collectionWrapper->findFiles($filter, $options);
    }

    public function getCollectionWrapper()
    {
        return $this->collectionWrapper;
    }

    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    /**
     * Gets the ID of the GridFS file associated with a stream.
     *
     * @param resource $stream GridFS stream
     *
     * @return mixed
     */
    public function getIdFromStream($stream)
    {
        $metadata=stream_get_meta_data($stream);

        if ($metadata['wrapper_data'] instanceof StreamWrapper) {
            return $metadata['wrapper_data']->getId();
        }

        // TODO: Throw if we cannot access the ID
    }

    /**
     * Opens a readable stream for reading a GridFS file.
     *
     * @param mixed $id File ID
     *
     * @return resource
     * @throws FileNotFoundException
     */
    public function openDownloadStream($id)
    {
        $file=$this->collectionWrapper->findFileById($id);

        if ($file===null) {
            throw FileNotFoundException::byId($id, $this->getFilesNamespace());
        }

        return $this->openDownloadStreamByFile($file);
    }

    /**
     * Opens a readable stream stream to read a GridFS file, which is selected
     * by name and revision.
     *
     * Supported options:
     *
     *  * revision (integer): Which revision (i.e. documents with the same
     *    filename and different uploadDate) of the file to retrieve. Defaults
     *    to -1 (i.e. the most recent revision).
     *
     * Revision numbers are defined as follows:
     *
     *  * 0 = the original stored file
     *  * 1 = the first revision
     *  * 2 = the second revision
     *  * etc…
     *  * -2 = the second most recent revision
     *  * -1 = the most recent revision
     *
     * @param string $filename Filename
     * @param array  $options Download options
     *
     * @return resource
     * @throws FileNotFoundException
     */
    public function openDownloadStreamByName($filename, array $options = [])
    {
        $options+=['revision'=>-1];

        $file=$this->collectionWrapper->findFileByFilenameAndRevision($filename, $options['revision']);

        if ($file===null) {
            throw FileNotFoundException::byFilenameAndRevision(
                $filename,
                $options['revision'],
                $this->getFilesNamespace()
            );
        }

        return $this->openDownloadStreamByFile($file);
    }

    /**
     * Opens a writable stream for writing a GridFS file.
     *
     * Supported options:
     *
     *  * chunkSizeBytes (integer): The chunk size in bytes. Defaults to the
     *    bucket's chunk size.
     *
     * @param string $filename Filename
     * @param array  $options Upload options
     *
     * @return resource
     */
    public function openUploadStream($filename, array $options = [])
    {
        $options+=['chunkSizeBytes'=>$this->options['chunkSizeBytes']];

        $path   =$this->createPathForUpload();
        $context=stream_context_create([
            self::$streamWrapperProtocol=>[
                'collectionWrapper'=>$this->collectionWrapper,
                'filename'         =>$filename,
                'options'          =>$options,
            ],
        ]);

        return fopen($path, 'w', false, $context);
    }

    /**
     * Renames the GridFS file with the specified ID.
     *
     * @param mixed  $id File ID
     * @param string $newFilename New filename
     *
     * @throws FileNotFoundException
     */
    public function rename($id, $newFilename)
    {
        $updateResult=$this->collectionWrapper->updateFilenameForId($id, $newFilename);

        if ($updateResult->getModifiedCount()===1) {
            return;
        }

        /* If the update resulted in no modification, it's possible that the
         * file did not exist, in which case we must raise an error. Checking
         * the write result's matched count will be most efficient, but fall
         * back to a findOne operation if necessary (i.e. legacy writes).
         */
        $found=$updateResult->getMatchedCount()!==null
            ?$updateResult->getMatchedCount()===1
            :$this->collectionWrapper->findFileById($id)!==null;

        if (!$found) {
            throw FileNotFoundException::byId($id, $this->getFilesNamespace());
        }
    }

    /**
     * Writes the contents of a readable stream to a GridFS file.
     *
     * Supported options:
     *
     *  * chunkSizeBytes (integer): The chunk size in bytes. Defaults to the
     *    bucket's chunk size.
     *
     * @param string   $filename Filename
     * @param resource $source Readable stream
     * @param array    $options Stream options
     *
     * @return ObjectId ID of the newly created GridFS file
     * @throws InvalidArgumentException
     */
    public function uploadFromStream($filename, $source, array $options = [])
    {
        $options+=['chunkSizeBytes'=>$this->options['chunkSizeBytes']];

        $stream=new WritableStream($this->collectionWrapper, $filename, $options);

        return $stream->uploadFromStream($source);
    }

    /**
     * Creates a path for an existing GridFS file.
     *
     * @param stdClass $file GridFS file document
     *
     * @return string
     */
    private function createPathForFile(stdClass $file)
    {
        if (!is_object($file->_id)||method_exists($file->_id, '__toString')) {
            $id=(string)$file->_id;
        } else {
            $id=\MongoDB\BSON\toJSON(\MongoDB\BSON\fromPHP(['_id'=>$file->_id]));
        }

        return sprintf(
            '%s://%s/%s.files/%s',
            self::$streamWrapperProtocol,
            urlencode($this->databaseName),
            urlencode($this->options['bucketName']),
            urlencode($id)
        );
    }

    /**
     * Creates a path for a new GridFS file, which does not yet have an ID.
     *
     * @return string
     */
    private function createPathForUpload()
    {
        return sprintf(
            '%s://%s/%s.files',
            self::$streamWrapperProtocol,
            urlencode($this->databaseName),
            urlencode($this->options['bucketName'])
        );
    }

    /**
     * Returns the names of the files collection.
     *
     * @return string
     */
    private function getFilesNamespace()
    {
        return sprintf('%s.%s.files', $this->databaseName, $this->options['bucketName']);
    }

    /**
     * Opens a readable stream for the GridFS file.
     *
     * @param stdClass $file GridFS file document
     *
     * @return resource
     */
    private function openDownloadStreamByFile(stdClass $file)
    {
        $path   =$this->createPathForFile($file);
        $context=stream_context_create([
            self::$streamWrapperProtocol=>[
                'collectionWrapper'=>$this->collectionWrapper,
                'file'             =>$file,
            ],
        ]);

        return fopen($path, 'r', false, $context);
    }

    /**
     * Registers the GridFS stream wrapper if it is not already registered.
     */
    private function registerStreamWrapper()
    {
        if (in_array(self::$streamWrapperProtocol, stream_get_wrappers())) {
            return;
        }

        StreamWrapper::register(self::$streamWrapperProtocol);
    }
}
