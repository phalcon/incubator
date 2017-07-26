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

namespace Phalcon\Http;

class Uri
{
    private $parts = [];

    public function __construct($uri = null)
    {
        if (empty($uri)) {
            return;
        }

        if (is_string($uri)) {
            $this->parts = parse_url($uri);
            if (!empty($this->parts['query'])) {
                $query = [];
                parse_str($this->parts['query'], $query);
                $this->parts['query'] = $query;
            }

            return;
        }

        if ($uri instanceof self) {
            $this->parts = $uri->parts;

            return;
        }

        if (is_array($uri)) {
            $this->parts = $uri;

            return;
        }
    }

    public function __toString()
    {
        return $this->build();
    }

    public function __unset($name)
    {
        unset($this->parts[$name]);
    }

    public function __set($name, $value)
    {
        $this->parts[$name] = $value;
    }

    public function __get($name)
    {
        return $this->parts[$name];
    }

    public function __isset($name)
    {
        return isset($this->parts[$name]);
    }

    public function build()
    {
        $uri = '';
        $parts = $this->parts;

        if (!empty($parts['scheme'])) {
            $uri .= $parts['scheme'] . ':';
            if (!empty($parts['host'])) {
                $uri .= '//';
                if (!empty($parts['user'])) {
                    $uri .= $parts['user'];

                    if (!empty($parts['pass'])) {
                        $uri .= ':' . $parts['pass'];
                    }

                    $uri .= '@';
                }
                $uri .= $parts['host'];
            }
        }

        if (!empty($parts['port'])) {
            $uri .= ':' . $parts['port'];
        }

        if (!empty($parts['path'])) {
            $uri .= $parts['path'];
        }

        if (!empty($parts['query'])) {
            $uri .= '?' . $this->buildQuery($parts['query']);
        }

        if (!empty($parts['fragment'])) {
            $uri .= '#' . $parts['fragment'];
        }

        return $uri;
    }

    public function buildQuery($query)
    {
        return (is_array($query) ? http_build_query($query) : $query);
    }

    public function resolve($uri)
    {
        $newUri = new self($this);
        $newUri->extend($uri);

        return $newUri;
    }

    public function extend($uri)
    {
        if (!$uri instanceof self) {
            $uri = new self($uri);
        }

        $this->parts = array_merge(
            $this->parts,
            array_diff_key($uri->parts, array_flip(['query', 'path']))
        );

        if (!empty($uri->parts['query'])) {
            $this->extendQuery($uri->parts['query']);
        }

        if (!empty($uri->parts['path'])) {
            $this->extendPath($uri->parts['path']);
        }

        return $this;
    }

    public function extendQuery($params)
    {
        $query = empty($this->parts['query']) ? [] : $this->parts['query'];
        $params = empty($params) ? [] : $params;
        $this->parts['query'] = array_merge($query, $params);

        return $this;
    }

    public function extendPath($path)
    {
        if (empty($path)) {
            return $this;
        }

        if (!strncmp($path, '/', 1)) {
            $this->parts['path'] = $path;

            return $this;
        }

        if (empty($this->parts['path'])) {
            $this->parts['path'] = '/' . $path;

            return $this;
        }

        $this->parts['path'] = substr(
            $this->parts['path'],
            0,
            strrpos($this->parts['path'], '/') + 1
        ) . $path;

        return $this;
    }
}
