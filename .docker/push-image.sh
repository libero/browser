#!/bin/bash
set -e

# should be executed only if $TRAVIS_BRANCH = master
docker login --username $DOCKER_USERNAME --password $DOCKER_PASSWORD
# tag temporarily as liberoadmin
docker tag libero/browser:$IMAGE_TAG liberoadmin/browser:$IMAGE_TAG
docker push liberoadmin/browser:$IMAGE_TAG
