#!/bin/bash

DBPASS=$(openssl rand -base64 32)

clear
echo "Wait..."
sleep 3s
echo -e "\n"

#VARS
IP=$(dig +short myip.opendns.com @resolver1.opendns.com)

#START
sudo mkdir /cipi/
sudo mkdir /cipi/
sudo chmod o-r /cipi
clear
echo "Installation has been started... It may takes some time! Hold on :)"
sleep 6s
echo -e "\n"

#PHP7 PPA
sudo apt-get -y install python-software-properties
sudo apt-get -y install software-properties-common
sudo add-apt-repository -y universe
sudo apt-get -y install software-properties-common
sudo add-apt-repository -y ppa:ondrej/php
clear
echo "Repositories: OK!"
sleep 3s
echo -e "\n"

#REPO UPDATES
sudo apt-get update

#LAMP INSTALLATION
sudo apt -y purge libzip4
wget http://ftp.it.debian.org/debian/pool/main/libz/libzip/libzip4_1.5.1-4_amd64.deb
sudo dpkg -i libzip4_1.5.1-4_amd64.deb
sudo apt-get -y install rpl dos2unix fail2ban openssl apache2 php7.3 php7.3-common php7.3-intl php7.3-cli php7.3-fpm php-pear php7.3-curl php7.3-dev php7.3-gd php7.3-mbstring php-gettext php7.3-zip php7.3-mysql php7.3-xml libmcrypt-dev zip unzip mysql-client
clear
echo "Base installation: OK!"
sleep 3s
echo -e "\n"

#FIREWALL
sudo ufw --force-enable reset
clear
echo "Firewall rules: OK!"
sleep 3s
echo -e "\n"

#MYSQL INSTALLATION AND PASSWORD SET
sudo debconf-set-selections <<< "mysql-server mysql-server/root_password password $DBPASS"
sudo debconf-set-selections <<< "mysql-server mysql-server/root_password_again password $DBPASS"
sudo apt-get -y install mysql-server
clear
echo "MySql service: OK!"
sleep 3s
echo -e "\n"

#SERVICE RESTART AND CONFIGURATION FIXING
echo -e "\n"
sudo a2enmod rewrite
echo -e "\n"
sudo a2enmod proxy_fcgi setenvif
echo -e "\n"
sudo a2enconf php7.3-fpm
echo -e "\n"
sudo rpl -i -w "AllowOverride None" "AllowOverride All" /etc/apache2/apache2.conf
echo -e "\n"
sudo service apache2 restart && apache2 reload && service mysql restart > /dev/null
echo -e "\n"
php -v
if [ $? -ne 0 ]; then
   echo "Please Check the Install Services, There is some $(tput bold)$(tput setaf 1)Problem$(tput sgr0)"
else
   echo "Installed Services run $(tput bold)$(tput setaf 2)Sucessfully$(tput sgr0)"
fi
clear
echo "PHP-FPM configuration: OK!"
sleep 3s
echo -e "\n"

sudo unlink /etc/apache2/sites-available/000-default.conf
CONF=/etc/apache2/sites-available/000-default.conf
sudo touch $CONF
sudo cat > "$CONF" <<EOF
<VirtualHost *:80>
        ServerAdmin webmaster@localhost
        DocumentRoot /cipi/public/
        <Directory />
          Order allow,deny
          Options FollowSymLinks
          Allow from all
          AllowOverRide All
          Require all granted
          SetOutputFilter DEFLATE
        </Directory>
        <Directory /cipi/public>
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
sudo a2ensite 000-default.conf
sudo service apache2 reload
clear
echo "Default virtualhost: OK!"
sleep 3s
echo -e "\n"


#COMPOSER INSTALLATION
sudo php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
sudo php composer-setup.php
sudo php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer
clear
echo "Composer installation: OK!"
sleep 3s
echo -e "\n"


sudo /bin/dd if=/dev/zero of=/var/swap.1 bs=1M count=1024
sudo /sbin/mkswap /var/swap.1
sudo /sbin/swapon /var/swap.1
PHPINI=/etc/php/7.3/fpm/conf.d/cipi.ini
sudo touch $PHPINI
sudo cat > "$PHPINI" <<EOF
memory_limit = 256M
upload_max_filesize = 256M
post_max_size = 256M
max_execution_time = 180
max_input_time = 180
EOF
sudo service php7.3-fpm restart
sudo service apache2 restart
sudo systemctl restart apache2.service
clear
echo "Optimization: OK!"
sleep 3s
echo -e "\n"



#APPLICATION INSTALLATION
/usr/bin/mysql -u root -p$DBPASS <<EOF
CREATE DATABASE IF NOT EXISTS cipi;
EOF
composer create-project andreapollastri/cipi /cipi/
cd /cipi/ && sudo cp .env.example .env
sudo rpl -i -w "DB_USERNAME=dbuser" "DB_USERNAME=root" /cipi/.env
sudo rpl -i -w "DB_PASSWORD=dbpass" "DB_PASSWORD=$DBPASS" /cipi/.env
sudo rpl -i -w "DB_DATABASE=dbname" "DB_DATABASE=cipi" /cipi/.env
sudo rpl -i -w "APP_URL=http://localhost" "APP_URL=http://$IP" /cipi/.env
cd /cipi/ && php artisan key:generate
cd /cipi/ && php artisan storage:link
cd /cipi/ && php artisan migrate --seed
sudo chmod -R o+rx /cipi/
sudo chmod -R 777 /cipi/storage/
sudo chmod -R 777 /cipi/public/storage/
clear
echo "Application installation: OK!"
sleep 3s
echo -e "\n"

# SETUP SSH KEYLESS ACCESS INTO CHILD SERVERS
cat /dev/zero | ssh-keygen -q -N "" > /dev/null
AUTHORIZEDKEY=$(cat ~/.ssh/id_rsa.pub)
sudo rpl -i -w "# CIPI-CONTROL-PANEL-PUBLIC-KEY" "$AUTHORIZEDKEY" /cipi/storage/app/configuration/authorized_keys

#FINAL MESSAGGE
clear
echo ""
echo "  _____ _       _ "
echo " / ____(_)     (_)"
echo "| |     _ _ __  _ "
echo "| |    | |  _ \| |"
echo "| |____| | |_) | |"
echo " \_____|_| .__/|_|"
echo "         | |      "
echo "         |_|      "
echo ""
echo "<\ SETUP COMPLETE >"
echo ""
echo "URL: http://$IP"
echo "USER: admin@admin.com"
echo "PASS: 12345678"
echo ""
echo "Enjoy Cipi :)"
echo ""
