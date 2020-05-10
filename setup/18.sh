#!/bin/bash


#START
clear
echo "Welcome on Cipi Cloud Control Panel ;)"
sleep 3s



#WAIT
clear
echo "Wait..."
sleep 3s



#OS Check
ID=$(grep -oP '(?<=^ID=).+' /etc/os-release | tr -d '"')
VERSION=$(grep -oP '(?<=^VERSION_ID=).+' /etc/os-release | tr -d '"')
if [ "$ID:$VERSION" = "ubuntu:18.04" ]; then

    clear
    echo "Running on Ubuntu 18.04 LTS :)"
    sleep 2s

else

    clear
    echo -e "You have to run this script on Ubuntu 18.04 LTS"
    exit 1

fi



#ROOT Check
if [ "$(id -u)" = "0" ]; then

    clear
    echo "Running as root :)"
    sleep 2s

else

    clear
    echo -e "You have to run this script as root. In AWS digit 'sudo -s'"
    exit 1

fi



#START
clear
echo "Installation has been started... It may takes some time! Hold on :)"
sleep 5s



#SERVER BASIC CONFIGURATION
clear
echo "Server basic configuration..."
sleep 3s

sudo apt-get update

sudo apt-get -y install rpl zip unzip openssl curl dirmngr apt-transport-https lsb-release ca-certificates dnsutils htop


sudo rpl -i -w "#PasswordAuthentication" "PasswordAuthentication" /etc/ssh/sshd_config
sudo rpl -i -w "# PasswordAuthentication" "PasswordAuthentication" /etc/ssh/sshd_config
sudo rpl -i -w "PasswordAuthentication no" "PasswordAuthentication yes" /etc/ssh/sshd_config
sudo rpl -i -w "PermitRootLogin yes" "PermitRootLogin no" /etc/ssh/sshd_config
sudo service sshd restart

WELCOME=/etc/motd
sudo touch $WELCOME
sudo cat > "$WELCOME" <<EOF
  _____ _       _
 / ____(_)     (_)
| |     _ _ __  _
| |    | |  _ \| |
| |____| | |_) | |
 \_____|_| .__/|_|
         | |
         |_|

    <\ cipi.sh >

You are into the server!
Remember... "With great power comes great responsibility!"
Enjoy your session ;) ...

EOF

sudo /bin/dd if=/dev/zero of=/var/swap.1 bs=1M count=1024
sudo /sbin/mkswap /var/swap.1
sudo /sbin/swapon /var/swap.1

sudo mkdir /cipi/
sudo chmod o-r /cipi

shopt -s expand_aliases
alias ll='ls -alF'

IP=$(dig +short myip.opendns.com @resolver1.opendns.com)
PASS=$(openssl rand -base64 20)
DBPASS=$(openssl rand -base64 16)

DATABASE=/cipi/dbroot
sudo touch $DATABASE
sudo cat > "$DATABASE" <<EOF
$DBPASS
EOF

clear
echo "Server basic configuration: OK!"
sleep 3s



#REPOSITORIES
clear
echo "Repositories update..."
sleep 3s

sudo apt-get -y install software-properties-common
sudo apt-get -y autoremove
sudo apt-get update
sudo apt-get upgrade -y
sudo apt-get update
clear

clear
echo "Repositories: OK!"
sleep 3s



#FIREWALL
clear
echo "Firewall installation..."
sleep 3s

sudo apt-get -y install fail2ban

JAIL=/etc/fail2ban/jail.local
sudo unlink JAIL
sudo touch $JAIL
sudo cat > "$JAIL" <<EOF
[DEFAULT]
# Ban hosts for one hour:
bantime = 3600

# Override /etc/fail2ban/jail.d/00-firewalld.conf:
banaction = iptables-multiport

[sshd]
enabled = true

# Auth log file
logpath  = /var/log/auth.log
EOF

sudo systemctl restart fail2ban
sudo ufw --force enable
sudo ufw allow ssh
sudo ufw allow http
sudo ufw allow https
sudo ufw allow "Nginx Full"

clear
echo "Firewall: OK!"
sleep 3s



#NGINX
clear
echo "nginx installation..."
sleep 3s

sudo apt-get -y install nginx
sudo systemctl start nginx.service
sudo systemctl enable nginx.service

clear
echo "nginx: OK!"
sleep 3s



#PHP
clear
echo "PHP installation..."
sleep 3s

sudo add-apt-repository -y ppa:ondrej/php
sudo apt-get update

sudo apt-get -y install php7.4-fpm
sudo apt-get -y install php7.4-common
sudo apt-get -y install php7.4-mbstring
sudo apt-get -y install php7.4-mysql
sudo apt-get -y install php7.4-xml
sudo apt-get -y install php7.4-zip
sudo apt-get -y install php7.4-bcmath
sudo apt-get -y install php7.4-imagick
PHPINI74=/etc/php/7.4/fpm/conf.d/cipi.ini
sudo touch $PHPINI74
sudo cat > "$PHPINI74" <<EOF
memory_limit = 256M
upload_max_filesize = 256M
post_max_size = 256M
max_execution_time = 180
max_input_time = 180
EOF
sudo service php7.4-fpm restart

