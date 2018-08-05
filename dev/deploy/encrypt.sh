#!/usr/bin/env bash

SCRIPTDIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
cd "${SCRIPTDIR}";

# check for .env file
if [ ! -f "${SCRIPTDIR}/.env" ]; then
    echo "Could not find .env file. Copy .env.sample to .env and edit the file for further instructions"
    exit 1
fi;

source ".env"

ansible-vault --vault-password-file=.vaultpass encrypt .${TARGET_HOST}/config/common.cfg --output=.${TARGET_HOST}/config/common.cfg.vaulted

if [ "${WP_ENGINE}" = 1 ]; then
    ansible-vault --vault-password-file=.vaultpass encrypt .${TARGET_HOST}/config/staging.cfg --output=.${TARGET_HOST}/config/staging.cfg.vaulted
    ansible-vault --vault-password-file=.vaultpass encrypt .${TARGET_HOST}/config/production.cfg --output=.${TARGET_HOST}/config/production.cfg.vaulted
    ansible-vault --vault-password-file=.vaultpass encrypt .${TARGET_HOST}/ansible_rsa --output=.${TARGET_HOST}/ansible_rsa.vaulted
    ansible-vault --vault-password-file=.vaultpass encrypt .${TARGET_HOST}/ansible_rsa_password --output=.${TARGET_HOST}/ansible_rsa_password.vaulted
fi
