#!/bin/bash
set -e

function finish {
    docker-compose --file docker-compose.yaml --file docker-compose.test.yaml down --volumes
}

trap finish EXIT

packages=("libero/content-page-bundle" "libero/patterns-bundle" "libero/views")

output=$(docker-compose --file docker-compose.yaml --file docker-compose.test.yaml run app composer update --dry-run --no-ansi)
echo "${output}"

for package in ${packages[@]};
do
    [[ ${output} != *"Updating ${package}"* ]]
done
