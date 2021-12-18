#!/usr/bin/env bash

BASE_PATH=
USER_SHELL=/bin/rbash

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

if [ $BASE_PATH != "" ]; then
    mkdir /home/$USER_NAME/web/$BASE_PATH
    WELCOME=/home/$USER_NAME/web/$BASE_PATH/index.php
else
    WELCOME=/home/$USER_NAME/web/index.php
fi
sudo touch $WELCOME
sudo cat > "$WELCOME" <<EOF
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> <meta name="robots" content="noindex, nofollow"> <meta name="googlebot" content="noindex"> <title>Coming Soon</title> <style>html{font-family: Arial, sans-serif; color: #000; font-size: 16px; font-weight: 400;}main{margin: 3rem 0 0 3rem;}h1, p{margin-top: 0; margin-bottom: 1.8rem;}h1{font-weight: 700; font-size: 4.5rem;}p{color: #7d7d7d; font-size: 1.75rem;}@media screen and (max-width: 768px){html{font-size: 12px;}main{margin: 3rem 0 0 3rem;}}</style></head><body><main><h1>Coming Soon</h1><p><script>document.write(window.location.hostname);</script></p></main></body></html>
EOF

NGINX=/etc/nginx/sites-available/$USER_NAME.conf
sudo wget $REMOTE/conf/host/$SITEID -O $NGINX
sudo dos2unix $NGINX
POOL=/etc/php/$PHP/fpm/pool.d/$USER_NAME.conf
sudo wget $REMOTE/conf/php/$SITEID -O $POOL
sudo dos2unix $POOL
CUSTOM=/etc/nginx/cipi/$USER_NAME.conf
sudo wget $REMOTE/conf/nginx -O $CUSTOM
sudo dos2unix $CUSTOM
sudo ln -s $NGINX /etc/nginx/sites-enabled/$USER_NAME.conf
sudo chown -R www-data: /home/$USER_NAME/web
sudo service php$PHP-fpm restart
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