#!/usr/bin/env bash

SCRIPTDIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
cd "${SCRIPTDIR}";

# check for .env file
if [ ! -f "${SCRIPTDIR}/.env" ]; then
    echo "Could not find .env file. Copy .env.sample to .env and edit the file for further instructions"
    exit 1
fi;

source ".env"

# wp engine specific environments
if [ "${TARGET_HOST}" = "wpengine" ]; then
    ansible-vault --vault-password-file=.vaultpass decrypt .${TARGET_HOST}/config/staging.cfg.vaulted --output=.${TARGET_HOST}/config/staging.cfg
    ansible-vault --vault-password-file=.vaultpass decrypt .${TARGET_HOST}/config/production.cfg.vaulted --output=.${TARGET_HOST}/config/production.cfg
fi

# pantheon specific environments
if [ "${TARGET_HOST}" = "pantheon" ]; then
    ansible-vault --vault-password-file=.vaultpass decrypt .${TARGET_HOST}/config/dev.cfg.vaulted --output=.${TARGET_HOST}/config/dev.cfg
fi

# common on all hosts
ansible-vault --vault-password-file=.vaultpass decrypt .${TARGET_HOST}/config/common.cfg.vaulted --output=.${TARGET_HOST}/config/common.cfg
ansible-vault --vault-password-file=.vaultpass decrypt .${TARGET_HOST}/ansible_rsa.vaulted --output=.${TARGET_HOST}/ansible_rsa
ansible-vault --vault-password-file=.vaultpass decrypt .${TARGET_HOST}/ansible_rsa_password.vaulted --output=.${TARGET_HOST}/ansible_rsa_password
