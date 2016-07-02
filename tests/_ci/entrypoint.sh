#!/usr/bin/env bash
#
#  Phalcon Framework
#
#  Copyright (c) 2016 Phalcon Team (http://www.phalconphp.com)
#
#  This source file is subject to the New BSD License that is bundled
#  with this package in the file LICENSE.txt.
#
#  If you did not receive a copy of the license and are unable to
#  obtain it through the world-wide-web, please send an email
#  to license@phalconphp.com so we can send you a copy immediately.
#
#  Authors: Serghei Iakovlev <serghei@phalconphp.com>

PURPLE="\e[0;35m"
GREEN="\033[0;32m"
YELLOW="\e[1;33m"
NC="\033[0m"

echo -e "\nWelcome to the Docker testing container."

PHP_EXTENSION_DIR=`php-config --extension-dir`
echo -e "PHP extension path: ${PURPLE}${PHP_EXTENSION_DIR}${NC}\n"

ln -sf /ext/phalcon.so ${PHP_EXTENSION_DIR}/phalcon.so

rm -f /etc/php/${TRAVIS_PHP_VERSION}/cli/conf.d/50-phalcon.ini
ln -s /app/tests/_ci/phalcon.ini /etc/php/${TRAVIS_PHP_VERSION}/cli/conf.d/50-phalcon.ini

PHP_FULL_VERSION=`php -r 'echo phpversion();'`
PHALCON_VERSION=`php --ri phalcon | grep "Version =" | awk '{print $3}'`

echo -e "${GREEN}PHP${NC}         version ${YELLOW}${TRAVIS_PHP_VERSION}${NC} (${PHP_FULL_VERSION})"
echo -e "${GREEN}Phalcon${NC}     version ${YELLOW}${PHALCON_VERSION}${NC}"
/app/vendor/bin/codecept --version

/app/vendor/bin/codecept build &> /dev/null

echo -e ""
/app/vendor/bin/phpcs --standard=PSR2 --colors --extensions=php --encoding=utf-8 Library/
result_phpcs=$?

/app/vendor/bin/codecept run "${RUN_ARGS}"
result_codecept=$?

if [ ${result_codecept} -ne 0 -o ${result_phpcs} -ne 0 ];
then
   exit 1;
fi

exit 0

