#!/usr/bin/env bash

DBROOT=???
IP=???

BASE_PATH=
USER_SHELL=/bin/bash

# Check if user is root
if [ $(id -u) != "0" ]; then
    echo "Error: You must be root to run this script."
    exit 1
fi

while [ -n "$1" ] ; do
            case $1 in
            -d | --domain )
                    shift
                    DOMAIN=$1
                    ;;
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
            -r |  --remote )
                    shift
                    REMOTE=$1
                    ;;
            -r |  --appcode )
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
isUserExits() {
    grep $1 /etc/passwd > /dev/null
    [ $? -eq 0 ] && return $TRUE || return $FALSE
}
if(!isUserExits $USER_NAME)
    then
        sudo useradd -m -s $USER_SHELL -d /home/$USER_NAME -G www-data $USER_NAME
        echo "$USER_NAME:$PASSWORD"|chpasswd
        sudo chmod o-r /home/$USER_NAME
    else
        echo "Error: Retry!"
        exit 1
fi

if [ $BASE_PATH != "" ]; then
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

#VIRTUALHOST
HOST="'wget -qO- http://$REMOTE/sh/hg/'"
NGINX=/etc/nginx/sites-available/$USER_NAME.conf
sudo touch $NGINX
sudo cat > "$NGINX" <<EOF
    $HOST
EOF
sudo dos2unix $NGINX
sudo ln -s $NGINX /etc/nginx/sites-enabled/
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

#RESUME
clear
echo "###CIPI###Ok"

#GIT
sudo mkdir /home/$USER_NAME/git/
sudo cp /cipi/github /home/$USER_NAME/git/deploy
sudo cp /cipi/github.pub /home/$USER_NAME/git/deploy.pub
sudo cp /cipi/deploy.sh /home/$USER_NAME/git/deploy.sh
sudo rpl -q "###CIPI-USER###" "$USER_NAME" /home/$USER_NAME/git/deploy.sh
sudo chown -R $USER_NAME:$USER_NAME /home/$USER_NAME/git/
sudo chown -R $USER_NAME:$USER_NAME /home/$USER_NAME/web/

#PERMISSIONS
chown -R $USER_NAME:$USER_NAME /home/$USER_NAME
