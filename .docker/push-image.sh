#!/bin/bash
set -e

if [[ "$TRAVIS_BRANCH" = "master" && "$TRAVIS_PULL_REQUEST" = "false" ]]; then
    echo "$DOCKER_PASSWORD" | docker login --username "$DOCKER_USERNAME" --password-stdin 

    # tag temporarily as liberoadmin due to lack of `libero/` availability
    docker tag "libero/browser:$IMAGE_TAG" "liberoadmin/browser:$IMAGE_TAG"
    docker push "liberoadmin/browser:$IMAGE_TAG"
fi
