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
  | Author: Tuğrul Topuz <tugrultopuz@gmail.com>                           |
  +------------------------------------------------------------------------+
*/

namespace Phalcon\Http\Client;

use Phalcon\Http\Client\Provider\Curl;
use Phalcon\Http\Client\Provider\Exception as ProviderException;
use Phalcon\Http\Client\Provider\Stream;
use Phalcon\Http\Uri;

abstract class Request
{
    protected $baseUri;
    public $header = null;

    const VERSION = '0.0.2';

    public function __construct()
    {
        $this->baseUri = new Uri();
        $this->header = new Header();
    }

    public static function getProvider()
    {
        if (Curl::isAvailable()) {
            return new Curl();
        }

        if (Stream::isAvailable()) {
            return new Stream();
        }

        throw new ProviderException("There isn't any available provider");
    }

    public function setBaseUri($baseUri)
    {
        $this->baseUri = new Uri($baseUri);
    }

    public function getBaseUri()
    {
        return $this->baseUri;
    }

    public function resolveUri($uri)
    {
        return $this->baseUri->resolve($uri);
    }
}
