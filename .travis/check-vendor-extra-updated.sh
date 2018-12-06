#!/bin/bash
set -e

function finish {
    docker-compose --file docker-compose.yaml --file docker-compose.test.yaml down --volumes
}

trap finish EXIT

cd "$(dirname "$0")/.."

while IFS=$'\n' read -r line; do packages+=("$line"); done < <(< composer.lock jq --raw-output '.packages[] | select(.dist.url | startswith("./vendor-extra/")) .name')

output=$(docker-compose --file docker-compose.yaml --file docker-compose.test.yaml run app composer update --dry-run --no-ansi)
echo "${output}"

for package in "${packages[@]}";
do
    [[ ${output} != *"Updating ${package}"* ]]
done
