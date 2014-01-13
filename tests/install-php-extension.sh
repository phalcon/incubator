#!/usr/bin/env bash

git clone -q https://github.com/phalcon/cphalcon.git -b 1.2.4
cd cphalcon/build
sudo ./travis-install
echo "extension=phalcon.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
