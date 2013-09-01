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
use Phalcon\Http\Client\Header;
use Phalcon\Http\Client\Provider\Exception as ProviderException;
use Phalcon\Http\Client\Exception as HttpException;
use Phalcon\Http\Client\Uri;

class Stream extends Request
{
    private $context = null;

    public static function isAvailable()
    {
        $wrappers = stream_get_wrappers();
        return in_array('http', $wrappers) && in_array('https', $wrappers);
    }

    function __construct()
    {
        if (!self::isAvailable()) {
            throw new ProviderException('HTTP or HTTPS stream wrappers not registered');
        }

        $this->context = stream_context_create();
        $this->initOptions();
        parent::__construct();
    }

    function __destruct()
    {
        $this->context = null;
    }

    private function initOptions()
    {
        $this->setOptions(array(
            'user_agent' => 'Phalcon HTTP/' . self::VERSION . ' (Stream)',
            'follow_location' => 1,
            'max_redirects' => 20,
            'timeout' => 30
        ));
    }

    public function setOption($option, $value)
    {
        return stream_context_set_option($this->context, 'http', $option, $value);
    }

    public function setOptions($options)
    {
        return stream_context_set_option($this->context, array('http' => $options));
    }

    public function setTimeout($timeout)
    {
        $this->setOption('timeout', $timeout);
    }

    private function errorHandler($errno, $errstr)
    {
        throw new HttpException($errstr, $errno);
    }

    private function send($uri)
    {
        if (count($this->header) > 0) {
            $this->setOption('header', $this->header->build(Header::BUILD_FIELDS));
        }

        set_error_handler(array($this, 'errorHandler'));
        $content = file_get_contents($uri->build(), false, $this->context);
        restore_error_handler();

        $response = new Response();
        $response->header->parse($http_response_header);
        $response->body = $content;

        return $response;
    }

    private function initPostFields($params)
    {
        if (!empty($params) && is_array($params)) {
            $this->setHeader('Content-Type', 'application/x-www-form-urlencoded');
            $this->setOption('content', http_build_query($params));
        }
    }

    public function setProxy($host, $port = 8080, $user = null, $pass = null)
    {
        $uri = new Uri(array(
            'scheme' => 'tcp',
            'host' => $host,
            'port' => $port
        ));

        if (!empty($user)) {
            $uri->user = $user;
            if (!empty($pass)) {
                $uri->pass = $pass;
            }
        }

        $this->setOption('proxy', $uri->build());
    }

    public function get($uri, $params = array())
    {
        $uri = $this->resolveUri($uri);

        if (!empty($params)) {
            $uri->extendQuery($params);
        }

        $this->setOptions(array(
            'method' => 'GET',
            'content' => ''
        ));

        $this->header->remove('Content-Type');

        return $this->send($uri);
    }

    public function head($uri, $params = array())
    {
        $uri = $this->resolveUri($uri);

        if (!empty($params)) {
            $uri->extendQuery($params);
        }

        $this->setOptions(array(
            'method' => 'HEAD',
            'content' => ''
        ));

        $this->header->remove('Content-Type');

        return $this->send($uri);
    }

    public function delete($uri, $params = array())
    {
        $uri = $this->resolveUri($uri);

        if (!empty($params)) {
            $uri->extendQuery($params);
        }

        $this->setOptions(array(
            'method' => 'DELETE',
            'content' => ''
        ));

        $this->header->remove('Content-Type');

        return $this->send($uri);
    }

    public function post($uri, $params = array())
    {
        $this->setOption('method', 'POST');

        $this->initPostFields($params);

        return $this->send($this->resolveUri($uri));
    }

    public function put($uri, $params = array())
    {
        $this->setOption('method', 'PUT');

        $this->initPostFields($params);

        return $this->send($this->resolveUri($uri));
    }
}
