# Deploy Instructions

## Copy and edit .env.sample file


```
cp .env.sample .env
```

Edit your .env file and ensure your local paths to the PHP and composer binaries will match the version on the server
you're deploying to. E.g. if you run `composer install` with PHP 7.2 and the server is running PHP 7.0, you'll get
errors once deployed.

## Decrypting necessary files

We're not using Ansible for the deploy, but using vault for some files.  Create your `.vaultpass` file. You can find the vault key in [1password.
](https://my.1password.com/vaults/evhlch44byup67f3cli36xdb3m/allitems/pre46e747lan7hlfm32mdu336u)
```
echo "[ vault key ]" > .vaultpass
```

```
./decrypt.sh
```
# Wp Engine

## Prerequisites

1. [You'll need to setup your specific environments, e.g. staging](https://wpengine.com/support/staging-development-environments-wp-engine/)
2. [Add your SSH Key for git deployments](https://wpengine.com/support/set-git-push-user-portal/)

# Pantheon

## Prerequisites

1. [Enable git connection mode on Pantheon](https://pantheon.io/docs/guides/quickstart/connection-modes/) 
2. [Add your pub ssh key to Pantheon](https://pantheon.io/docs/ssh-keys/)

# Run a Deploy

```
./deploy-composer.sh [staging|production]
```

You can optionally specify a branch name (defaults to server/staging or server/production, as appropriate). E.g.:

```
./deploy-composer.sh staging -b sprint/1
```

You can also run this script forcing a "yes" answer to any questions:

```
./deploy-composer.sh staging -b sprint/1 -y
```

### Pantheon Specific Instructions
Note Pantheon doesn't allow code deploys to various environments so you're always deploying code to dev. test and live are code locked. Make sure you deploy to the `dev` branch.

```
./deploy-composer.sh dev
```

You can optionally specify a branch name. E.g.:

```
./deploy-composer.sh dev -b sprint/1
```

You can also run this script forcing a "yes" answer to any questions:

```
./deploy-composer.sh dev -b sprint/1 -y
```

## Setup Instructions


We're not using Ansible for the deploy, but using vault for some files. Create your .vaultpass file. 
You can find the vault key in [1password.
](https://my.1password.com/vaults/evhlch44byup67f3cli36xdb3m/allitems/pre46e747lan7hlfm32mdu336u)

```
echo "[ vault key ]" > .vaultpass
```

* Run `init.sh` to build the files for the project.
* Edit the production and staging config files in `.wpengine/config`.
* Run `encrypt.sh` to re-encrypt those files after you edit them.
* Give the ssh key from `.wpengine/ansible_rsa.pub` access to push to the WP Engine git repo.

## Notes

This script supports both composer and sub module based square-one installs
