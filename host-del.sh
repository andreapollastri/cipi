#!/usr/bin/env bash

USER_NAME=
DBROOT=$(for word in $(cat /cipi/DBR); do echo $word; done)

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

#LINUX USER
userdel -r $USER_NAME

#MYSQL USER AND DB
/usr/bin/mysql -u root -p$DBROOT <<EOF
DROP DATABASE $USER_NAME;
DROP USER '$USER_NAME'@'localhost';
EOF
unlink /cipi/$USER_NAME

#SSL CERTIFICATE
unlink /cipi/certbot_renew_$USER_NAME.sh
unlink /etc/cron.d/certbot_renew_$USER_NAME.crontab
crontab -u $USER_NAME -r

#APACHE
a2dissite $USER_NAME.conf

#RESTART
service apache2 reload

echo "###################################################################################"
echo "                               DELETE COMPLETE "
echo "###################################################################################"
echo ""
