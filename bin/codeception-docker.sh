#!/usr/bin/env bash
docker run -v `pwd`:/var/www -it --net phalcon-incubator_default registry.gitlab.com/ruudboon/hub/phalcon-incubator:latest bash
