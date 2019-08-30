<?php

namespace Phalcon\Db\Adapter\MongoDB\GridFS;

/**
 * Stream wrapper for reading and writing a GridFS file.
 *
 * @internal
 * @see Bucket::openUploadStream()
 * @see Bucket::openDownloadStream()
 */
class StreamWrapper
{
    /**
     * @var resource|null Stream context (set by PHP)
     */
    public $context;

    private $mode;
    private $protocol;
    private $stream;

    public function getId()
    {
        return $this->stream->getId();
    }

    /**
     * Register the GridFS stream wrapper.
     *
     * @param string $protocol Protocol to use for stream_wrapper_register()
     */
    public static function register($protocol = 'gridfs')
    {
        if (in_array($protocol, stream_get_wrappers())) {
            stream_wrapper_unregister($protocol);
        }

        stream_wrapper_register($protocol, get_called_class(), \STREAM_IS_URL);
    }

    /**
     * Closes the stream.
     *
     * @see http://php.net/manual/en/streamwrapper.stream-close.php
     */
    // @codingStandardsIgnoreStart
    public function stream_close()
    {
        // @codingStandardsIgnoreEnd
        $this->stream->close();
    }

    /**
     * Returns whether the file pointer is at the end of the stream.
     *
     * @see http://php.net/manual/en/streamwrapper.stream-eof.php
     * @return boolean
     */
    // @codingStandardsIgnoreStart
    public function stream_eof()
    {
        // @codingStandardsIgnoreEnd
        return $this->stream->isEOF();
    }

    /**
     * Opens the stream.
     *
     * @see http://php.net/manual/en/streamwrapper.stream-open.php
     *
     * @param string  $path Path to the file resource
     * @param string  $mode Mode used to open the file (only "r" and "w" are supported)
     * @param integer $options Additional flags set by the streams API
     * @param string  $openedPath Not used
     */
    // @codingStandardsIgnoreStart
    public function stream_open($path, $mode, $options, &$openedPath)
    {
        // @codingStandardsIgnoreEnd
        $this->initProtocol($path);
        $this->mode=$mode;

        if ($mode==='r') {
            return $this->initReadableStream();
        }

        if ($mode==='w') {
            return $this->initWritableStream();
        }

        return false;
    }

    /**
     * Read bytes from the stream.
     *
     * Note: this method may return a string smaller than the requested length
     * if data is not available to be read.
     *
     * @see http://php.net/manual/en/streamwrapper.stream-read.php
     *
     * @param integer $count Number of bytes to read
     *
     * @return string
     */
    // @codingStandardsIgnoreStart
    public function stream_read($count)
    {
        // @codingStandardsIgnoreEnd
        // TODO: Ensure that $this->stream is a ReadableStream
        return $this->stream->downloadNumBytes($count);
    }

    /**
     * Return information about the stream.
     *
     * @see http://php.net/manual/en/streamwrapper.stream-stat.php
     * @return array
     */
    // @codingStandardsIgnoreStart
    public function stream_stat()
    {
        // @codingStandardsIgnoreEnd
        $stat=$this->getStatTemplate();

        $stat[2]=$stat['mode']=$this->mode;
        $stat[7]=$stat['size']=$this->stream->getSize();

        return $stat;
    }

    /**
     * Write bytes to the stream.
     *
     * @see http://php.net/manual/en/streamwrapper.stream-write.php
     *
     * @param string $data Data to write
     *
     * @return integer The number of bytes successfully stored
     */
    // @codingStandardsIgnoreStart
    public function stream_write($data)
    {
        // @codingStandardsIgnoreEnd
        // TODO: Ensure that $this->stream is a WritableStream
        $this->stream->insertChunks($data);

        return strlen($data);
    }

    /**
     * Returns a stat template with default values.
     *
     * @return array
     */
    private function getStatTemplate()
    {
        return [
            0        =>0,
            'dev'    =>0,
            1        =>0,
            'ino'    =>0,
            2        =>0,
            'mode'   =>0,
            3        =>0,
            'nlink'  =>0,
            4        =>0,
            'uid'    =>0,
            5        =>0,
            'gid'    =>0,
            6        =>-1,
            'rdev'   =>-1,
            7        =>0,
            'size'   =>0,
            8        =>0,
            'atime'  =>0,
            9        =>0,
            'mtime'  =>0,
            10       =>0,
            'ctime'  =>0,
            11       =>-1,
            'blksize'=>-1,
            12       =>-1,
            'blocks' =>-1,
        ];
    }

    /**
     * Initialize the protocol from the given path.
     *
     * @see StreamWrapper::stream_open()
     *
     * @param string $path
     */
    private function initProtocol($path)
    {
        $parts         =explode('://', $path, 2);
        $this->protocol=$parts[0]?:'gridfs';
    }

    /**
     * Initialize the internal stream for reading.
     *
     * @see StreamWrapper::stream_open()
     * @return boolean
     */
    private function initReadableStream()
    {
        $context=stream_context_get_options($this->context);

        $this->stream=new ReadableStream(
            $context[ $this->protocol ]['collectionWrapper'],
            $context[ $this->protocol ]['file']
        );

        return true;
    }

    /**
     * Initialize the internal stream for writing.
     *
     * @see StreamWrapper::stream_open()
     * @return boolean
     */
    private function initWritableStream()
    {
        $context=stream_context_get_options($this->context);

        $this->stream=new WritableStream(
            $context[ $this->protocol ]['collectionWrapper'],
            $context[ $this->protocol ]['filename'],
            $context[ $this->protocol ]['options']
        );

        return true;
    }
}
