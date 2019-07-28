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
  | Authors: Andres Gutierrez <andres@phalconphp.com>                      |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Config\Adapter;

use LibXMLError;
use Phalcon\Config;
use Phalcon\Config\Exception;

/**
 * Phalcon\Config\Adapter\Xml
 *
 * Reads xml files and converts them to Phalcon\Config objects.
 *
 * Given the next configuration file:
 *
 * <code>
 * <?xml version="1.0"?>
 * <root>
 *   <phalcon>
 *     <baseuri>/phalcon/</baseuri>
 *   </phalcon>
 *   <models>
 *     <metadata>memory</metadata>
 *   </models>
 *   <nested>
 *     <config>
 *       <parameter>here</parameter>
 *     </config>
 *   </nested>
 * </root>
 * </code>
 *
 * You can read it as follows:
 *
 * <code>
 * use Phalcon\Config\Adapter\Xml;
 * $config = new Xml("path/config.xml");
 * echo $config->phalcon->baseuri;
 * echo $config->nested->config->parameter;
 * </code>
 *
 * @package Phalcon\Config\Adapter
 */
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

        $data = simplexml_load_file(
            $filePath,
            'SimpleXMLElement',
            LIBXML_NOCDATA
        );

        foreach (libxml_get_errors() as $error) {
            /** @var LibXMLError $error */
            switch ($error->code) {
                case LIBXML_ERR_WARNING:
                    trigger_error($error->message, E_USER_WARNING);
                    break;

                default:
                    throw new Exception($error->message);
            }
        }

        libxml_use_internal_errors(false);

        parent::__construct(
            json_decode(
                json_encode(
                    (array) $data
                ),
                true
            )
        );
    }
}
