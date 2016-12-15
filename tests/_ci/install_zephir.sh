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

# trace ERR through pipes
set -o pipefail

# trace ERR through 'time command' and other functions
set -o errtrace

# set -u : exit the script if you try to use an uninitialised variable
set -o nounset

# set -e : exit the script if any statement returns a non-true return value
set -o errexit

CURRENT_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
TRAVIS_BUILD_DIR="${TRAVIS_BUILD_DIR:-$(dirname $(dirname $CURRENT_DIR))}"
ZEPHIRDIR=${TRAVIS_BUILD_DIR}/vendor/phalcon/zephir

if [ ! -d "${ZEPHIRDIR}" ]; then
  echo -e "The ${ZEPHIRDIR} directory does not exists. First run 'composer install --dev'"
  exit 1;
fi

cd ${ZEPHIRDIR}

sed "s#%ZEPHIRDIR%#$ZEPHIRDIR#g" bin/zephir > bin/zephir-cmd
chmod 755 bin/zephir-cmd

mkdir -p ~/bin

cp bin/zephir-cmd ~/bin/zephir
rm bin/zephir-cmd

cd ${TRAVIS_BUILD_DIR}
