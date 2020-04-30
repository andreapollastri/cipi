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

#SSL CERTIFICATE
sudo certbot --nginx -d $DOMAIN --non-interactive --agree-tos --register-unsafely-without-email
sudo systemctl restart nginx.service

#RESUME
clear
echo "###CIPI###Ok"
