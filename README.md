# Phalcon Incubator

[![Build Status](https://img.shields.io/travis/phalcon/incubator/master.svg?style=flat-square)](https://travis-ci.org/phalcon/incubator)
[![Latest Version](https://img.shields.io/packagist/v/phalcon/incubator.svg?style=flat-square)](https://github.com/phalcon/incubator/releases)
[![Software License](https://img.shields.io/badge/license-BSD--3-brightgreen.svg?style=flat-square)](https://github.com/phalcon/incubator/blob/master/LICENSE.txt)
[![Total Downloads](https://img.shields.io/packagist/dt/phalcon/incubator.svg?style=flat-square)](https://packagist.org/packages/phalcon/incubator)
[![Daily Downloads](https://img.shields.io/packagist/dd/phalcon/incubator.svg?style=flat-square)](https://packagist.org/packages/phalcon/incubator)

This is a repository to publish/share/experiment with new adapters, prototypes or functionality that can potentially be incorporated into the [Phalcon Framework](https://github.com/phalcon/cphalcon).

We also welcome submissions of snippets from the community, to further extend the framework.

The code in this repository is written in PHP.

## Installation

### Installing via Composer

Install Composer in a common location or in your project:

```bash
curl -s http://getcomposer.org/installer | php
```

Then create the `composer.json` file as follows:

```json
{
    "require": {
        "phalcon/incubator": "^3.4"
    }
}
```

If you are still using Phalcon 2.0.x, create the `composer.json` file as follows:

```json
{
    "require": {
        "phalcon/incubator": "^2.0"
    }
}
```

Run the composer installer:

```bash
$ php composer.phar install
```

### Installing via GitHub

Just clone the repository in a common location or inside your project:

```
git clone https://github.com/phalcon/incubator.git
```

For a specific git branch (eg 2.0.13) please use:

```
git clone -b 2.0.13 git@github.com:phalcon/incubator.git
```

## Autoloading from the Incubator

Add or register the following namespace strategy to your `Phalcon\Loader` in order
to load classes from the incubator repository:

```php

$loader = new Phalcon\Loader();

$loader->registerNamespaces(
    [
        'Phalcon' => '/path/to/incubator/Library/Phalcon/',
    ]
);

$loader->register();
```

## Testing

Tests are located in `tests/` and use Codeception. See [tests/README.md](tests/README.md).

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md).

## Contributions Index

See [INDEX.md](INDEX.md).

## License

Incubator is open-sourced software licensed under the [New BSD License](https://github.com/phalcon/incubator/blob/master/LICENSE.txt).<br>
© 2011-2018, Phalcon Framework Team
