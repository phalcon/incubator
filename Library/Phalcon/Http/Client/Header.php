<?php
/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2015 Phalcon Team (http://www.phalconphp.com)       |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file docs/LICENSE.txt.                        |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Author: Tuğrul Topuz <tugrultopuz@gmail.com>                           |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Http\Client;

use Phalcon\Http\Response\StatusCode;

class Header implements \Countable
{
    private $fields = [];
    public $version = '1.0';
    public $statusCode = 0;
    public $statusMessage = '';
    public $status = '';

    const BUILD_STATUS = 1;
    const BUILD_FIELDS = 2;

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function set($name, $value)
    {
        $this->fields[$name] = $value;
        return $this;
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function setMultiple(array $fields)
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function addMultiple(array $fields)
    {
        $this->fields = array_combine($this->fields, $fields);
        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        return $this->fields[$name];
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->fields;
    }

    /**
     * Determine if a header exists with a specific key.
     *
     * @param string $name Key to lookup.
     *
     * @return boolean
     */
    public function has($name)
    {
        foreach ($this->getAll() as $key => $value) {
            if (0 === strcmp(strtolower($key), strtolower($name))) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function remove($name)
    {
        unset($this->fields[$name]);
        return $this;
    }

    /**
     * @param $content
     * @return bool
     */
    public function parse($content)
    {
        if (empty($content)) {
            return false;
        }

        if (is_string($content)) {
            $content = array_filter(explode("\r\n", $content));
        } elseif (!is_array($content)) {
            return false;
        }

        $status = array();
        if (preg_match('/^HTTP\/(\d(?:\.\d)?)\s+(\d{3})\s+(.+)$/i', $content[0], $status)) {
            $this->status = array_shift($content);
            $this->version = $status[1];
            $this->statusCode = intval($status[2]);
            $this->statusMessage = $status[3];
        }

        foreach ($content as $field) {
            if (!is_array($field)) {
                $field = array_map('trim', explode(':', $field, 2));
            }

            if (count($field) == 2) {
                $this->set($field[0], $field[1]);
            }
        }

        return true;
    }

    /**
     * @param int $flags
     * @return array|string
     */
    public function build($flags = 0)
    {
        $lines = array();
        if (($flags & self::BUILD_STATUS) && StatusCode::message($this->statusCode)) {
            $lines[] = 'HTTP/' . $this->version . ' ' .
                $this->statusCode . ' ' .
                StatusCode::message($this->statusCode);
        }

        foreach ($this->fields as $field => $value) {
            $lines[] = $field . ': ' . $value;
        }

        if ($flags & self::BUILD_FIELDS) {
            return implode("\r\n", $lines);
        }

        return $lines;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->fields);
    }
}
