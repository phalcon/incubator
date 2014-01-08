
Phalcon\Debug
===================

Debug help

Dump
--------
This utility class is meant to be used for dumping variables, heavily inspired by [Zend Framework's \Zend\Debug\Debug class](http://framework.zend.com/apidoc/2.1/classes/Zend.Debug.Debug.html).
Outputs var using var_dump or xdebug_var_dump and, if outputted, flushes Phalcons default output buffer.
Also, writes name of file and line where it was called.

Basic usage:

```php

\Phalcon\Debug\Dump::dump($varToDump);

```

Can be set to return output instead of echoing it using \Phalcon\Debug\Dump::setDebug() method

```php

if (ENVIRONMENT === 'production') {
    \Phalcon\Debug\Dump::setOutput(false);
}

// will return dump instead of echoing it
\Phalcon\Debug\Dump::dump($varToDump);

```