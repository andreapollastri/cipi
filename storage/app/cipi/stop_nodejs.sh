#!/bin/bash

while [ -n "$1" ] ; do
    case $1 in
    -u | --user* )
        shift
        USER_NAME=$1
        ;;
    * )
        echo "ERROR: Unknown option: $1"
        exit -1
        ;;
    esac
    shift
done


cd /home/$USER_NAME/web

pm2 delete ecosystem.config.js
rm ecosystem.config.js
