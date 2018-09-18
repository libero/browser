#!/bin/bash
set -e

body='{
    "request": {
        "branch":"master",
        "message":"Update Browser to '"$1"'"
    }
}'

curl -s -X POST \
   -H "Content-Type: application/json" \
   -H "Accept: application/json" \
   -H "Travis-API-Version: 3" \
   -H "Authorization: token $TRAVIS_TOKEN" \
   -d "$body" \
   https://api.travis-ci.com/repo/libero%2Fenvironments/requests
