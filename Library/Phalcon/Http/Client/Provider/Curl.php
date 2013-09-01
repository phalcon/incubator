<?php

/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2012 Phalcon Team (http://www.phalconphp.com)       |
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

namespace Phalcon\Http\Client\Provider;

use Phalcon\Http\Client\Request;
use Phalcon\Http\Client\Response;
use Phalcon\Http\Client\Provider\Exception as ProviderException;
use Phalcon\Http\Client\Exception as HttpException;

class Curl extends Request
{
    private $handle = null;

    public static function isAvailable()
    {
        return extension_loaded('curl');
    }

    function __construct()
    {
        if (!self::isAvailable()) {
            throw new ProviderException('CURL extention is not loaded');
        }

        $this->handle = curl_init();
        $this->initOptions();
        parent::__construct();
    }

    function __destruct()
    {
        curl_close($this->handle);
    }

    function __clone()
    {
        $request = new self;
        $request->handle = curl_copy_handle($this->handle);
        return $request;
    }

    private function initOptions()
    {
        $this->setOptions(array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 20,
            CURLOPT_HEADER => true,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_USERAGENT => 'Phalcon HTTP/' . self::VERSION . ' (Curl)',
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT => 30
        ));
    }

    public function setOption($option, $value)
    {
        return curl_setopt($this->handle, $option, $value);
    }

    public function setOptions($options)
    {
        return curl_setopt_array($this->handle, $options);
    }

    public function setTimeout($timeout)
    {
        $this->setOption(CURLOPT_TIMEOUT, $timeout);
    }

    public function setConnectTimeout($timeout)
    {
        $this->setOption(CURLOPT_CONNECTTIMEOUT, $timeout);
    }

    private function send()
    {
        $header = array();
        if (count($this->header) > 0) {
            $header = $this->header->build();
        }
        $header[] = 'Expect:';
        $this->setOption(CURLOPT_HTTPHEADER, $header);

        $content = curl_exec($this->handle);

        if ($errno = curl_errno($this->handle)) {
            throw new HttpException(curl_error($this->handle), $errno);
        }

        $headerSize = curl_getinfo($this->handle, CURLINFO_HEADER_SIZE);

        $response = new Response();
        $response->header->parse(substr($content, 0, $headerSize));
        $response->body = substr($content, $headerSize);

        return $response;
    }

    private function initPostFields($params)
    {
        $multiPart = false;
        foreach ($params as $param) {
            if (is_string($param) && preg_match('/^@/', $param)) {
                $multiPart = true;
                break;
            }
        }

        if (!empty($params) && is_array($params)) {
            $this->setOption(CURLOPT_POSTFIELDS, $multiPart ? $params : http_build_query($params));
        }
    }

    public function setProxy($host, $port = 8080, $user = null, $pass = null)
    {
        $this->setOptions(array(
            CURLOPT_PROXY => $host,
            CURLOPT_PROXYPORT => $port
        ));

        if (!empty($user) && is_string($user)) {
            $pair = $user;
            if (!empty($pass) && is_string($pass)) {
                $pair .= ':' . $pass;
            }
            $this->setOption(CURLOPT_PROXYUSERPWD, $pair);
        }
    }

    public function get($uri, $params = array())
    {
        $uri = $this->resolveUri($uri);

        if (!empty($params)) {
            $uri->extendQuery($params);
        }

        $this->setOptions(array(
           CURLOPT_URL => $uri->build(),
           CURLOPT_HTTPGET => true,
           CURLOPT_CUSTOMREQUEST => 'GET'
        ));
        
        return $this->send();
    }

    public function head($uri, $params = array())
    {
        $uri = $this->resolveUri($uri);

        if (!empty($params)) {
            $uri->extendQuery($params);
        }

        $this->setOptions(array(
           CURLOPT_URL => $uri->build(),
           CURLOPT_HTTPGET => true,
           CURLOPT_CUSTOMREQUEST => 'HEAD'
        ));
        
        return $this->send();
    }

    public function delete($uri, $params = array())
    {
        $uri = $this->resolveUri($uri);

        if (!empty($params)) {
            $uri->extendQuery($params);
        }

        $this->setOptions(array(
            CURLOPT_URL => $uri->build(),
            CURLOPT_HTTPGET => true,
            CURLOPT_CUSTOMREQUEST => 'DELETE'
        ));

        return $this->send();
    }

    public function post($uri, $params = array())
    {
        $this->setOptions(array(
            CURLOPT_URL => $this->resolveUri($uri),
            CURLOPT_POST => true,
            CURLOPT_CUSTOMREQUEST => 'POST'
        ));

        $this->initPostFields($params);

        return $this->send();
    }

    public function put($uri, $params = array())
    {
        $this->setOptions(array(
            CURLOPT_URL => $this->resolveUri($uri),
            CURLOPT_POST => true,
            CURLOPT_CUSTOMREQUEST => 'PUT'
        ));

        $this->initPostFields($params);

        return $this->send();
    }
}
