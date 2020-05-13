#!/usr/bin/env bash

PHP=
USER_NAME=
DBROOT=???

while [ -n "$1" ] ; do
    case $1 in
    -u | --user* )
        shift
        USER_NAME=$1
        ;;
    -p | --php* )
        shift
        PHP=$1
        ;;
    * )
        echo "ERROR: Unknown option: $1"
        exit -1
        ;;
    esac
    shift
done

sudo rm /etc/php/$PHP/fpm/pool.d/$USER_NAME.conf
sudo service php$PHP-fpm restart

sudo userdel -r $USER_NAME
sudo rm -rf /home/$USER_NAME

/usr/bin/mysql -u root -p$DBROOT <<EOF
DROP DATABASE $USER_NAME;
DROP USER '$USER_NAME'@'localhost';
EOF


sudo unlink /etc/nginx/sites-enabled/$USER_NAME.conf
sudo unlink /etc/nginx/sites-available/$USER_NAME.conf
sudo systemctl restart nginx.service

clear
echo "###CIPI###Ok"
