#!/usr/bin/env bash
#
# This file is part of the Phalcon Framework.
#
# (c) Phalcon Team <team@phalcon.io>
#
# For the full copyright and license information, please view the
# LICENSE.txt file that was distributed with this source code.

git clone --depth=1 -v https://github.com/phalcon/cphalcon.git -b ${PHALCON_VERSION:=master} /tmp/phalcon
cd /tmp/phalcon/build
./install --phpize $(phpenv which phpize) --php-config $(phpenv which php-config)
