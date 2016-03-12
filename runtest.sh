#!/usr/bin/env bash

docker run -it --rm --net=incubator_default -v $(pwd):/www klay/php-cli vendor/bin/phpcs --standard=PSR2 --colors --extensions=php --encoding=utf-8 Library/

docker run -it --rm --net=incubator_default -v $(pwd):/www klay/php-cli vendor/bin/codecept build

docker run -it --rm --net=incubator_default -v $(pwd):/www klay/php-cli vendor/bin/codecept run
