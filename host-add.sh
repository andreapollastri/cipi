#!/usr/bin/env bash

USER_NAME=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 12 | head -n 1)
PASSWORD=$(openssl rand -base64 24)
DOMAIN=
DBROOT=$(for word in $(cat /cipi/DBR); do echo $word; done)
IP=$(dig +short myip.opendns.com @resolver1.opendns.com)
DBNAME=$USER_NAME
DBUSER=$USER_NAME
DBPASS=$(openssl rand -base64 16)

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
            * )
                    echo "ERROR: Unknown option: $1"
                    exit -1
                    ;;
            esac
            shift
done

#CREATE USER
isUserExits(){
    grep $1 /etc/passwd > /dev/null
    [ $? -eq 0 ] && return $TRUE || return $FALSE
}

if ( ! isUserExits $USER_NAME )
    then 
        sudo useradd -m -s $USER_SHELL -d /home/$USER_NAME -G www-data $USER_NAME 
        echo "$USER_NAME:$PASSWORD"|chpasswd
	sudo chmod o-r /home/$USER_NAME
    else
        echo "Error: Retry to run this script!"
        exit 1
fi

mkdir /home/$USER_NAME/web
chown -R $USER_NAME:$USER_NAME /home/$USER_NAME

CONF=/etc/apache2/sites-available/$USER_NAME.conf
touch $CONF

cat > "$CONF" <<EOF
<VirtualHost *:80>
	ServerName $DOMAIN
        ServerAlias www.$DOMAIN
        ServerAdmin webmaster@localhost
        DocumentRoot /home/$USER_NAME/web
	ErrorLog /home/$USER_NAME/error.log
  	CustomLog /home/$USER_NAME/access.log combined
        <Directory />
                Order allow,deny
				Options FollowSymLinks
				Allow from all
				AllowOverRide All
				Require all granted
                SetOutputFilter DEFLATE
        </Directory>
        <Directory /home/$USER_NAME/web>
				Order allow,deny
				Options FollowSymLinks
				Allow from all
				AllowOverRide All
				Require all granted
                SetOutputFilter DEFLATE
        </Directory>
</VirtualHost>
EOF

#MYSQL USER AND DB
/usr/bin/mysql -u root -p$DBROOT <<EOF
CREATE DATABASE IF NOT EXISTS $DBNAME;
CREATE USER $DBUSER@'localhost' IDENTIFIED BY '$DBPASS';
GRANT USAGE ON *.* TO '$DBUSER'@'localhost' IDENTIFIED BY '$DBPASS' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;
GRANT ALL PRIVILEGES ON $DBNAME.* TO $DBUSER@'localhost';
EOF
DBRFILE=/cipi/$DBUSER
touch $DBRFILE
cat > "$DBRFILE" <<EOF
$DBPASS
EOF

#RESTART
sudo a2ensite $USER_NAME.conf
sudo service apache2 reload

#SSL CERTIFICATE
certbot --apache -d $DOMAIN --non-interactive --agree-tos --email admin@admin.com
certbot --apache -d www.$DOMAIN --non-interactive --agree-tos --email admin@admin.com
CRON=/cipi/certbot_renew_$USER_NAME.sh
touch $CRON
cat > "$CRON" <<EOF
sudo certbot certonly --noninteractive --apache --agree-tos --email admin@admin.com --d $DOMAIN,www.$DOMAIN --post-hook "service apache2 reload"
EOF
TASK=/etc/cron.d/certbot_renew_$USER_NAME.crontab
touch $TASK
cat > "$TASK" <<EOF
0 1 * * * $USER_NAME /cipi/certbot_renew_$USER_NAME.sh
EOF
crontab /etc/cron.d/certbot_renew_$USER_NAME.crontab

#RESUME
clear
echo "###################################################################################"
echo "                              INSTALLATION COMPLETE "
echo "###################################################################################"
echo ""
echo "Domain: $DOMAIN"
echo "SFTP/SSH User / Pass: $USER_NAME / $PASSWORD"
echo "Document Root: /home/$USER_NAME/web/"
echo "MySQL DB Name: $DBNAME"
echo "MySQL DB User / Pass: $DBUSER / $DBPASS"
echo "Installed phpmyadmin URL: http://$DOMAIN/phpmyadmin/"
echo ""
echo "                       >>>>> DO NOT LOSE THIS DATA! <<<<<"
echo ""
echo "###################################################################################"
echo ""
