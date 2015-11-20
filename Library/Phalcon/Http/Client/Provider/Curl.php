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

use Phalcon\Http\Client\Exception as HttpException;
use Phalcon\Http\Client\Provider\Exception as ProviderException;
use Phalcon\Http\Client\Request;
use Phalcon\Http\Client\Response;

class Curl extends Request
{
    private $handle = null;
    private $responseHeader = null;

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
        if ($this->handle === false) {
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
        $request = new self();
        $request->handle = curl_copy_handle($this->handle);

        return $request;
    }

    private function initOptions()
    {
        $this->setOptions(array(
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_AUTOREFERER     => true,
            //CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_MAXREDIRS       => 20,
            //CURLOPT_HEADER          => true,
            CURLOPT_PROTOCOLS       => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_USERAGENT       => 'Phalcon HTTP/'.self::VERSION.' (Curl)',
            CURLOPT_CONNECTTIMEOUT  => 30,
            CURLOPT_TIMEOUT         => 30,
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

    private function send($customHeader = array(), $fullResponse = false)
    {
        if (!empty($customHeader)) {
            $header = $customHeader;
        } else {
            $header = array();
            if (count($this->header) > 0) {
                $header = $this->header->build();
            }
            $header[] = 'Expect:';
        }

        $this->setOption(CURLOPT_HTTPHEADER, $header);
        $this->setOption(CURLOPT_HEADER, false);
        $this->setOption(CURLOPT_HEADERFUNCTION, array($this, "readHeader"));
        $this->responseHeader = "";

        $content = curl_exec($this->handle);

        if ($errno = curl_errno($this->handle)) {
            throw new HttpException(curl_error($this->handle), $errno);
        }

        //$headerSize = curl_getinfo($this->handle, CURLINFO_HEADER_SIZE);

        $response = new Response();
        $response->header->parse($this->responseHeader);

        if ($fullResponse) {
            $response->body = $this->responseHeader.$content;
        } else {
            $response->body = $content;
        }

        return $response;
    }

    /**
     * Header reader function for CURL
     *
     * @param  resource $curl
     * @param  string   $header
     * @return int
     */
    public function readHeader($curl, $header)
    {
        $this->responseHeader .= $header;

        return strlen($header);
    }

    /**
     * Prepare data for a cURL post.
     *
     * @param mixed   $params      Data to send.
     * @param boolean $useEncoding Whether to url-encode params. Defaults to true.
     *
     * @return void
     */
    private function initPostFields($params, $useEncoding = true)
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
     * @param string $auth Can be 'basic' or 'digest'
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
     *
     * @see http://curl.haxx.se/docs/manpage.html
     * @see http://www.nczonline.net/blog/2009/05/05/http-cookies-explained/
     */
    public function setCookies($cookies)
    {
        if (empty($cookies)) {
            return;
        }
        $cookieList = array();
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
        $this->setOptions(array(
            CURLOPT_PROXY     => $host,
            CURLOPT_PROXYPORT => $port,
        ));

        if (!empty($user) && is_string($user)) {
            $pair = $user;
            if (!empty($pass) && is_string($pass)) {
                $pair .= ':'.$pass;
            }
            $this->setOption(CURLOPT_PROXYUSERPWD, $pair);
        }
    }

    public function get($uri, $params = array(), $customHeader = array(), $fullResponse = false)
    {
        $uri = $this->resolveUri($uri);

        if (!empty($params)) {
            $uri->extendQuery($params);
        }

        $this->setOptions(array(
            CURLOPT_URL           => $uri->build(),
            CURLOPT_HTTPGET       => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        return $this->send($customHeader, $fullResponse);
    }

    public function head($uri, $params = array(), $customHeader = array(), $fullResponse = false)
    {
        $uri = $this->resolveUri($uri);

        if (!empty($params)) {
            $uri->extendQuery($params);
        }

        $this->setOptions(array(
            CURLOPT_URL           => $uri->build(),
            CURLOPT_HTTPGET       => true,
            CURLOPT_CUSTOMREQUEST => 'HEAD',
        ));

        return $this->send($customHeader, $fullResponse);
    }

    public function delete($uri, $params = array(), $customHeader = array(), $fullResponse = false)
    {
        $uri = $this->resolveUri($uri);

        if (!empty($params)) {
            $uri->extendQuery($params);
        }

        $this->setOptions(array(
            CURLOPT_URL           => $uri->build(),
            CURLOPT_HTTPGET       => true,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
        ));

        return $this->send($customHeader, $fullResponse);
    }

    public function post($uri, $params = array(), $useEncoding = true, $customHeader = array(), $fullResponse = false)
    {
        $this->setOptions(array(
            CURLOPT_URL           => $this->resolveUri($uri),
            CURLOPT_POST          => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
        ));

        $this->initPostFields($params, $useEncoding);

        return $this->send($customHeader, $fullResponse);
    }

    public function put($uri, $params = array(), $useEncoding = true, $customHeader = array(), $fullResponse = false)
    {
        $this->setOptions(array(
            CURLOPT_URL           => $this->resolveUri($uri),
            CURLOPT_POST          => true,
            CURLOPT_CUSTOMREQUEST => 'PUT',
        ));

        $this->initPostFields($params, $useEncoding, $customHeader);

        return $this->send($customHeader, $fullResponse);
    }
}
