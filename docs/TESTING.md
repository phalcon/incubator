# Phalcon Incubator Testing

Thanks for reading this page, [test](tests) folder includes all the unit tests
we used to be sure that Phalcon Incubator will run properly and have a stable state.

For testing Incubator you don't need take care of any dependencies. You don't need to install any
additional software which is used in Incubator, compile Phalcon, install Zephir, etc.
You don't need keep in mind database credentials, all necessary extensions, versions, environment variables,
and so on any more.

You can easily test Incubator on any PC. Aiming to be on edge, we reworked the testing process and
decided to introduce only two dependencies, [Docker][1] and [Docker Compose][2], instead of a vast number of them.

All you need for testing is:

* [Docker][1] >= 1.10
* [Docker Compose][2] >= 1.6.2

Phalcon Incubator uses [Codeception][3] which can be installed using Composer:

```sh
# run this command from project root
$ composer install --dev --prefer-source
```

You have to export few variables:
```sh
export TRAVIS_PHP_VERSION=5.6 # PHP version you want to use
export PHALCON_SRC_PATH=/home/user/src/phalcon # Path to the cphalcon source
```

After you have installed all necessary dependencies use these two commands:

```sh
# Create and run containers in background
docker-compose -p incubator up -d

# run test
bash tests/build.sh

# run particular test
bash tests/build.sh tests/unit/Annotations/Adapter/RedisTest.php
```

In addition to the obvious advantage related to reduction in the number of dependencies, this approach allows you to
run tests immediately by using the service [Travis CI][4].

## The testing process

Incubator is built under [Travis CI][4] service.
Every commit pushed to this repository will queue a build into the continuous integration service and will run all tests
to ensure that everything is going well and the project is stable.

## MongoDB

Tests for the new MongoCollection include the functions `save()` `delete()` `find()` `findFirst()` `findById()` and `aggregate()` functions.

Tests still need to be written to incorporate the Validation, Behaviours and Events functionality.

[1]: https://docs.docker.com/
[2]: https://docs.docker.com/compose/
[3]: http://codeception.com/
[4]: https://travis-ci.org/
