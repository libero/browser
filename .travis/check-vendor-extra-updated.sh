#!/bin/bash
set -e

function finish {
    docker-compose --file docker-compose.yaml --file docker-compose.test.yaml down --volumes
}

trap finish EXIT

cd "$(dirname "$0")/.."

while IFS=$'\n' read -r line; do packages+=("$line"); done < <(< composer.lock jq --raw-output '.packages[] | select(.dist.url | startswith("./vendor-extra/")) .name')

output=$(docker-compose --file docker-compose.yaml --file docker-compose.test.yaml run app composer update --dry-run --no-ansi)

failures=0
for package in "${packages[@]}";
do
    echo "Checking ${package}"
    if [[ ${output} == *"Updating ${package}"* ]]
    then
        echo "Needs to be updated"
        ((failures++))
    else
        echo "Up to date"
    fi
done

[[ ${failures} == 0 ]]
