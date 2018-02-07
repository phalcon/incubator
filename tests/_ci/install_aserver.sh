#!/usr/bin/env bash
#
#  Phalcon Framework
#
#  Copyright (c) 2011-2018 Phalcon Team (https://www.phalconphp.com)
#
#  This source file is subject to the New BSD License that is bundled
#  with this package in the file LICENSE.txt.
#
#  If you did not receive a copy of the license and are unable to
#  obtain it through the world-wide-web, please send an email
#  to license@phalconphp.com so we can send you a copy immediately.

CURRENT_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
TRAVIS_BUILD_DIR="${TRAVIS_BUILD_DIR:-$(dirname $(dirname $CURRENT_DIR))}"

wget -O aerospike.tgz "http://aerospike.com/download/server/latest/artifact/ubuntu12"
tar -xvf aerospike.tgz
cd aerospike-server-community-*-ubuntu12*

sudo ./asinstall > /dev/null 2>&1
sudo service aerospike start > /dev/null 2>&1 &

mv ${TRAVIS_BUILD_DIR}/tests/unit.suite.5.yml ${TRAVIS_BUILD_DIR}/tests/unit.suite.yml

cd ${TRAVIS_BUILD_DIR}
