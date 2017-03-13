#!/usr/bin/env bash

COMMAND=$1 # Composer command to execute. e.g. "install"

pushd "$( dirname "${BASH_SOURCE[0]}" )/.." > /dev/null

docker run \
	--rm \
	-v "$(pwd)/App:/app" \
	--workdir /app \
	composer/composer \
	${COMMAND} --no-interaction

popd > /dev/null
