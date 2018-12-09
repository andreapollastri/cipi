#!/usr/bin/env bash

USER_NAME=
DOMAIN=

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
            * )
                    echo "ERROR: Unknown option: $1"
                    exit -1
                    ;;
            esac
            shift
done

ALIAS=$(cat /dev/urandom | tr -dc '0-9' | fold -w 8 | head -n 1)
CONF=/etc/apache2/sites-available/$USER_NAME$ALIAS.conf
touch $CONF

cat > "$CONF" <<EOF
<VirtualHost $DOMAIN:80>
	ServerName $DOMAIN
        ServerAdmin webmaster@localhost
        DocumentRoot /home/$USER_NAME/web
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

#RESTART
sudo a2ensite $USER_NAME$ALIAS.conf
sudo service apache2 reload

#SSL CERTIFICATE
sudo certbot --apache -d $DOMAIN --non-interactive --agree-tos --email admin@admin.com
CRON=/cipi/certbot_renew_$USER_NAME$ALIAS.sh
touch $CRON
cat > "$CRON" <<EOF
sudo certbot certonly --noninteractive --apache --agree-tos --email admin@admin.com --d $DOMAIN --post-hook "service apache2 reload"
EOF
TASK=/etc/cron.d/certbot_renew_$USER_NAME$ALIAS.crontab
touch $TASK
cat > "$TASK" <<EOF
0 1 * * * $USER_NAME /cipi/certbot_renew_$USER_NAME$ALIAS.sh
EOF
sudo crontab /etc/cron.d/certbot_renew_$USER_NAME$ALIAS.crontab

#RESUME
clear
echo "###################################################################################"
echo "                              INSTALLATION COMPLETE "
echo "###################################################################################"
echo ""
echo "Alias: $DOMAIN"
echo "Alias Code: $ALIAS"
echo "Alias User: $USER_NAME"
echo "Document Root: /home/$USER_NAME/web/"
echo ""
echo "                       >>>>> DO NOT LOSE THIS DATA! <<<<<"
echo ""
echo "###################################################################################"
echo ""
