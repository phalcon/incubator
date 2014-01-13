#!/usr/bin/env bash

git clone -q https://github.com/phalcon/cphalcon.git -b 1.2.4
cd cphalcon/ext; export CFLAGS="-g3 -O1 -fno-delete-null-pointer-checks -Wall"; phpize && ./configure --enable-phalcon && make -j4 && sudo make install && phpenv config-add ../unit-tests/ci/phalcon.ini
