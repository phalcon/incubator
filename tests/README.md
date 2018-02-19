# Phalcon Incubator Tests

Welcome to the Phalcon Incubator Testing Suites.

This folder includes all the tests that test Incubator components, ensuring that you enjoy a bug free library.

## Getting Started

These testing suites uses [Travis CI](https://travis-ci.org/phalcon/incubator) for each run. Every commit pushed to this repository will queue a build into the [continuous integration](https://en.wikipedia.org/wiki/Continuous_integration) service and will run all tests to ensure that everything is going well and the project is stable.

The testing suites can be run on your own machine. The main dependency is [Codeception](http://codeception.com/) which can be installed using [Composer](http://getcomposer.org/):

```bash
# run this command from project root
composer install --prefer-source
```

You can read more about installing and configuring Codeception from the following resources:

* [Codeception Introduction](http://codeception.com/docs/01-Introduction)
* [Codeception Console Commands](http://codeception.com/docs/reference/Commands)

## Requirements

A MySQL database is also required in this suite. You'll need to create database and configure connection in `tests/.env` file.

Some tests uses Aerospike database and they are run separately. To uses those tests you need to install the [Aerospike Server](https://www.aerospike.com/download/server), [Aerospike Client](https://www.aerospike.com/download/client/php) and create the database.

You may need the following services to run other tests:

* Memcached
* Redis
* MongoDB
* Beanstalk

## Docker Compose

As an alternative to installing the above requirements, you may also utilize the included docker-compose.yml. This requires docker-compose and docker to be installed. You will need to update your `tests/.env` file appropriately to point to the resulting docker containers. You may start the containers by running:

```bash
docker-compose up
```

## Run tests

First you need to re-generate base classes for test all suites:

```bash
vendor/bin/codecept build
```

Once the database is created and base clases re-generated, run the tests on a terminal:

```bash
vendor/bin/codecept run
```

or for detailed output:

```bash
vendor/bin/codecept run --debug
```

To run all tests from a folder:

```bash
vendor/bin/codecept run tests/unit/some/folder/
```

To run legacy tests (PHP 5.x) you need to use the `unit5x` suite:

```bash
vendor/bin/codecept run tests/unit5x/
```

To run Aerospike-related tests you need to use the `aerospike` suite:

```bash
vendor/bin/codecept run tests/aerospike/
```

To run single test:

```bash
vendor/bin/codecept run tests/unit/some/folder/some/test/file.php
```

## Advanced Configuration
The test suites ship with `.dist.yml` configuration files:

 ```
 codeception.dist.yml
 tests/aerospike.suite.dist.yml
 tests/unit.suite.5.dist.yml
 tests/unit.suite.dist.yml
 tests/unit5x.suite.dist.yml
 ```

 You may override the options in each of these files by creating a new configuration file of the same name, without the `.dist`. For example, to auto-populate/reset the database on every unit test, create the file `tests/unit.suite.yml` with:

 ```yaml
 modules:
     config:
         Db:
             populate: true
 ```

For additional configuration options, see http://codeception.com.

## Help

**Note:** Cache-related tests are slower than others tests because they use wait states (sleep command) to expire generated caches.

The file `.travis.yml` contains full instructions to test Phalcon Incubator on Ubuntu 14+
If you cannot run the tests, please check the file `.travis.yml` for an in depth view on how test Incubator.
Additional information regarding our testing environment can be found by looking at the `tests/_bootstrap.php` file.

<hr>
Please report any issue if you find out bugs or memory leaks.<br>Thanks!

Phalcon Framework Team<br>2017
