#!/usr/bin/env bash

git clone -q https://github.com/phalcon/cphalcon.git -b 1.2.4
cd cphalcon/build
sudo ./travis-install
sudo make install && phpenv config-add ../unit-tests/ci/phalcon.ini
