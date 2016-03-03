<?php

/*
 +------------------------------------------------------------------------+
 | Phalcon Framework                                                      |
 +------------------------------------------------------------------------+
 | Copyright (c) 2011-2016 Phalcon Team (http://www.phalconphp.com)       |
 +------------------------------------------------------------------------+
 | This source file is subject to the New BSD License that is bundled     |
 | with this package in the file docs/LICENSE.txt.                        |
 |                                                                        |
 | If you did not receive a copy of the license and are unable to         |
 | obtain it through the world-wide-web, please send an email             |
 | to license@phalconphp.com so we can send you a copy immediately.       |
 +------------------------------------------------------------------------+
 | Authors: Andres Gutierrez <andres@phalconphp.com>                      |
 |          Serghei Iakovlev <serghei@phalconphp.com>                     |
 +------------------------------------------------------------------------+
 */

namespace Phalcon\Config\Adapter;

use Phalcon\Config;
use Phalcon\Config\Exception;

class Xml extends Config
{
    /**
     * Phalcon\Config\Adapter\Xml constructor
     *
     * @param string $filePath
     * @throws Exception
     */
    public function __construct($filePath)
    {
        if (!extension_loaded('SimpleXML')) {
            throw new Exception("SimpleXML extension not loaded");
        }

        libxml_use_internal_errors(true);
        $data = simplexml_load_file($filePath, 'SimpleXMLElement', LIBXML_NOCDATA);

        foreach (libxml_get_errors() as $error) {
            /** @var \LibXMLError $error */
            switch ($error->code) {
                case LIBXML_ERR_WARNING:
                    trigger_error($error->message, E_USER_WARNING);
                    break;
                default:
                    throw new Exception($error->message);
            }
        }

        parent::__construct(json_decode(json_encode((array) $data), true));
    }
}
