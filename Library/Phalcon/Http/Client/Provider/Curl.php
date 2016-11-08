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

namespace Phalcon\Http\Client\Provider;

use Phalcon\Http\Client\Exception as HttpException;
use Phalcon\Http\Client\Provider\Exception as ProviderException;
use Phalcon\Http\Client\Request;
use Phalcon\Http\Client\Response;
use Phalcon\Http\Request\Method;

class Curl extends Request
{
    private $handle = null;
    private $responseHeader = '';

    public static function isAvailable()
    {
        return extension_loaded('curl');
    }

    public function __construct()
    {
        if (!self::isAvailable()) {
            throw new ProviderException('CURL extension is not loaded');
        }

        $this->handle = curl_init();
        if (!is_resource($this->handle)) {
            throw new HttpException(curl_error($this->handle), 'curl');
        }

        $this->initOptions();
        parent::__construct();
    }

    public function __destruct()
    {
        curl_close($this->handle);
    }

    public function __clone()
    {
        $request = new self;
        $request->handle = curl_copy_handle($this->handle);

        return $request;
    }

    public function headerFunction($ch, $headerLine)
    {
        $this->responseHeader .= $headerLine;

        return strlen($headerLine);
    }

    private function initOptions()
    {
        $this->setOptions([
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_AUTOREFERER     => true,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_MAXREDIRS       => 20,
            CURLOPT_HEADER          => false,
            CURLOPT_PROTOCOLS       => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_USERAGENT       => 'Phalcon HTTP/' . self::VERSION . ' (Curl)',
            CURLOPT_CONNECTTIMEOUT  => 30,
            CURLOPT_TIMEOUT         => 30,
        ]);
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

    /**
     * Sends the request and returns the response.
     *
     * <code>
     * // using custom headers:
     * $customHeader = array(
     *     0 => 'Accept: text/plain',
     *     1 => 'X-Foo: bar',
     *     2 => 'X-Bar: baz',
     * );
     * $response = $this->send($customHeader);
     * </code>
     *
     * @param array $customHeader An array of values. If not empty then previously added headers gets ignored.
     * @param bool  $fullResponse If true returns the full response (including headers).
     *
     * @return Response
     * @throws HttpException
     */
    protected function send(array $customHeader = [], $fullResponse = false)
    {
        if (!empty($customHeader)) {
            $header = $customHeader;
        } else {
            $header = [];
            if (count($this->header) > 0) {
                $header = $this->header->build();
            }
        }
        $header[] = 'Expect:';
        $header = array_unique($header, SORT_STRING);

        $this->responseHeader = '';

        $this->setOption(CURLOPT_HEADERFUNCTION, [$this, 'headerFunction']);
        $this->setOption(CURLOPT_HTTPHEADER, $header);

        $content = curl_exec($this->handle);

        if ($errno = curl_errno($this->handle)) {
            throw new HttpException(curl_error($this->handle), $errno);
        }

        $response = new Response();
        $response->header->parse($this->responseHeader);

        if ($fullResponse) {
            $response->body = $content;
        } else {
            $response->body = substr($content, strlen($this->responseHeader));
        }

        return $response;
    }

    /**
     * Prepare data for a cURL post.
     *
     * @param mixed   $params      Data to send.
     * @param boolean $useEncoding Whether to url-encode params. Defaults to true.
     *
     * @return void
     */
    protected function initPostFields($params, $useEncoding = true)
    {
        if (is_array($params)) {
            foreach ($params as $param) {
                if (is_string($param) && preg_match('/^@/', $param)) {
                    $useEncoding = false;
                    break;
                }
            }

            if ($useEncoding) {
                $params = http_build_query($params);
            }
        }

        if (!empty($params)) {
            $this->setOption(CURLOPT_POSTFIELDS, $params);
        }
    }

    /**
     * Setup authentication
     *
     * @param string $user
     * @param string $pass
     * @param string $auth
     */
    public function setAuth($user, $pass, $auth = 'basic')
    {
        $this->setOption(CURLOPT_HTTPAUTH, constant('CURLAUTH_'.strtoupper($auth)));
        $this->setOption(CURLOPT_USERPWD, $user.":".$pass);
    }

    /**
     * Set cookies for this session
     *
     * @param array $cookies
     * @link http://curl.haxx.se/docs/manpage.html
     * @link http://www.nczonline.net/blog/2009/05/05/http-cookies-explained/
     */
    public function setCookies(array $cookies)
    {
        if (empty($cookies)) {
            return;
        }

        $cookieList = [];
        foreach ($cookies as $cookieName => $cookieValue) {
            $cookie = urlencode($cookieName);

            if (isset($cookieValue)) {
                $cookie .= '=';
                $cookie .= urlencode($cookieValue);
            }

            $cookieList[] = $cookie;
        }

        $this->setOption(CURLOPT_COOKIE, implode(';', $cookieList));
    }

    public function setProxy($host, $port = 8080, $user = null, $pass = null)
    {
        $this->setOptions([
            CURLOPT_PROXY     => $host,
            CURLOPT_PROXYPORT => $port
        ]);

        if (!empty($user) && is_string($user)) {
            $pair = $user;
            if (!empty($pass) && is_string($pass)) {
                $pair .= ':' . $pass;
            }
            $this->setOption(CURLOPT_PROXYUSERPWD, $pair);
        }
    }

    public function get($uri, $params = [], $customHeader = [], $fullResponse = false)
    {
        $uri = $this->resolveUri($uri);

        if (!empty($params)) {
            $uri->extendQuery($params);
        }

        $this->setOptions([
            CURLOPT_URL           => $uri->build(),
            CURLOPT_HTTPGET       => true,
            CURLOPT_CUSTOMREQUEST => Method::GET,
        ]);

        return $this->send($customHeader, $fullResponse);
    }

    public function head($uri, $params = [], $customHeader = [], $fullResponse = false)
    {
        $uri = $this->resolveUri($uri);

        if (!empty($params)) {
            $uri->extendQuery($params);
        }

        $this->setOptions([
            CURLOPT_URL           => $uri->build(),
            CURLOPT_HTTPGET       => true,
            CURLOPT_CUSTOMREQUEST => Method::HEAD,
        ]);

        return $this->send($customHeader, $fullResponse);
    }

    public function delete($uri, $params = [], $customHeader = [], $fullResponse = false)
    {
        $uri = $this->resolveUri($uri);

        if (!empty($params)) {
            $uri->extendQuery($params);
        }

        $this->setOptions([
            CURLOPT_URL           => $uri->build(),
            CURLOPT_HTTPGET       => true,
            CURLOPT_CUSTOMREQUEST => Method::DELETE,
        ]);

        return $this->send($customHeader, $fullResponse);
    }

    public function post($uri, $params = [], $useEncoding = true, $customHeader = [], $fullResponse = false)
    {
        $this->setOptions([
            CURLOPT_URL           => $this->resolveUri($uri),
            CURLOPT_POST          => true,
            CURLOPT_CUSTOMREQUEST => Method::POST,
        ]);

        $this->initPostFields($params, $useEncoding);

        return $this->send($customHeader, $fullResponse);
    }

    public function put($uri, $params = [], $useEncoding = true, $customHeader = [], $fullResponse = false)
    {
        $this->setOptions([
            CURLOPT_URL           => $this->resolveUri($uri),
            CURLOPT_POST          => true,
            CURLOPT_CUSTOMREQUEST => Method::PUT,
        ]);

        $this->initPostFields($params, $useEncoding);

        return $this->send($customHeader, $fullResponse);
    }

    public function patch($uri, $params = [], $useEncoding = true, $customHeader = [], $fullResponse = false)
    {
        $this->setOptions([
            CURLOPT_URL           => $this->resolveUri($uri),
            CURLOPT_POST          => true,
            CURLOPT_CUSTOMREQUEST => Method::PATCH,
        ]);

        $this->initPostFields($params, $useEncoding);

        return $this->send($customHeader, $fullResponse);
    }
}
