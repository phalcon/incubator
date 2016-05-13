#!/usr/bin/env bash
#
#  Phalcon Framework
#
#  Copyright (c) 2011-2016 Phalcon Team (http://www.phalconphp.com)
#
#  This source file is subject to the New BSD License that is bundled
#  with this package in the file LICENSE.txt.
#
#  If you did not receive a copy of the license and are unable to
#  obtain it through the world-wide-web, please send an email
#  to license@phalconphp.com so we can send you a copy immediately.
#
#  Authors: Serghei Iakovlev <serghei@phalconphp.com>

docker_bin="$(which docker.io 2> /dev/null || which docker 2> /dev/null)"

[ -z "${TRAVIS_PHP_VERSION}" ] && echo "Need to set TRAVIS_PHP_VERSION variable. Fox example: 'export TRAVIS_PHP_VERSION=7'" && exit 1;
[ -z "${PHALCON_SRC_PATH}" ] && echo "Need to set PHALCON_SRC_PATH variable. Fox example: 'export PHALCON_SRC_PATH=/home/user/src/phalcon'" && exit 1;
[ -z "${TEST_BT_HOST}" ] && TEST_BT_HOST="incubator_beanstalkd"
[ -z "${TRAVIS_BUILD_DIR}" ] && TRAVIS_BUILD_DIR=$(cd $(dirname "$1") && pwd -P)/$(basename "$1")

RUN_ARGS="$@"
shift

function zephir() {
    ${docker_bin} run -it --rm \
        --privileged=true \
        -e ZEND_DONT_UNLOAD_MODULES=1 \
        -v $(pwd):/zephir \
        phalconphp/zephir:${TRAVIS_PHP_VERSION} "/usr/local/bin/zephir $1"
}

if [ ! -f ${TRAVIS_BUILD_DIR}/tests/_ci/phalcon.so ]; then
    echo "Phalcon extension not loaded, compiling it..."

    cd ${PHALCON_SRC_PATH}

    zephir "fullclean"
    zephir "builddev"

    if [ ! -f $(pwd)/ext/modules/phalcon.so ]; then
        echo "Unable to compile Phalcon."
        exit 1;
    fi

    cp $(pwd)/ext/modules/phalcon.so ${TRAVIS_BUILD_DIR}/tests/_ci/phalcon.so

    cd ${TRAVIS_BUILD_DIR}
fi

if [ ! -f ${TRAVIS_BUILD_DIR}/tests/_ci/entrypoint.sh ]; then
    echo "Unable locate docker entry point."
    exit 1;
fi

chmod +x ${TRAVIS_BUILD_DIR}/tests/_ci/entrypoint.sh

if [ -z ${TRAVIS} ]; then ${docker_bin} restart ${TEST_BT_HOST}; fi

${docker_bin} run -it --rm \
  --entrypoint /entrypoint.sh \
  --privileged=true \
  --net=incubator_default \
  -e RUN_ARGS="${RUN_ARGS}" \
  -e TEST_BT_HOST="${TEST_BT_HOST}" \
  -e TRAVIS_PHP_VERSION="${TRAVIS_PHP_VERSION}" \
  --name test-incubator-${TRAVIS_PHP_VERSION} \
  -v ${TRAVIS_BUILD_DIR}/tests/_ci/entrypoint.sh:/entrypoint.sh \
  -v ${TRAVIS_BUILD_DIR}/vendor:/app/vendor \
  -v ${TRAVIS_BUILD_DIR}/codeception.yml:/app/codeception.yml \
  -v ${TRAVIS_BUILD_DIR}/tests:/app/tests \
  -v ${TRAVIS_BUILD_DIR}/Library:/app/Library \
  -v ${TRAVIS_BUILD_DIR}/vendor:/app/vendor \
  -v ${TRAVIS_BUILD_DIR}/tests/_ci/phalcon.so:/ext/phalcon.so \
  phalconphp/php:${TRAVIS_PHP_VERSION} bash
