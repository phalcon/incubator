
Phalcon\Debug
===================

Debug help

Dump
--------
This utility class is meant to be used for dumping variables, heavily inspired by [Zend Framework's \Zend\Debug\Debug class](http://framework.zend.com/apidoc/2.1/classes/Zend.Debug.Debug.html).
Outputs var using var_dump() or xdebug_var_dump() and, if outputted, flushes Phalcons default output buffer.
Also, writes name of file and line from which it was called.

Basic usage:

```php

(new \Phalcon\Debug\Dump())->dump($varToDump);

```

Can be set to return output instead of echoing it using \Phalcon\Debug\Dump::setOutput() method

```php

if (ENVIRONMENT === 'production') {
    \Phalcon\Debug\Dump::setOutput(false);
}

// will return dump instead of echoing it
(new \Phalcon\Debug\Dump())->dump($varToDump);

```

If, for any reason, there is need to override \Phalcon\Debug\Dump::$output value,
behavior can be overriden by setting second argument of \Phalcon\Debug\Dump::dump method to true or false

```php

\Phalcon\Debug\Dump::setOutput(false);

// will return dump instead of echoing it
(new \Phalcon\Debug\Dump())->dump($varToDump);

// this will echo dump
(new \Phalcon\Debug\Dump())->dump($varToDump, true);

```

and

```php

\Phalcon\Debug\Dump::setOutput(true);

// this will echo dump
\Phalcon\Debug\Dump::dump($varToDump);

// will return dump instead of echoing it
(new \Phalcon\Debug\Dump())->dump($varToDump, false);

```

Convenient way of setting dump application wide (instead of making instance every time its called) can be using Phalcons DI:

```php

$this->getDI()->setShared('dump', function() {
    return new \Phalcon\Debug\Dump();
});

// ... later in application ...
$this->getDI()->getShared('dump')->dump($varToDump); // echoes dump

```

If calling ob_flush() every time after var is dumped is not wanted behaviour it can be changed by setting false in object constructor:
(WARNING: if dump is echoed anywhere in Phalcon application, except in view, and ob_flush() is not called, it will not be seen due to Phalcons output buffering)

```php

// ob_flush() will not be called
(new \Phalcon\Debug\Dump(false))->dump($varToDump);

```
