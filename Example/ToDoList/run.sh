#!/usr/bin/env bash

pushd "$( dirname "${BASH_SOURCE[0]}" )" > /dev/null

./Script/composer.sh install

docker-compose up

popd > /dev/null
