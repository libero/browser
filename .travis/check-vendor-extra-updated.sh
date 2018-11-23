#!/bin/bash
set -e

output=$(docker-compose --file docker-compose.yaml --file docker-compose.test.yaml run app composer update libero/content-page-bundle --with-dependencies --dry-run --no-ansi)
echo "${output}"

[[ ${output} =~ "Nothing to install or update" ]]
