#!/usr/bin/env bash

git clone https://github.com/phalcon/cphalcon.git -b 1.2.4
cd cphalcon/build
sudo ./install && phpenv config-add ../../tests/phalcon.ini
