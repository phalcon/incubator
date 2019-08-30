<?php

namespace Phalcon\Db\Adapter\MongoDB\GridFS;

use Phalcon\Db\Adapter\MongoDB\Collection;
use Phalcon\Db\Adapter\MongoDB\UpdateResult;
use MongoDB\Driver\Cursor;
use MongoDB\Driver\Manager;
use MongoDB\Driver\ReadPreference;
use IteratorIterator;
use stdClass;

/**
 * CollectionWrapper abstracts the GridFS files and chunks collections.
 *
 * @internal
 */
class CollectionWrapper
{
    private $chunksCollection;
    private $checkedIndexes=false;
    private $filesCollection;

    /**
     * Constructs a GridFS collection wrapper.
     *
     * @see Collection::__construct() for supported options
     *
     * @param Manager $manager Manager instance from the driver
     * @param string  $databaseName Database name
     * @param string  $bucketName Bucket name
     * @param array   $collectionOptions Collection options
     *
     * @throws InvalidArgumentException
     */
    public function __construct(Manager $manager, $databaseName, $bucketName, array $collectionOptions = [])
    {
        $this->filesCollection =new Collection(
            $manager,
            $databaseName,
            sprintf('%s.files', $bucketName),
            $collectionOptions
        );
        $this->chunksCollection=new Collection(
            $manager,
            $databaseName,
            sprintf('%s.chunks', $bucketName),
            $collectionOptions
        );
    }

    /**
     * Deletes all GridFS chunks for a given file ID.
     *
     * @param mixed $id
     */
    public function deleteChunksByFilesId($id)
    {
        $this->chunksCollection->deleteMany(['files_id'=>$id]);
    }

    /**
     * Deletes a GridFS file and related chunks by ID.
     *
     * @param mixed $id
     */
    public function deleteFileAndChunksById($id)
    {
        $this->filesCollection->deleteOne(['_id'=>$id]);
        $this->chunksCollection->deleteMany(['files_id'=>$id]);
    }

    /**
     * Drops the GridFS files and chunks collections.
     */
    public function dropCollections()
    {
        $this->filesCollection->drop();
        $this->chunksCollection->drop();
    }

    /**
     * Finds a GridFS file document for a given filename and revision.
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
     * @see Bucket::downloadToStreamByName()
     * @see Bucket::openDownloadStreamByName()
     *
     * @param string  $filename
     * @param integer $revision
     *
     * @return stdClass|null
     */
    public function findFileByFilenameAndRevision($filename, $revision)
    {
        $filename=(string)$filename;
        $revision=(integer)$revision;

        if ($revision<0) {
            $skip     =abs($revision)-1;
            $sortOrder=-1;
        } else {
            $skip     =$revision;
            $sortOrder=1;
        }

        return $this->filesCollection->findOne(['filename'=>$filename], [
                'skip'   =>$skip,
                'sort'   =>['uploadDate'=>$sortOrder],
                'typeMap'=>['root'=>'stdClass'],
            ]);
    }

    /**
     * Finds a GridFS file document for a given ID.
     *
     * @param mixed $id
     *
     * @return stdClass|null
     */
    public function findFileById($id)
    {
        return $this->filesCollection->findOne(['_id'=>$id], ['typeMap'=>['root'=>'stdClass']]);
    }

    /**
     * Finds documents from the GridFS bucket's files collection.
     *
     * @see Find::__construct() for supported options
     *
     * @param array|object $filter Query by which to filter documents
     * @param array        $options Additional options
     *
     * @return Cursor
     */
    public function findFiles($filter, array $options = [])
    {
        return $this->filesCollection->find($filter, $options);
    }

    // TODO: Remove this
    public function getChunksCollection()
    {
        return $this->chunksCollection;
    }

    /**
     * Returns a chunks iterator for a given file ID.
     *
     * @param mixed $id
     *
     * @return IteratorIterator
     */
    public function getChunksIteratorByFilesId($id)
    {
        $cursor=$this->chunksCollection->find(['files_id'=>$id], [
                'sort'   =>['n'=>1],
                'typeMap'=>['root'=>'stdClass'],
            ]);

        return new IteratorIterator($cursor);
    }

    // TODO: Remove this
    public function getFilesCollection()
    {
        return $this->filesCollection;
    }

    /**
     * Inserts a document into the chunks collection.
     *
     * @param array|object $chunk Chunk document
     */
    public function insertChunk($chunk)
    {
        if (!$this->checkedIndexes) {
            $this->ensureIndexes();
        }

        $this->chunksCollection->insertOne($chunk);
    }

    /**
     * Inserts a document into the files collection.
     *
     * The file document should be inserted after all chunks have been inserted.
     *
     * @param array|object $file File document
     */
    public function insertFile($file)
    {
        if (!$this->checkedIndexes) {
            $this->ensureIndexes();
        }

        $this->filesCollection->insertOne($file);
    }

    /**
     * Updates the filename field in the file document for a given ID.
     *
     * @param mixed  $id
     * @param string $filename
     *
     * @return UpdateResult
     */
    public function updateFilenameForId($id, $filename)
    {
        return $this->filesCollection->updateOne(['_id'=>$id], ['$set'=>['filename'=>(string)$filename]]);
    }

    /**
     * Create an index on the chunks collection if it does not already exist.
     */
    private function ensureChunksIndex()
    {
        foreach ($this->chunksCollection->listIndexes() as $index) {
            if ($index->isUnique()&&$index->getKey()===['files_id'=>1,'n'=>1]) {
                return;
            }
        }

        $this->chunksCollection->createIndex(['files_id'=>1,'n'=>1], ['unique'=>true]);
    }

    /**
     * Create an index on the files collection if it does not already exist.
     */
    private function ensureFilesIndex()
    {
        foreach ($this->filesCollection->listIndexes() as $index) {
            if ($index->getKey()===['filename'=>1,'uploadDate'=>1]) {
                return;
            }
        }

        $this->filesCollection->createIndex(['filename'=>1,'uploadDate'=>1]);
    }

    /**
     * Ensure indexes on the files and chunks collections exist.
     *
     * This method is called once before the first write operation on a GridFS
     * bucket. Indexes are only be created if the files collection is empty.
     */
    private function ensureIndexes()
    {
        if ($this->checkedIndexes) {
            return;
        }

        $this->checkedIndexes=true;

        if (!$this->isFilesCollectionEmpty()) {
            return;
        }

        $this->ensureFilesIndex();
        $this->ensureChunksIndex();
    }

    /**
     * Returns whether the files collection is empty.
     *
     * @return boolean
     */
    private function isFilesCollectionEmpty()
    {
        return null===$this->filesCollection->findOne([], [
            'readPreference'=>new ReadPreference(ReadPreference::RP_PRIMARY),
            'projection'    =>['_id'=>1],
        ]);
    }
}
