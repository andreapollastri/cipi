#!/usr/bin/env bash

DOMAIN=

while [ -n "$1" ] ; do
    case $1 in
    -d | --domain )
        shift
        DOMAIN=$1
        ;;
    -c | --config )
        shift
        CONFIG=$1
        ;;
    * )
        echo "ERROR: Unknown option: $1"
        exit -1
        ;;
    esac
    shift
done

sudo certbot --nginx -d $DOMAIN --non-interactive --agree-tos --register-unsafely-without-email
sudo rpl -i -w "[::]:443 ssl" "[::]:443 ssl http2" /etc/nginx/sites-enabled/$CONFIG.conf
sudo rpl -i -w "443 ssl" "443 ssl http2" /etc/nginx/sites-enabled/$CONFIG.conf
sudo systemctl restart nginx.service


clear
echo "###CIPI###Ok"
