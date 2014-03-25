#!/usr/bin/env bash

git clone -q --depth=1 https://github.com/phalcon/cphalcon.git -b 1.3.0
echo "\n" | pecl install memcache
phpenv config-add tests/php-config.ini
cd cphalcon/ext; export CFLAGS="-g3 -O1 -fno-delete-null-pointer-checks -Wall"; phpize && ./configure --enable-phalcon && make -j4 && sudo make install && phpenv config-add ../unit-tests/ci/phalcon.ini
