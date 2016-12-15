#!/usr/bin/env bash
#
#  Phalcon Framework
#
#  Copyright (c) 2011-2016 Phalcon Team (https://www.phalconphp.com)
#
#  This source file is subject to the New BSD License that is bundled
#  with this package in the file LICENSE.txt.
#
#  If you did not receive a copy of the license and are unable to
#  obtain it through the world-wide-web, please send an email
#  to license@phalconphp.com so we can send you a copy immediately.

CURRENT_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
TRAVIS_BUILD_DIR="${TRAVIS_BUILD_DIR:-$(dirname $(dirname $CURRENT_DIR))}"

pecl channel-update pecl.php.net

printf "\n" | pecl install apcu-4.0.11 &> /dev/null

echo "apc.enable_cli=On" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini

printf "\n" | pecl install yaml > /dev/null 2>&1

phpenv config-add ${TRAVIS_BUILD_DIR}/tests/_ci/phalcon.ini
phpenv config-add ${TRAVIS_BUILD_DIR}/tests/_ci/redis.ini
phpenv config-add ${TRAVIS_BUILD_DIR}/tests/_ci/mongo.ini
phpenv config-add ${TRAVIS_BUILD_DIR}/tests/_ci/mongodb.ini
phpenv config-add ${TRAVIS_BUILD_DIR}/tests/_ci/memcached.ini
