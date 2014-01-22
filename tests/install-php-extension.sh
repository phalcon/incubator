#!/bin/sh

git clone -q --depth=1 https://github.com/phalcon/cphalcon.git -b 1.3.0
(cd cphalcon/ext; phpize && ./configure --enable-phalcon && make -j4 && sudo make install && phpenv config-add ../unit-tests/ci/phalcon.ini)
