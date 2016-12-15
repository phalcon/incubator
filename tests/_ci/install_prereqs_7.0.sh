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

CFLAGS="-O2 -g3 -fno-strict-aliasing -std=gnu90";

pecl channel-update pecl.php.net

install_apcu() {
	# See https://github.com/krakjoe/apcu/issues/203
	git clone -q https://github.com/krakjoe/apcu -b v5.1.7 /tmp/apcu
	cd /tmp/apcu

	phpize &> /dev/null
	./configure &> /dev/null

	make --silent -j4 &> /dev/null
	make --silent install
}

install_apcu_bc() {
	git clone -q https://github.com/krakjoe/apcu-bc /tmp/apcu-bc
	cd /tmp/apcu-bc

	phpize &> /dev/null
	./configure &> /dev/null

	make --silent -j4 &> /dev/null
	make --silent install
}

install_apcu
install_apcu_bc

phpenv config-add "${TRAVIS_BUILD_DIR}/tests/_ci/apc_bc.ini"

printf "\n" | pecl install yaml-2.0.0 >/dev/null 2>&1

phpenv config-add ${TRAVIS_BUILD_DIR}/tests/_ci/phalcon.ini
phpenv config-add ${TRAVIS_BUILD_DIR}/tests/_ci/redis.ini
phpenv config-add ${TRAVIS_BUILD_DIR}/tests/_ci/mongodb.ini
phpenv config-add ${TRAVIS_BUILD_DIR}/tests/_ci/memcached.ini
