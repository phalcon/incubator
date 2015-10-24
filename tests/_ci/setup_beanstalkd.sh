#!/usr/bin/env bash

set -eufo pipefail

BEANSTALKD_VERSION="1.10"
src_url="https://github.com/kr/beanstalkd/archive/v${BEANSTALKD_VERSION}.tar.gz"

mkdir -p "$HOME/.local/bin"

curl -L "$src_url" | tar xz
pushd "beanstalkd-${BEANSTALKD_VERSION}"
    make
    mv beanstalkd "$HOME/.local/bin"
popd
rm -rf "beanstalkd-${BEANSTALKD_VERSION}"
