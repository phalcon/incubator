# Phalcon Incubator Testing

Thanks for reading this page, [test](tests) folder includes all the unit tests
we used to be sure that Phalcon Incubator will run properly and have a stable state.

The main dependency is [Codeception][1] which can be installed using Composer:

```sh
# run this command from project root
$ composer install --dev --prefer-source
```

A MySQL database is also required for several tests. Follow these instructions to create the database:

```sh
$ echo 'create database incubator_tests charset=utf8mb4 collate=utf8mb4_unicode_ci;' | mysql -u root
cat tests/_data/dump.sql | mysql incubator_tests -u root
```

Then you need to re-generate base classes for all suites:

```sh
$ vendor/bin/codecept build
```

You can execute all test with `run` command:

```sh
$ vendor/bin/codecept run
# OR
$ vendor/bin/codecept run --debug # Detailed output
```

For these tests we use the user `root` without a password. You may need to change this in `codeception.yml` file.

Obviously, Beanstalk-tests use Beanstalk, Memcached-tests use Memcached, Aerospike-tests use Aerospike, etc.

We use the following settings of these services:

**Beanstalk**
+ Host: `127.0.0.1`
+ Port: `11300`

**Memcached**
+ Host: `127.0.0.1`
+ Port: `11211`

**Aerospike**
+ Host: `127.0.0.1`
+ Port: `3000`

**Database** (MySQL)
+ Host: `127.0.0.1`
+ Port: `3306`
+ Username: `root`
+ Password: `''` _(empty string)_
+ DB Name: `incubator_tests`
+ Charset: `utf8`

You can change the connection settings of these services **before** running tests
by using [environment variables][4]:
```sh
# Beanstalk
export TEST_BT_HOST="127.0.0.1"
export TEST_BT_PORT="11300"

# Memcached
export TEST_MC_HOST="127.0.0.1"
export TEST_MC_PORT="11211"

# Aerospike
export TEST_AS_HOST="127.0.0.1"
export TEST_AS_PORT="3000"

# Database
export TEST_DB_HOST="127.0.0.1"
export TEST_DB_PORT="3306"
export TEST_DB_USER="root"
export TEST_DB_PASSWD=""
export TEST_DB_NAME="incubator_tests"
export TEST_DB_CHARSET="urf8"
```

Execute test groups with `run -g <group_name>` command.

Available groups:
* `Acl`
* `aerospike`
* `Annotation`
* `Avatar`
* `db`
* `Beanstalk`
* `Cache`
* `config`
* `DbValidation`
* `EagerLoading`
* `Http`
* `Loader`
* `MetaData`
* `Paginator`
* `Session`
* `utils`
* `Validation`

Read more about the installation and configuration of Codeception:
* [Codeception Introduction][2]
* [Codeception Console Commands][3]

Additionally, the file `.travis.yml` contains full instructions to test Phalcon Incubator on Ubuntu 12+
If you cannot run the tests, please refer to the `.travis.yml` file for more instructions how we test Incubator.
For detailed information on our testing environment setting refer to `tests/_bootstrap.php` file.

## The testing process

Incubator is built under [Travis CI][5] service.
Every commit pushed to this repository will queue a build into the continuous integration service and will run all tests
to ensure that everything is going well and the project is stable.

[1]: http://codeception.com/
[2]: http://codeception.com/docs/01-Introduction
[3]: http://codeception.com/docs/reference/Commands
[4]: https://wiki.archlinux.org/index.php/Environment_variables
[5]: https://travis-ci.org/
