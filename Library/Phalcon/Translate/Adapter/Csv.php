<?php
namespace Phalcon\Translate\Adapter;

use Phalcon\Translate\Adapter;
use Phalcon\Translate\AdapterInterface;

class Csv extends Adapter implements AdapterInterface
{
    protected $_translate;

    /**
     * Phalcon\Translate\Adapter\Csv constructor
     *
     * @param array $options
     * @throws \Exception
     */
    public function __construct($options)
    {
        if (!isset($options['file'])) {
            throw new \Exception('Parameter "file" is required.');
        }

        $default = [
            'delimiter' => ';',
            'length'    => 0,
            'enclosure' => '"',
        ];

        $options = array_merge($default, $options);
        if (false === ($file = @fopen($options['file'], 'rb'))) {
            throw new \Exception('Error opening translation file "' . $options['file'] . '".');
        }

        while (false !== ($data = fgetcsv($file, $options['length'], $options['delimiter'], $options['enclosure']))) {
            if (substr($data[0], 0, 1) === '#' || !isset($data[1])) {
                continue;
            }

            $this->_translate[$data[0]] = $data[1];
        }
        @fclose($file);
    }

    /**
     * Returns the translation related to the given key
     *
     * @param string $index
     * @param null   $placeholders
     * @return string
     */
    public function query($index, $placeholders = null)
    {
        if (!$this->exists($index)) {
            return $index;
        }

        $translation = $this->_translate[$index];
        if (is_array($placeholders)) {
            foreach ($placeholders as $key => $value) {
                $translation = str_replace('%' . $key . '%', $value, $translation);
            }
        }

        return $translation;
    }

    /**
     * Check whether is defined a translation key in the csv
     *
     * @param string $index
     * @return bool
     */
    public function exists($index)
    {
        return isset($this->_translate[$index]);
    }
}
