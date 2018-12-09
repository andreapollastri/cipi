#!/usr/bin/env bash

ALIAS=
USER_NAME=

# Check if user is root
if [ $(id -u) != "0" ]; then
    echo "Error: You must be root to run this script."
    exit 1
fi

while [ -n "$1" ] ; do
            case $1 in
            -a | --alias )
                    shift
                    ALIAS=$1
                    ;;
            -u | --user )
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

#SSL CERTIFICATE
unlink /cipi/certbot_renew_$USER_NAME$ALIAS.sh
unlink /etc/cron.d/certbot_renew_$USER_NAME$ALIAS.crontab

#APACHE
sudo a2dissite $USER_NAME$ALIAS.conf

#RESTART
sudo service apache2 reload

echo "###################################################################################"
echo "                               DELETE COMPLETE "
echo "###################################################################################"
echo ""