sudo update-alternatives --set php /usr/bin/php7.4

NGINX=/etc/nginx/sites-available/default
sudo unlink NGINX
sudo touch $NGINX
sudo cat > "$NGINX" <<EOF
server {

    listen 80 default_server;
    listen [::]:80 default_server;

    root /var/www/html/public;

    client_body_timeout 10s;
    client_header_timeout 10s;
    client_max_body_size 256M;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    index index.html index.php;

    charset utf-8;

    server_tokens off;

    location / {
        try_files   \$uri     \$uri/  /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF
sudo systemctl restart nginx.service

clear
echo "PHP: OK!"
sleep 3s



#MYSQL
clear
echo "Mysql installation..."
sleep 3s

sudo debconf-set-selections <<< "mysql-server mysql-server/root_password password $DBPASS"
sudo debconf-set-selections <<< "mysql-server mysql-server/root_password_again password $DBPASS"
sudo apt-get -y install mysql-server mysql-client

clear
echo "Mysql: OK!"
sleep 3s




#LET'S ENCRYPT
clear
echo "Let's Encrypt installation..."
sleep 3s

sudo add-apt-repository -y ppa:certbot/certbot
sudo apt-get -y install python-certbot-nginx

clear
echo "Let's Encrypt: OK!"
sleep 3s



#COMPOSER
clear
echo "Composer installation..."
sleep 3s

sudo php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
sudo php composer-setup.php
sudo php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer
sudo composer config --global repo.packagist composer https://packagist.org

clear
echo "Composer installation: OK!"
sleep 3s



#CIPI USER
clear
echo "User creation..."
sleep 3s

sudo useradd -m -s /bin/bash cipi
echo "cipi:$PASS"|chpasswd
sudo usermod -aG sudo cipi

clear
echo "User creation: OK!"
sleep 3s



#APPLICATION INSTALLATION
clear
echo "Application installation..."
sleep 3s

/usr/bin/mysql -u root -p$DBPASS <<EOF
CREATE DATABASE IF NOT EXISTS cipi;
EOF
clear
sudo rm -rf /var/www/html
sudo mkdir /var/www/html
echo "Downloading Cipi from packagist.org... It may takes some time! Hold on :)"
sleep 1s
composer create-project andreapollastri/cipi /var/www/html
cd /var/www/html && sudo unlink .env
cd /var/www/html && sudo cp .env.example .env
sudo rpl -i -w "DB_USERNAME=dbuser" "DB_USERNAME=root" /var/www/html/.env
sudo rpl -i -w "DB_PASSWORD=dbpass" "DB_PASSWORD=$DBPASS" /var/www/html/.env
sudo rpl -i -w "DB_DATABASE=dbname" "DB_DATABASE=cipi" /var/www/html/.env
sudo rpl -i -w "APP_URL=http://localhost" "APP_URL=http://$IP" /var/www/html/.env
sudo chmod -R o+w /var/www/html/storage
sudo chmod -R 775 /var/www/html/storage
sudo chmod -R o+w /var/www/html/bootstrap/cache
sudo chmod -R 775 /var/www/html/bootstrap/cache
sudo chown -R www-data:www-data /var/www/html
cd /var/www/html && composer dump-autoload
cd /var/www/html && php artisan key:generate
cd /var/www/html && php artisan cache:clear
cd /var/www/html && php artisan storage:link
cd /var/www/html && php artisan view:cache
cd /var/www/html && php artisan key:generate
cd /var/www/html && php artisan migrate --seed --force
cd /var/www/html && php artisan config:cache

clear
echo "Application installation: OK!"
sleep 3s



#END
clear
echo "Cipi installation is finishing. Wait..."
sleep 3s

sudo apt-get upgrade -y
sudo apt-get update

TASK=/etc/cron.d/cipi.crontab
touch $TASK
cat > "$TASK" <<EOF
0 5 * * 7 certbot renew --nginx --non-interactive --post-hook "systemctl restart nginx.service"
5 4 * * sun DEBIAN_FRONTEND=noninteractive DEBIAN_PRIORITY=critical sudo apt-get -q -y -o "Dpkg::Options::=--force-confdef" -o "Dpkg::Options::=--force-confold" dist-upgrade
* 3 * * sun apt-get -y update"
EOF
crontab $TASK

sudo systemctl restart nginx.service

clear
echo "Cipi installation has been completed... Wait for your data!"
sleep 3s



#FINAL MESSAGGE
clear
echo "***********************************************************"
echo "                    SETUP COMPLETE"
echo "***********************************************************"
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
echo "Visit http://$IP and login into Cipi:"
echo "USERNAME: admin@admin.com"
echo "PASSWORD: 12345678"
echo "After login, you can update your data into 'settings' section"
echo ""
echo "If you need SSH into this server:"
echo "Cipi server username: cipi"
echo "Cipi server password: $PASS"
echo "Cipi server db root pass: $DBPASS"
echo ""
echo "Enjoy Cipi :)"
echo ""
echo "***********************************************************"
echo "          DO NOT LOSE AND KEEP SAFE THIS DATA"
echo "***********************************************************"
