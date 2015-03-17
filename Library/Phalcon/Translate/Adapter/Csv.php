<?php
namespace Phalcon\Translate\Adapter;

use Phalcon\Translate\Adapter;
use Phalcon\Translate\AdapterInterface;

class Csv extends Base implements AdapterInterface
{

    /**
     * @var array
     */
    protected $translate;

    /**
     * Class constructor.
     *
     * @param  array      $options
     * @throws \Exception
     */
    public function __construct($options)
    {
        if (!isset($options['file'])) {
            throw new \Exception('Parameter "file" is required.');
        }

        $default = array(
            'delimiter' => ';',
            'length'    => 0,
            'enclosure' => '"',
        );

        $options = array_merge($default, $options);
        if (false === ($file = @fopen($options['file'], 'rb'))) {
            throw new \Exception('Error opening translation file "' . $options['file'] . '".');
        }

        while (false !== ($data = fgetcsv($file, $options['length'], $options['delimiter'], $options['enclosure']))) {
            if (substr($data[0], 0, 1) === '#' || !isset($data[1])) {
                continue;
            }

            $this->translate[$data[0]] = $data[1];
        }

        @fclose($file);
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $index
     * @param  array  $placeholders
     * @return string
     */
    public function query($index, $placeholders = null)
    {
        if (!$this->exists($index)) {
            return $index;
        }

        return self::setPlaceholders($this->translate[$index], $placeholders);
    }

    /**
     * {@inheritdoc}
     *
     * @param  string  $index
     * @return boolean
     */
    public function exists($index)
    {
        return isset($this->translate[$index]);
    }
}
