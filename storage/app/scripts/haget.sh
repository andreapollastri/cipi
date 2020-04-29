#!/usr/bin/env bash

USER_NAME=???
BASE_PATH=???
PHP_VERSION=???
DOMAIN=???

server {

    listen 80;
    listen [::]:80;

    server_tokens off;

    server_name $DOMAIN

    if [ $BASE_PATH != "" ]; then
        root /home/$USER_NAME/web/$BASE_PATH
    else
        root /home/$USER_NAME/web
    fi

    access_log /home/$USER_NAME/nginx/log/$DOMAIN.access.log;
    error_log /home/$USER_NAME/nginx/log/$DOMAIN.error.log;

    include /home/$USER_NAME/nginx/custom.conf;

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php$PHP_VERSION-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

}
