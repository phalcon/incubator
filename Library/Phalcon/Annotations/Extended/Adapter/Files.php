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
  | Authors: Serghei Iakovlev <serghei@phalconphp.com>                     |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Annotations\Extended\Adapter;

use DirectoryIterator;
use Phalcon\Annotations\Exception;
use Phalcon\Annotations\Reflection;
use Phalcon\Annotations\Extended\AbstractAdapter;

/**
 * Phalcon\Annotations\Extended\Adapter\Files
 *
 * Stores the parsed annotations in files.
 * This adapter is suitable for production.
 *
 * <code>
 * use Phalcon\Annotations\Adapter\Files;
 *
 * $annotations = new Files(
 *     [
 *         "annotationsDir" => "app/cache/annotations/",
 *     ]
 * );
 * </code>
 *
 * @package Phalcon\Annotations\Extended\Adapter
 */
class Files extends AbstractAdapter
{
    protected $annotationsDir = './';

    /**
     * Configurable properties.
     * @var array
     */
    protected $configurable = [
        'annotationsDir',
    ];

    /**
     * Files adapter constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        if (!isset($options['annotationsDir'])) {
            $options['annotationsDir'] = sys_get_temp_dir();
        }

        parent::__construct($options);
    }

    /**
     * Sets the annotations dir.
     *
     * @param  string $annotationsDir The storage key prefix.
     * @return $this
     */
    protected function setAnnotationsDir($annotationsDir)
    {
        $annotationsDir = (string) $annotationsDir;

        $this->annotationsDir = rtrim($annotationsDir, '\\/') . DIRECTORY_SEPARATOR;

        return $this;
    }

    /**
     * Reads parsed annotations from memory.
     *
     * @param  string $key
     * @return Reflection|bool
     *
     * @throws Exception
     */
    public function read($key)
    {
        $this->checkKey($key);

        $result = null;
        $path = $this->getPrefixedIdentifier($key);

        if (file_exists($path)) {
            /** @noinspection PhpIncludeInspection */
            $result = require $path;
        }

        return $this->castResult($result);
    }

    /**
     * Writes parsed annotations to files.
     *
     * @param  string     $key
     * @param  Reflection $reflection
     * @return bool
     *
     * @throws Exception
     */
    public function write($key, Reflection $reflection)
    {
        $this->checkKey($key);

        $path = $this->getPrefixedIdentifier($key);

        if (file_put_contents($path, '<?php return ' . var_export($reflection, true) . '; ') === false) {
            throw new Exception('Annotations directory cannot be written.');
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * <code>
     * use Phalcon\Annotations\Extended\Files;
     *
     * $annotations = new Files(['annotationsDir' => BASE_DIR . '/cache/']);
     * $annotations->flush();
     * </code>
     *
     * @return bool
     */
    public function flush()
    {
        $iterator = new DirectoryIterator($this->annotationsDir);

        foreach ($iterator as $item) {
            if ($item->isDot() || !$item->isFile() || $item->getExtension() !== 'php') {
                continue;
            }

            unlink(
                $item->getPathname()
            );
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $id
     * @return string
     */
    protected function getPrefixedIdentifier($key)
    {
        $key = strtolower(
            str_replace(
                [
                    '\\',
                    '/',
                    ':',
                ],
                '_',
                $key
            )
        );

        return $this->annotationsDir . preg_replace('#_{2,}#', '_', $key) . '.php';
    }
}
