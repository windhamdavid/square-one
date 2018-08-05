#!/usr/bin/env bash

# config
SCRIPTDIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
ENVIRONMENT=$1; shift
FORCEYES=false
BRANCH=server/${ENVIRONMENT}

cd "${SCRIPTDIR}";

# check for .env file
if [ ! -f "${SCRIPTDIR}/.env" ]; then
    echo "Could not find .env file. Copy .env.sample to .env and edit the file for further instructions"
    exit 1
fi;

source ".env"

# composer check
if [ ! -e "${COMPOSER_BIN}" ]; then
    echo "Could not find composer binary. Update the COMPOSER_BIN path in your .env file"
    exit 1
fi

# check for the proper PHP binary
if [[ $(${PHP_BIN} --version | grep ${PHP_PROD_VERSION}) ]]; then
    echo "Found PHP ${PHP_PROD_VERSION}..."
else
    echo "Your PHP version does not match ${PHP_PROD_VERSION} supplied in your .env file. Update your .env file to point to a PHP ${PHP_PROD_VERSION} binary."
    exit 1
fi

while getopts "b:y" opt; do
    case "$opt" in
        b)
            BRANCH=$OPTARG
            ;;
        y)
            FORCEEYES=true
            ;;
    esac
done

if [ ! -f ".${TARGET_HOST}/config/common.cfg" ]; then
    echo "common.cfg not found for ${TARGET_HOST}"
    exit 1
fi

# all hosts will have a common.cfg
source ".${TARGET_HOST}/config/common.cfg"

# only wp engine will let us deploy to different environments
if [ "${WP_ENGINE}" = 1 ]; then
    if [ ! -f ".${TARGET_HOST}/config/${ENVIRONMENT}.cfg" ]; then
        echo "Unknown environment for ${TARGET_HOST}: ${ENVIRONMENT}"
        exit 1
    fi
    source ".${TARGET_HOST}/config/${ENVIRONMENT}.cfg"
fi

DEPLOY_TIMESTAMP=`date +%Y%m%d%H%M%S`

echo "Preparing to deploy ${BRANCH} to ${ENVIRONMENT} on ${TARGET_HOST}"

if [ -d .deploy/src ]; then
    cd .deploy/src
    ${PHP_BIN} ${COMPOSER_BIN} install
    if [ -f .gitmodules ]; then
        git submodule foreach git reset --hard
        git submodule foreach git fetch --tags
        git submodule update
    fi
    cd ../..
else
    git clone $dev_repo .deploy/src
fi

cd .deploy/src
git reset --hard HEAD
git checkout ${BRANCH}
git pull origin ${BRANCH}
${PHP_BIN} ${COMPOSER_BIN} install
git submodule update --init --recursive
commit_hash=$(git rev-parse HEAD)

echo "Building front-end assets... this could take a while"

yarn install
grunt build dist
cd ../..

if [ -d .deploy/build ]; then
    cd .deploy/build
    ${PHP_BIN} ${COMPOSER_BIN} install
    if [ -f .gitmodules ]; then
        git submodule foreach git reset --hard
        git submodule foreach git fetch --tags
        git submodule update
    fi
    cd ../..
else
    git clone ${deploy_repo} .deploy/build
fi

if [ "${WP_ENGINE}" = 1 ]; then
    GIT_SSH_COMMAND="ssh -i .wpengine/ansible_rsa -F /dev/null"
fi

cd .deploy/build

if [ ! -d .git ]; then
    echo "Build directory is not a git repository. Aborting..."
    exit 1
fi

if git config "remote.${ENVIRONMENT}.url" > /dev/null; then
    git remote set-url ${ENVIRONMENT} ${deploy_repo}
else
    git remote add ${ENVIRONMENT} ${deploy_repo}
fi

git config core.autocrlf false
git fetch ${ENVIRONMENT}
git checkout master
git reset --hard ${ENVIRONMENT}/master

cd ../..

echo "syncing WordPress core files"

rsync -rp --delete .deploy/src/wp/ .deploy/build \
    --exclude=.git \
    --exclude=.gitmodules \
    --exclude=.gitignore \
    --exclude=.htaccess \
    --exclude=wp-config.php \
    --exclude=pantheon.yml \
    --exclude=wp-content

echo "syncing wp-content dir"

rsync -rp --delete .deploy/src/wp-content .deploy/build \
    --exclude=.git \
    --exclude=.gitmodules \
    --exclude=.gitignore \
    --exclude=.htaccess \
    --exclude=.babelrc \
    --exclude=.editorconfig \
    --exclude=.eslintrc \
    --exclude=dev \
    --exclude=dev_components \
    --exclude=docs \
    --exclude=grunt_options \
    --exclude=node_modules

echo "syncing configuration files"
# not wp-config.php. The target host generally manages that
rsync -rp .deploy/src/ .deploy/build \
    --include=local-config-sample.php \
    --include=general-config.php \
    --include=build-process.php \
    --include=.wpengine.htaccess \
    --include=vendor/*** \
    --include=pantheon.yml \
    --include=pantheon.upstream.yml \
    --include=README.md \
    --exclude=*

cd .deploy/build

# enable object caching
if [ "${DEPLOY_OBJECT_CACHE}" = 1 ]; then
    cp wp-content/object-cache-sample.php wp-content/object-cache.php
fi

git add -Av

if [ ${FORCEYES} == false ]; then
    read -p "Ready to deploy ${BRANCH} to ${ENVIRONMENT}. Have you made a backup? [Y/n] " yn
    case $yn in
        [Yy]* ) ;;
        [Nn]* ) exit;;
        * ) exit;;
    esac
fi

git commit --allow-empty -m "Deployment ${DEPLOY_TIMESTAMP}"
echo "pushing ${BRANCH} to ${TARGET_HOST}"
git push ${ENVIRONMENT} master

if [ -z "$slackchannel" ] || [ -z "$slacktoken" ]; then
    echo "Skipping slack notification"
else
    curl -F channel="$slackchannel" -F token="$slacktoken" -F text="Finished deploying \`${BRANCH}\` to ${TARGET_HOST_READABLE}" -F username="Deployment Bot" -F link_names=1 https://slack.com/api/chat.postMessage
    echo
fi

echo "done"
