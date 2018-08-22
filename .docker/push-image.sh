#!/bin/bash
set -e

if [ "$TRAVIS_BRANCH" = master ]; then
    docker login --username $DOCKER_USERNAME --password $DOCKER_PASSWORD

    # tag temporarily as liberoadmin due to lack of `libero/` availability
    docker tag libero/browser:$IMAGE_TAG liberoadmin/browser:$IMAGE_TAG
    docker push liberoadmin/browser:$IMAGE_TAG
fi
