#!/usr/bin/env bash

SCRIPTDIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

cd "$SCRIPTDIR"

PROJECT_ID=$(cat ./.projectID)

# Create an empty composer cache folder if it doesn't exist, so we can mount it to php-fpm
COMPOSER_CACHE=${SCRIPTDIR}/composer-cache
if [ ! -d ${COMPOSER_CACHE} ]; then
    mkdir ${COMPOSER_CACHE}
fi;

echo "Starting docker-compose project: ${PROJECT_ID}"


if [[ "$OSTYPE" == "darwin"* ]]; then
	DC_COMMAND="docker-compose"
elif [[ $(which docker.exe) ]]; then
	DC_COMMAND="docker-compose.exe"
else
	DC_COMMAND="docker-compose"
fi;

# Create a composer-config.json file that mirrors the format of .composer/auth.json, so we can mount it to php-fpm
# Also check if Travis CI is running using the CI global, which gets set to true by Travis CI.
CONFIG_FILE=${SCRIPTDIR}/composer-config.json
if [ ! -f ${CONFIG_FILE} ] && [ "$CI" != true ]; then
    echo "We have detected that you have not setup a GitHub oAuth token. Please go to https://github.com/settings/tokens/new?scopes=repo&description=Square%20One and create one. Then enter it here and press [ENTER]: "
    read githubtoken
    touch ${CONFIG_FILE}
    printf '{ "github-oauth": { "github.com": "%s" } }\n' "$githubtoken" >> ${CONFIG_FILE}
fi

${DC_COMMAND} --project-name=${PROJECT_ID} up -d --force-recreate

# If this is running on Travis, pass our encrypted github token from .travis.yml (secure variable)
if [ "$CI" = true ]; then
    ${SCRIPTDIR}/composer.sh config -g github-oauth.github.com $CI_USER_TOKEN
fi

bash ${SCRIPTDIR}/composer.sh install