#!/usr/bin/env bash

SCRIPTDIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
cd "${SCRIPTDIR}";

# check for .env file
if [ ! -f "${SCRIPTDIR}/.env" ]; then
    echo "Could not find .env file. Copy .env.sample to .env and edit the file for further instructions"
    exit 1
fi;

source ".env"

echo "Creating configuration files"

cp .${TARGET_HOST}/config/common.cfg.sample .${TARGET_HOST}/config/common.cfg

# wp engine specific environments
if [ "${TARGET_HOST}" = "wpengine" ]; then
    cp .${TARGET_HOST}/config/staging.cfg.sample .${TARGET_HOST}/config/staging.cfg
    cp .${TARGET_HOST}/config/production.cfg.sample .${TARGET_HOST}/config/production.cfg
fi

# pantheon specific environments
if [ "${TARGET_HOST}" = "pantheon" ]; then
    cp .${TARGET_HOST}/config/dev.cfg.sample .${TARGET_HOST}/config/dev.cfg
fi

PASSWORD=$(LC_ALL=C tr -dc 'A-Za-z0-9!"#$%&'\''()*+,-./:;<=>?@[\]^_`{|}~' </dev/urandom | head -c 13)

echo $PASSWORD > .${TARGET_HOST}/ansible_rsa_password

ssh-keygen -t rsa -b 4096 -N "$PASSWORD" -f .${TARGET_HOST}/ansible_rsa -C "servers@tri.be"

echo "Encrypting files"
source encrypt.sh

echo "Setup complete. Edit the .cfg files in the .${TARGET_HOST}/config directory and run encrypt.sh."
