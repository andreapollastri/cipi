#!/usr/bin/env bash

while [ -n "$1" ] ; do
    case $1 in
    -d | --domain )
            shift
            DOMAIN=$1
            ;;
    -a |  --appcode )
            shift
            APPCODE=$1
            ;;
    -r |  --remote )
            shift
            REMOTE=$1
            ;;
    * )
            echo "ERROR: Unknown option: $1"
            exit -1
            ;;
    esac
    shift
done


NGINX=/etc/nginx/sites-available/$DOMAIN.conf
wget $REMOTE/sh/ag/$APPCODE/$DOMAIN -O $NGINX
sudo dos2unix $NGINX
sudo ln -s $NGINX /etc/nginx/sites-enabled/$DOMAIN.conf
sudo systemctl restart nginx.service


clear
echo "###CIPI###Ok"
