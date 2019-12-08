#!/usr/bin/env bash

USER_NAME=
DBROOT=???

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
sudo userdel -r $USER_NAME

#MYSQL USER AND DB
/usr/bin/mysql -u root -p$DBROOT <<EOF
DROP DATABASE $USER_NAME;
DROP USER '$USER_NAME'@'localhost';
EOF
sudo unlink /cipi/$USER_NAME

#SSL CERTIFICATE
sudo unlink /cipi/certbot_renew_$USER_NAME.sh
sudo unlink /etc/cron.d/certbot_renew_$USER_NAME.crontab
sudo crontab -u $USER_NAME -r

#APACHE
sudo a2dissite $USER_NAME.conf
sudo a2dissite $USER_NAME-le-ssl.conf
sudo unlink /etc/apache2/sites-available/$USER_NAME.conf
sudo unlink /etc/apache2/sites-available/$USER_NAME-le-ssl.conf

#RESTART
sudo service apache2 reload
sudo systemctl reload apache2

clear
echo "###CIPI###Ok"
