#!/usr/bin/env bash

mkdir -p ~/.ssh
cat /run/secrets/host_ssh_key >> /root/.ssh/id_rsa
chmod 0600 /root/.ssh/id_rsa
eval $(ssh-agent -s)
ssh-add /root/.ssh/id_rsa

git config --global user.name "${GIT_COMMIT_NAME}"
git config --global user.email ${GIT_COMMIT_EMAIL}

#>Genera las claves para oauth
mkdir /var/www/html/var/oauth
openssl genrsa -out /var/www/html/var/oauth/private.key 2048
openssl rsa -in /var/www/html/var/oauth/private.key -pubout -out /var/www/html/var/oauth/public.key
chown -R www-data:www-data /var/www/html/var/oauth/public.key /var/www/html/var/oauth/private.key
#<Genera las claves para oauth

exec "$@"
