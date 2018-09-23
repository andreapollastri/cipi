#!/bin/bash

#START
echo "###################################################################################"
echo "Please be Patient: Installation will start now....... It may take some time :)"
echo "###################################################################################"
echo -e "\n"

#VARS
IP=$(dig +short myip.opendns.com @resolver1.opendns.com)
USER=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 12 | head -n 1)
PASS=$(openssl rand -base64 32)
DBPASS=$(openssl rand -base64 32)

#CIPI CORE
mkdir /cipi/
wget https://raw.githubusercontent.com/andreapollastri/cipi/master/host-add.sh -O /cipi/host-add.sh
wget https://raw.githubusercontent.com/andreapollastri/cipi/master/host-del.sh -O /cipi/host-del.sh
wget https://raw.githubusercontent.com/andreapollastri/cipi/master/ssl.sh -O /cipi/ssl.sh
wget https://raw.githubusercontent.com/andreapollastri/cipi/master/passwd.sh -O /cipi/passwd.sh
wget https://raw.githubusercontent.com/andreapollastri/cipi/master/alias-add.sh -O /cipi/alias-add.sh
wget https://raw.githubusercontent.com/andreapollastri/cipi/master/alias-del.sh -O /cipi/alias-del.sh
DBRFILE=/cipi/DBR
touch $DBRFILE
cat > "$DBRFILE" <<EOF
$DBPASS
EOF
sudo chmod o-r /cipi

#ALIAS
shopt -s expand_aliases
alias ll='ls -alF'

#NEWROOT USER
sudo useradd -m -s /bin/bash $USER
echo "$USER:$PASS"|chpasswd
usermod -aG sudo $USER

#PHP7 PPA
sudo apt-get -y install python-software-properties
sudo add-apt-repository -y ppa:ondrej/php

#REPO UPDATES
sudo apt-get update

#LAMP INSTALLATION
sudo apt-get -y install rpl dos2unix fail2ban openssl apache2 php7.2 php7.2-common php7.2-cli php7.2-fpm php-pear php7.2-curl php7.2-dev php7.2-gd php7.2-mbstring php7.2-zip php7.2-mysql php7.2-xml libmcrypt-dev mysql-client

#FIREWALL
sudo ufw --force-enable reset

#MYSQL INSTALLATION AND PASSWORD SET
sudo debconf-set-selections <<< "mysql-server mysql-server/root_password password $DBPASS"
sudo debconf-set-selections <<< "mysql-server mysql-server/root_password_again password $DBPASS"
sudo apt-get -y install mysql-server

#SERVICE RESTART AND CONFIGURATION FIXING
echo -e "\n"
sudo a2enmod rewrite
echo -e "\n"
sudo a2enmod proxy_fcgi setenvif
echo -e "\n"
sudo a2enconf php7.2-fpm
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

#PHPMYADMIN INSTALLATION
set -euo pipefail
IFS=$'\n\t'
sudo add-apt-repository -y ppa:nijel/phpmyadmin
sudo apt-get update
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/dbconfig-install boolean true"
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/app-password-confirm password $DBPASS"
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/mysql/admin-pass password $DBPASS"
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/mysql/app-pass password $DBPASS"
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2"
sudo apt-get -y install phpmyadmin
sudo service apache2 restart
sudo apt-get clean

#DEFAULT VIRTUALHOST
sudo rm -rf /var/www/html/
sudo mkdir /var/www/html/
BASE=/var/www/html/index.html
touch $BASE
cat > "$BASE" <<EOF
<title>It works!</title><br><br>
<center><h1>It works!</h1></center>
EOF
sudo service apache2 restart

sudo unlink /etc/apache2/sites-available/000-default.conf
CONF=/etc/apache2/sites-available/000-default.conf
touch $CONF

cat > "$CONF" <<EOF
<VirtualHost *:80>
        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/html
        <Directory />
          Order allow,deny
          Options FollowSymLinks
          Allow from all
          AllowOverRide All
          Require all granted
          SetOutputFilter DEFLATE
        </Directory>
        <Directory /var/www/html>
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
a2ensite 000-default.conf
service apache2 reload

BASE=/etc/apache2/sites-available/base.conf
touch $BASE
cat > "$BASE" <<EOF
<VirtualHost $IP:80>
        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/html
        <Directory />
          Order allow,deny
          Options FollowSymLinks
          Allow from all
          AllowOverRide All
          Require all granted
          SetOutputFilter DEFLATE
        </Directory>
        <Directory /var/www/html>
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
a2ensite base.conf
service apache2 reload

#LET'S ENCRYPT
sudo add-apt-repository -y ppa:certbot/certbot
sudo apt-get update
sudo apt-get -y install python-certbot-apache
sudo service apache2 restart

#COMPOSER INSTALLATION
sudo php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
sudo php composer-setup.php
sudo php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer

#SSH AND ROOT ACCESS CONFIGURATION
PORT=$(( ((RANDOM<<15)|RANDOM) % 63001 + 2000 ))
sudo rpl -i -w "# Port 22" "Port 22" /etc/ssh/sshd_config
sudo rpl -i -w "#Port 22" "Port 22" /etc/ssh/sshd_config
sudo rpl -i -w "Port 22" "Port $PORT" /etc/ssh/sshd_config
sudo rpl -i -w "PermitRootLogin yes" "PermitRootLogin no" /etc/ssh/sshd_config
sudo service sshd restart
echo -e "\n"

#OPTIMIZE
dos2unix /cipi/passwd.sh
dos2unix /cipi/host-add.sh
dos2unix /cipi/host-del.sh
dos2unix /cipi/alias-add.sh
dos2unix /cipi/alias-del.sh
dos2unix /cipi/ssl.sh
PHPINI=/etc/php/7.2/fpm/conf.d/cipi.ini
touch $PHPINI
cat > "$PHPINI" <<EOF
memory_limit = 256M
upload_max_filesize = 256M
post_max_size = 256M
EOF
sudo service php7.2-fpm restart

#FINAL MESSAGGE
clear
echo "###################################################################################"
echo "                              INSTALLATION COMPLETE "
echo "###################################################################################"
echo ""
echo "IP: $IP"
echo "SSH port: $PORT"
echo "Root User / Pass: $USER / $PASS"
echo "MySql root password: $DBPASS"
echo "Installed phpmyadmin URL: http://$IP/phpmyadmin/"
echo ""
echo "                       >>>>> DO NOT LOSE THIS DATA! <<<<<"
echo ""
echo "###################################################################################"
echo ""
