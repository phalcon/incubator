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
PHP_CONFIG_OUTPUT=$(php-config)
DOWNLOAD_PHP_UNIT=

mkdir -p /tmp/aerospike-ext

sudo mkdir -p /usr/local/aerospike/{lua,usr-lua}
sudo chmod -R ugoa+rwx /usr/local/aerospike

ln -sf /usr/lib/x86_64-linux-gnu/libcrypto.so /usr/local/lib/libcrypto.so
ln -sf /usr/lib/x86_64-linux-gnu/libcrypto.a /usr/local/lib/libcrypto.a

cd /tmp/aerospike-ext
wget -O aerospike.zip https://github.com/aerospike/aerospike-client-php/archive/master.zip
unzip aerospike.zip -x "aerospike-client-php-master/doc/*" -x "aerospike-client-php-master/examples/*" -x "aerospike-client-php-master/src/aerospike/tests/*"

mkdir -p aerospike-client-php-master/src/aerospike/tests/
cd aerospike-client-php-master/src/aerospike

./build.sh --loglevel OFF > /dev/null 2>&1
make --silent install

find . -type f -name aerospike.so | xargs sudo cp -t $(php-config --extension-dir)
phpenv config-add ${TRAVIS_BUILD_DIR}/tests/_ci/aerospike.ini

cd ${TRAVIS_BUILD_DIR}
