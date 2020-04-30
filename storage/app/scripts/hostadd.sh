#!/usr/bin/env bash

REMOTE=???
DBROOT=???

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
    -a |  --appcode )
        shift
        APPCODE=$1
        ;;
    * )
        echo "ERROR: Unknown option: $1"
        exit -1
        ;;
    esac
    shift
done

#CREATE USER
sudo useradd -m -s $USER_SHELL -d /home/$USER_NAME -G www-data $USER_NAME
echo "$USER_NAME:$PASSWORD"|chpasswd
sudo chmod o-r /home/$USER_NAME

mkdir /home/$USER_NAME/web
mkdir /home/$USER_NAME/nginx
mkdir /home/$USER_NAME/nginx/log

#WELCOME PAGE
if [ $BASE_PATH != "" ]; then
    mkdir /home/$USER_NAME/web/$BASE_PATH
    WELCOME=/home/$USER_NAME/web/$BASE_PATH/index.php
else
    WELCOME=/home/$USER_NAME/web/index.php
fi
sudo touch $WELCOME
sudo cat > "$WELCOME" <<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Coming soon...</title>
        <style type="text/css">
            body {
                text-align: center;
                background: #f0f0f0;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 48px;
                font-weight: bold;
            }
            h2.c1 {
                margin-top: 60px;
                color: #444;
                font-size: 32px;
                font-weight: lighter;
            }
        </style>
    </head>
    <body>
        <h2 class="c1">
            Coming soon...
        </h2>
    </body>
</html>
EOF

#VIRTUAL HOST
NGINX=/etc/nginx/sites-available/$USER_NAME.conf
wget $REMOTE/sh/hg/$APPCODE/ -O $NGINX
sudo dos2unix $NGINX
CUSTOM=/home/$USER_NAME/nginx/custom.conf
wget $REMOTE/sh/nx/ -O $CUSTOM
sudo dos2unix $CUSTOM
sudo ln -s $NGINX /etc/nginx/sites-enabled/$USER_NAME.conf
sudo chown -R www-data: /home/$USER_NAME
sudo systemctl restart nginx.service

#MYSQL
DBNAME=$USER_NAME
DBUSER=$USER_NAME
/usr/bin/mysql -u root -p$DBROOT <<EOF
CREATE DATABASE IF NOT EXISTS $DBNAME;
CREATE USER $DBUSER@'localhost' IDENTIFIED BY '$DBPASS';
GRANT USAGE ON *.* TO '$DBUSER'@'localhost' IDENTIFIED BY '$DBPASS' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;
GRANT ALL PRIVILEGES ON $DBNAME.* TO $DBUSER@'localhost';
EOF

#GIT
sudo mkdir /home/$USER_NAME/git/
sudo cp /cipi/github /home/$USER_NAME/git/deploy
sudo cp /cipi/github.pub /home/$USER_NAME/git/deploy.pub
sudo cp /cipi/deploy.sh /home/$USER_NAME/git/deploy.sh
sudo rpl -q "###CIPI-USER###" "$USER_NAME" /home/$USER_NAME/git/deploy.sh

#PERMISSIONS
sudo chown -R $USER_NAME:$USER_NAME /home/$USER_NAME/git/
sudo chown -R $USER_NAME:$USER_NAME /home/$USER_NAME/web/
sudo chown -R $USER_NAME:$USER_NAME /home/$USER_NAME

#RESUME
clear
echo "###CIPI###Ok"
