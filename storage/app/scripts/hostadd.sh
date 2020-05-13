#!/usr/bin/env bash

DBROOT=???

BASE_PATH=
USER_SHELL=/bin/bash

VERSION=$(grep -oP '(?<=^VERSION_ID=).+' /etc/os-release | tr -d '"')

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
    -php |  --php )
        shift
        PHP=$1
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


sudo useradd -m -s $USER_SHELL -d /home/$USER_NAME -G www-data $USER_NAME
echo "$USER_NAME:$PASSWORD"|chpasswd
sudo chmod o-r /home/$USER_NAME

mkdir /home/$USER_NAME/web
mkdir /home/$USER_NAME/log
mkdir /home/$USER_NAME/bks
mkdir /home/$USER_NAME/bks/db
mkdir /home/$USER_NAME/bks/fs

DBBKS=/home/$USER_NAME/bks/backup-db.sh
sudo touch $DBBKS
sudo cat > "$DBBKS" <<EOF
#!/bin/bash

######### BACKUP CONFIGURATION #########
DBUSER=
DBPASS=
DAYS=7

######### DO NOT CHANGE ANYTHING IN THIS AREA #########
mysqldump -u$DBUSER -p$DBPASS $DBUSER > db/dump_$(date +"%Y_%m_%d_%I_%M_%p").sql
find db/ -type f -mtime +$DAYS -exec rm -f {} \;
EOF

FSBKS=/home/$USER_NAME/bks/backup-fs.sh
sudo touch $FSBKS
sudo cat > "$FSBKS" <<EOF
#!/bin/bash

######### BACKUP CONFIGURATION #########
DAYS=30

######### DO NOT CHANGE ANYTHING IN THIS AREA #########
tar -zcvf fs/web_$(date +"%Y_%m_%d_%I_%M_%p").tar.gz ../web
find fs/ -type f -mtime +$DAYS -exec rm -f {} \;
EOF


if [ $BASE_PATH != "" ]; then
    mkdir /home/$USER_NAME/web/$BASE_PATH
    WELCOME=/home/$USER_NAME/web/$BASE_PATH/index.php
else
    WELCOME=/home/$USER_NAME/web/index.php
fi
sudo touch $WELCOME
sudo cat > "$WELCOME" <<EOF
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Coming soon...</title>
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }
            .full-height {
                height: 100vh;
            }
            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }
            .position-ref {
                position: relative;
            }
            .content {
                text-align: center;
            }
            .title {
                font-size: 64px;
            }
            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }
            .m-b-md {
                margin-bottom: 30px;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <div class="content">
                <div class="title m-b-md">
                    Coming soon...
                </div>
                <div class="links">
                    <a href="https://cipi.sh">Powered by Cipi</a>
                </div>
            </div>
        </div>
    </body>
</html>
EOF

NGINX=/etc/nginx/sites-available/$USER_NAME.conf
sudo wget $REMOTE/sh/hg/$APPCODE/ -O $NGINX
sudo dos2unix $NGINX
POOL=/etc/php/$PHP/fpm/pool.d/$USER_NAME.conf
sudo wget $REMOTE/sh/pf/$APPCODE/ -O $POOL
sudo dos2unix $POOL
CUSTOM=/etc/nginx/cipi/$USER_NAME.conf
sudo wget $REMOTE/sh/nx/ -O $CUSTOM
sudo dos2unix $CUSTOM
sudo ln -s $NGINX /etc/nginx/sites-enabled/$USER_NAME.conf
sudo chown -R www-data: /home/$USER_NAME/web
sudo service php$PHP-fpm restart
sudo systemctl restart nginx.service


if [ "$VERSION" = "20.04" ]; then

DBNAME=$USER_NAME
DBUSER=$USER_NAME
/usr/bin/mysql -u cipi -p$DBROOT <<EOF
CREATE DATABASE IF NOT EXISTS $DBNAME;
use mysql;
CREATE USER $DBUSER@'%' IDENTIFIED BY '$DBPASS';
GRANT ALL PRIVILEGES ON $DBNAME.* TO $DBUSER@'%' WITH GRANT OPTION;
FLUSH PRIVILEGES;
EOF

else

DBNAME=$USER_NAME
DBUSER=$USER_NAME
/usr/bin/mysql -u root -p$DBROOT <<EOF
CREATE DATABASE IF NOT EXISTS $DBNAME;
CREATE USER $DBUSER@'localhost' IDENTIFIED BY '$DBPASS';
GRANT USAGE ON *.* TO '$DBUSER'@'localhost' IDENTIFIED BY '$DBPASS' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;
GRANT ALL PRIVILEGES ON $DBNAME.* TO $DBUSER@'localhost';
EOF

fi



sudo mkdir /home/$USER_NAME/.cache
sudo mkdir /home/$USER_NAME/git
sudo cp /cipi/github /home/$USER_NAME/git/deploy
sudo cp /cipi/github.pub /home/$USER_NAME/git/deploy.pub
sudo cp /cipi/deploy.sh /home/$USER_NAME/git/deploy.sh
sudo rpl -q "###CIPI-USER###" "$USER_NAME" /home/$USER_NAME/git/deploy.sh

sudo chown -R $USER_NAME:$USER_NAME /home/$USER_NAME/.cache
sudo chown -R $USER_NAME:$USER_NAME /home/$USER_NAME/git
sudo chown -R $USER_NAME:$USER_NAME /home/$USER_NAME/web
sudo chown -R $USER_NAME:$USER_NAME /home/$USER_NAME/bks

clear
echo "###CIPI###Ok"
