#!/usr/bin/env bash

BASE_PATH=
USER_SHELL=/bin/bash

while [ -n "$1" ] ; do
    case $1 in
    -u | --user )
        shift
        USER_NAME=$1
        ;;
    -p | --pass )
        shift
        PASSWORD=$1
        ;;
    -dbp | --dbpass )
        shift
        DBPASS=$1
        ;;
    -b |  --base )
        shift
        BASE_PATH=$1
        ;;
    -id |  --siteid )
        shift
        SITEID=$1
        ;;
    -php |  --php )
        shift
        PHP=$1
        ;;
    -dbr | --dbroot )
        shift
        DBROOT=$1
        ;;
    -r | --remote )
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

sudo useradd -m -s $USER_SHELL -d /home/$USER_NAME -G www-data $USER_NAME
echo "$USER_NAME:$PASSWORD"|chpasswd
sudo chmod o-r /home/$USER_NAME

mkdir /home/$USER_NAME/web
mkdir /home/$USER_NAME/log

NGINX=/etc/nginx/sites-available/$USER_NAME.conf
sudo wget $REMOTE/conf/host_nodejs/$SITEID -O $NGINX
sudo dos2unix $NGINX

sudo ln -s $NGINX /etc/nginx/sites-enabled/$USER_NAME.conf
sudo chown -R www-data: /home/$USER_NAME/web

sudo systemctl restart nginx.service

DBNAME=$USER_NAME
DBUSER=$USER_NAME
/usr/bin/mysql -u cipi -p$DBROOT <<EOF
CREATE DATABASE IF NOT EXISTS $DBNAME;
use mysql;
CREATE USER $DBUSER@'%' IDENTIFIED WITH mysql_native_password BY '$DBPASS';
GRANT ALL PRIVILEGES ON $DBNAME.* TO $DBUSER@'%' WITH GRANT OPTION;
FLUSH PRIVILEGES;
EOF

sudo mkdir /home/$USER_NAME/.cache
sudo mkdir /home/$USER_NAME/git
sudo cp /etc/cipi/github /home/$USER_NAME/git/deploy

sudo chown -R $USER_NAME:$USER_NAME /home/$USER_NAME/.cache
sudo chown -R $USER_NAME:$USER_NAME /home/$USER_NAME/git
sudo chown -R $USER_NAME:$USER_NAME /home/$USER_NAME/web
