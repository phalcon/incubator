# Phalcon\Translate\Interpolator

Usage examples of the interpolators available here:

## Intl

It needs the extension [intl](php.net/manual/book.intl.php) to be installed in PHP, and it uses [MessageFormatter](http://php.net/manual/en/class.messageformatter.php) objects in an interpolator interface.
More about the syntax convention can be read on this [formating guide](https://www.sitepoint.com/localization-demystified-understanding-php-intl/) and on the [ICU documentation](http://userguide.icu-project.org/formatparse/messages).

```php
<?php
use Phalcon\Translate\Adapter\NativeArray;
use Phalcon\Translate\Interpolator\Intl;

$translate = new NativeArray([
    'interpolator' => new Intl('en_US'), // this interpolator must be locale aware
    'content' => ['hi-name' => 'Hello {name}, it\'s {time, number, integer} o\'clock']
]);

$name = 'Henry';
$translate->_('hi-name', ['name' => $name, 'time' => 8]); // Hello Henry, it's 8 o'clock
```

```php
<?php
use Phalcon\Translate\Adapter\NativeArray;
use Phalcon\Translate\Interpolator\Intl;

$translate = new NativeArray([
    'interpolator' => new Intl('fr_FR'), // this interpolator must be locale aware
    'content' => ['apples' => "{count, plural, =0{Je n'ai aucune pomme} =1{J'ai une pomme} other{J'ai # pommes}}."]
]);

// thousands separator is " " (blank space) for fr_FR
echo $translate->_('apples', ['count' => 1000]); // J'ai 1 000 pommes
```