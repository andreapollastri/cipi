#!/usr/bin/env bash

DOMAIN=

while [ -n "$1" ] ; do
    case $1 in
    -d | --domain )
        shift
        DOMAIN=$1
        ;;
    * )
        echo "ERROR: Unknown option: $1"
        exit -1
        ;;
    esac
    shift
done


sudo certbot --nginx -d $DOMAIN --non-interactive --agree-tos --register-unsafely-without-email
sudo systemctl restart nginx.service


clear
echo "###CIPI###Ok"
