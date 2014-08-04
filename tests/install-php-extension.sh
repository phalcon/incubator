#!/usr/bin/env bash

git clone -q --depth=1 https://github.com/phalcon/cphalcon.git -b master
cd cphalcon/ext; export CFLAGS="-g3 -O1 -fno-delete-null-pointer-checks -Wall"; phpize && ./configure --enable-phalcon && make -j4 && sudo make install && phpenv config-add ../unit-tests/ci/phalcon.ini
