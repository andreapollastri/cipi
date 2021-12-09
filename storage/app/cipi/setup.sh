#!/bin/bash



#################################################### CONFIGURATION ###
BUILD=202112091
PASS=???
DBPASS=???
SERVERID=???
REPO=andreapollastri/cipi


####################################################   CLI TOOLS   ###
reset=$(tput sgr0)
bold=$(tput bold)
underline=$(tput smul)
black=$(tput setaf 0)
white=$(tput setaf 7)
red=$(tput setaf 1)
green=$(tput setaf 2)
yellow=$(tput setaf 3)
blue=$(tput setaf 4)
purple=$(tput setaf 5)
bgblack=$(tput setab 0)
bgwhite=$(tput setab 7)
bgred=$(tput setab 1)
bggreen=$(tput setab 2)
bgyellow=$(tput setab 4)
bgblue=$(tput setab 4)
bgpurple=$(tput setab 5)



#################################################### CIPI SETUP ######



# LOGO
clear
echo "${green}${bold}"
echo ""
echo " ██████ ██ ██████  ██" 
echo "██      ██ ██   ██ ██" 
echo "██      ██ ██████  ██" 
echo "██      ██ ██      ██" 
echo " ██████ ██ ██      ██" 
echo ""
echo "Installation has been started... Hold on!"
echo "${reset}"
sleep 3s



# OS CHECK
clear
clear
echo "${bggreen}${black}${bold}"
echo "OS check..."
echo "${reset}"
sleep 1s

ID=$(grep -oP '(?<=^ID=).+' /etc/os-release | tr -d '"')
VERSION=$(grep -oP '(?<=^VERSION_ID=).+' /etc/os-release | tr -d '"')
if [ "$ID" = "ubuntu" ]; then
    case $VERSION in
        20.04)
            break
            ;;
        *)
            echo "${bgred}${white}${bold}"
            echo "Cipi requires Linux Ubuntu 20.04 LTS"
            echo "${reset}"
            exit 1;
            break
            ;;
    esac
else
    echo "${bgred}${white}${bold}"
    echo "Cipi requires Linux Ubuntu 20.04 LTS"
    echo "${reset}"
    exit 1
fi



# ROOT CHECK
clear
clear
echo "${bggreen}${black}${bold}"
echo "Permission check..."
echo "${reset}"
sleep 1s

if [ "$(id -u)" = "0" ]; then
    clear
else
    clear
    echo "${bgred}${white}${bold}"
    echo "You have to run Cipi as root. (In AWS use 'sudo -s')"
    echo "${reset}"
    exit 1
fi



# BASIC SETUP
clear
clear
echo "${bggreen}${black}${bold}"
echo "Base setup..."
echo "${reset}"
sleep 1s

sudo apt-get update
sudo apt-get -y install software-properties-common curl wget nano vim rpl sed zip unzip openssl expect dirmngr apt-transport-https lsb-release ca-certificates dnsutils dos2unix zsh htop ffmpeg



# MOTD WELCOME MESSAGE
clear
echo "${bggreen}${black}${bold}"
echo "Motd settings..."
echo "${reset}"
sleep 1s

WELCOME=/etc/motd
sudo touch $WELCOME
sudo cat > "$WELCOME" <<EOF

 ██████ ██ ██████  ██ 
██      ██ ██   ██ ██ 
██      ██ ██████  ██ 
██      ██ ██      ██
 ██████ ██ ██      ██

With great power comes great responsibility...

EOF



# SWAP
clear
echo "${bggreen}${black}${bold}"
echo "Memory SWAP..."
echo "${reset}"
sleep 1s

sudo /bin/dd if=/dev/zero of=/var/swap.1 bs=1M count=1024
sudo /sbin/mkswap /var/swap.1
sudo /sbin/swapon /var/swap.1



# ALIAS
clear
echo "${bggreen}${black}${bold}"
echo "Custom CLI configuration..."
echo "${reset}"
sleep 1s

shopt -s expand_aliases
alias ll='ls -alF'



# CIPI DIRS
clear
echo "${bggreen}${black}${bold}"
echo "Cipi directories..."
echo "${reset}"
sleep 1s

sudo mkdir /etc/cipi/
sudo chmod o-r /etc/cipi
sudo mkdir /var/cipi/
sudo chmod o-r /var/cipi



# USER
clear
echo "${bggreen}${black}${bold}"
echo "Cipi root user..."
echo "${reset}"
sleep 1s

sudo pam-auth-update --package
sudo mount -o remount,rw /
sudo chmod 640 /etc/shadow
sudo useradd -m -s /bin/bash cipi
echo "cipi:$PASS"|sudo chpasswd
sudo usermod -aG sudo cipi




# NGINX
clear
echo "${bggreen}${black}${bold}"
echo "nginx setup..."
echo "${reset}"
sleep 1s

sudo apt-get -y install nginx-core
sudo systemctl start nginx.service
sudo rpl -i -w "http {" "http { limit_req_zone \$binary_remote_addr zone=one:10m rate=1r/s; fastcgi_read_timeout 300;" /etc/nginx/nginx.conf
sudo rpl -i -w "http {" "http { limit_req_zone \$binary_remote_addr zone=one:10m rate=1r/s; fastcgi_read_timeout 300;" /etc/nginx/nginx.conf
sudo systemctl enable nginx.service





# FIREWALL
clear
echo "${bggreen}${black}${bold}"
echo "fail2ban setup..."
echo "${reset}"
sleep 1s

sudo apt-get -y install fail2ban
JAIL=/etc/fail2ban/jail.local
sudo unlink JAIL
sudo touch $JAIL
sudo cat > "$JAIL" <<EOF
[DEFAULT]
bantime = 3600
banaction = iptables-multiport
[sshd]
enabled = true
logpath  = /var/log/auth.log
EOF
sudo systemctl restart fail2ban
sudo ufw --force enable
sudo ufw allow ssh
sudo ufw allow http
sudo ufw allow https
sudo ufw allow "Nginx Full"




# PHP
clear
echo "${bggreen}${black}${bold}"
echo "PHP setup..."
echo "${reset}"
sleep 1s


sudo add-apt-repository -y ppa:ondrej/php
sudo apt-get update

sudo apt-get -y install php7.3-fpm
sudo apt-get -y install php7.3-common
sudo apt-get -y install php7.3-curl
sudo apt-get -y install php7.3-openssl
sudo apt-get -y install php7.3-bcmath
sudo apt-get -y install php7.3-mbstring
sudo apt-get -y install php7.3-tokenizer
sudo apt-get -y install php7.3-mysql
sudo apt-get -y install php7.3-sqlite3
sudo apt-get -y install php7.3-pgsql
sudo apt-get -y install php7.3-redis
sudo apt-get -y install php7.3-memcached
sudo apt-get -y install php7.3-json
sudo apt-get -y install php7.3-zip
sudo apt-get -y install php7.3-xml
sudo apt-get -y install php7.3-soap
sudo apt-get -y install php7.3-gd
sudo apt-get -y install php7.3-imagick
sudo apt-get -y install php7.3-fileinfo
sudo apt-get -y install php7.3-imap
sudo apt-get -y install php7.3-cli
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

sudo apt-get -y install php7.4-fpm
sudo apt-get -y install php7.4-common
sudo apt-get -y install php7.4-curl
sudo apt-get -y install php7.4-openssl
sudo apt-get -y install php7.4-bcmath
sudo apt-get -y install php7.4-mbstring
sudo apt-get -y install php7.4-tokenizer
sudo apt-get -y install php7.4-mysql
sudo apt-get -y install php7.4-sqlite3
sudo apt-get -y install php7.4-pgsql
sudo apt-get -y install php7.4-redis
sudo apt-get -y install php7.4-memcached
sudo apt-get -y install php7.4-json
sudo apt-get -y install php7.4-zip
sudo apt-get -y install php7.4-xml
sudo apt-get -y install php7.4-soap
sudo apt-get -y install php7.4-gd
sudo apt-get -y install php7.4-imagick
sudo apt-get -y install php7.4-fileinfo
sudo apt-get -y install php7.4-imap
sudo apt-get -y install php7.4-cli
PHPINI=/etc/php/7.4/fpm/conf.d/cipi.ini
sudo touch $PHPINI
sudo cat > "$PHPINI" <<EOF
memory_limit = 256M
upload_max_filesize = 256M
post_max_size = 256M
max_execution_time = 180
max_input_time = 180
EOF
sudo service php7.4-fpm restart

sudo apt-get -y install php8.0-fpm
sudo apt-get -y install php8.0-common
sudo apt-get -y install php8.0-curl
sudo apt-get -y install php8.0-openssl
sudo apt-get -y install php8.0-bcmath
sudo apt-get -y install php8.0-mbstring
sudo apt-get -y install php8.0-tokenizer
sudo apt-get -y install php8.0-mysql
sudo apt-get -y install php8.0-sqlite3
sudo apt-get -y install php8.0-pgsql
sudo apt-get -y install php8.0-redis
sudo apt-get -y install php8.0-memcached
sudo apt-get -y install php8.0-json
sudo apt-get -y install php8.0-zip
sudo apt-get -y install php8.0-xml
sudo apt-get -y install php8.0-soap
sudo apt-get -y install php8.0-gd
sudo apt-get -y install php8.0-imagick
sudo apt-get -y install php8.0-fileinfo
sudo apt-get -y install php8.0-imap
sudo apt-get -y install php8.0-cli
PHPINI=/etc/php/8.0/fpm/conf.d/cipi.ini
sudo touch $PHPINI
sudo cat > "$PHPINI" <<EOF
memory_limit = 256M
upload_max_filesize = 256M
post_max_size = 256M
max_execution_time = 180
max_input_time = 180
EOF
sudo service php8.0-fpm restart


sudo apt-get -y install php8.1-fpm
sudo apt-get -y install php8.1-common
sudo apt-get -y install php8.1-curl
sudo apt-get -y install php8.1-openssl
sudo apt-get -y install php8.1-bcmath
sudo apt-get -y install php8.1-mbstring
sudo apt-get -y install php8.1-tokenizer
sudo apt-get -y install php8.1-mysql
sudo apt-get -y install php8.1-sqlite3
sudo apt-get -y install php8.1-pgsql
sudo apt-get -y install php8.1-redis
sudo apt-get -y install php8.1-memcached
sudo apt-get -y install php8.1-json
sudo apt-get -y install php8.1-zip
sudo apt-get -y install php8.1-xml
sudo apt-get -y install php8.1-soap
sudo apt-get -y install php8.1-gd
sudo apt-get -y install php8.1-imagick
sudo apt-get -y install php8.1-fileinfo
sudo apt-get -y install php8.1-imap
sudo apt-get -y install php8.1-cli
PHPINI=/etc/php/8.1/fpm/conf.d/cipi.ini
sudo touch $PHPINI
sudo cat > "$PHPINI" <<EOF
memory_limit = 256M
upload_max_filesize = 256M
post_max_size = 256M
max_execution_time = 180
max_input_time = 180
EOF
sudo service php8.1-fpm restart


# PHP EXTRA
sudo apt-get -y install php-dev php-pear


# PHP EXTRA
sudo apt-get -y install php-dev php-pear


# PHP CLI
clear
echo "${bggreen}${black}${bold}"
echo "PHP CLI configuration..."
echo "${reset}"
sleep 1s

sudo update-alternatives --set php /usr/bin/php8.0



# COMPOSER
clear
echo "${bggreen}${black}${bold}"
echo "Composer setup..."
echo "${reset}"
sleep 1s

php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --no-interaction
php -r "unlink('composer-setup.php');"
mv composer.phar /usr/local/bin/composer
composer config --global repo.packagist composer https://packagist.org --no-interaction




# GIT
clear
echo "${bggreen}${black}${bold}"
echo "GIT setup..."
echo "${reset}"
sleep 1s

sudo apt-get -y install git
sudo ssh-keygen -t rsa -C "git@github.com" -f /etc/cipi/github -q -P ""



# SUPERVISOR
clear
echo "${bggreen}${black}${bold}"
echo "Supervisor setup..."
echo "${reset}"
sleep 1s

sudo apt-get -y install supervisor
service supervisor restart



# DEFAULT VHOST
clear
echo "${bggreen}${black}${bold}"
echo "Default vhost..."
echo "${reset}"
sleep 1s

NGINX=/etc/nginx/sites-available/default
if test -f "$NGINX"; then
    sudo unlink NGINX
fi
sudo touch $NGINX
sudo cat > "$NGINX" <<EOF
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    root /var/www/html;
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";
    client_body_timeout 10s;
    client_header_timeout 10s;
    client_max_body_size 256M;
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
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
    }
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF
sudo mkdir /etc/nginx/cipi/
sudo systemctl restart nginx.service





# MYSQL
clear
echo "${bggreen}${black}${bold}"
echo "MySQL setup..."
echo "${reset}"
sleep 1s


sudo apt-get install -y mysql-server
SECURE_MYSQL=$(expect -c "
set timeout 10
spawn mysql_secure_installation
expect \"Press y|Y for Yes, any other key for No:\"
send \"n\r\"
expect \"New password:\"
send \"$DBPASS\r\"
expect \"Re-enter new password:\"
send \"$DBPASS\r\"
expect \"Remove anonymous users? (Press y|Y for Yes, any other key for No)\"
send \"y\r\"
expect \"Disallow root login remotely? (Press y|Y for Yes, any other key for No)\"
send \"n\r\"
expect \"Remove test database and access to it? (Press y|Y for Yes, any other key for No)\"
send \"y\r\"
expect \"Reload privilege tables now? (Press y|Y for Yes, any other key for No) \"
send \"y\r\"
expect eof
")
echo "$SECURE_MYSQL"
/usr/bin/mysql -u root -p$DBPASS <<EOF
use mysql;
CREATE USER 'cipi'@'%' IDENTIFIED WITH mysql_native_password BY '$DBPASS';
GRANT ALL PRIVILEGES ON *.* TO 'cipi'@'%' WITH GRANT OPTION;
FLUSH PRIVILEGES;
EOF



# REDIS
clear
echo "${bggreen}${black}${bold}"
echo "Redis setup..."
echo "${reset}"
sleep 1s

sudo apt install -y redis-server
sudo rpl -i -w "supervised no" "supervised systemd" /etc/redis/redis.conf
sudo systemctl restart redis.service



# LET'S ENCRYPT
clear
echo "${bggreen}${black}${bold}"
echo "Let's Encrypt setup..."
echo "${reset}"
sleep 1s

sudo apt-get install -y certbot
sudo apt-get install -y python3-certbot-nginx



# NODE
clear
echo "${bggreen}${black}${bold}"
echo "Node/npm setup..."
echo "${reset}"
sleep 1s

curl -s https://deb.nodesource.com/gpgkey/nodesource.gpg.key | sudo apt-key add -
curl -sL https://deb.nodesource.com/setup_16.x | sudo -E bash -
NODE=/etc/apt/sources.list.d/nodesource.list
sudo unlink NODE
sudo touch $NODE
sudo cat > "$NODE" <<EOF
deb https://deb.nodesource.com/node_16.x focal main
deb-src https://deb.nodesource.com/node_16.x focal main
EOF
sudo apt-get update
sudo apt -y install nodejs
sudo apt -y install npm




# LAST STEPS
clear
echo "${bggreen}${black}${bold}"
echo "Last steps..."
echo "${reset}"
sleep 1s

sudo echo 'StartLimitBurst=0' >> /usr/lib/systemd/system/user@.service
sudo systemctl daemon-reload

TASK=/etc/cron.d/cipi.crontab
touch $TASK
cat > "$TASK" <<EOF
10 4 * * 7 certbot renew --nginx --non-interactive --post-hook "systemctl restart nginx.service"
20 4 * * 7 apt-get -y update
40 4 * * 7 DEBIAN_FRONTEND=noninteractive DEBIAN_PRIORITY=critical sudo apt-get -q -y -o "Dpkg::Options::=--force-confdef" -o "Dpkg::Options::=--force-confold" dist-upgrade
20 5 * * 7 apt-get clean && apt-get autoclean
50 5 * * * echo 3 > /proc/sys/vm/drop_caches && swapoff -a && swapon -a
* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1
5 2 * * * cd /var/www/html/utility/cipi-update && sh run.sh >> /dev/null 2>&1
EOF
crontab $TASK
sudo systemctl restart nginx.service
sudo rpl -i -w "#PasswordAuthentication" "PasswordAuthentication" /etc/ssh/sshd_config
sudo rpl -i -w "# PasswordAuthentication" "PasswordAuthentication" /etc/ssh/sshd_config
sudo rpl -i -w "PasswordAuthentication no" "PasswordAuthentication yes" /etc/ssh/sshd_config
sudo rpl -i -w "PermitRootLogin yes" "PermitRootLogin no" /etc/ssh/sshd_config
sudo service sshd restart
wget -P /var/www/html/ - https://raw.githubusercontent.com/$REPO/latest/utility/zero-page/index.php
CIPIBULD=/var/www/html/build_$SERVERID.php
sudo touch $CIPIBULD
sudo cat > "$CIPIBULD" <<EOF
$BUILD
EOF
CIPIPING=/var/www/html/ping_$SERVERID.php
sudo touch $CIPIPING
sudo cat > "$CIPIPING" <<EOF
Up
EOF
ARTISAN=/var/www/html/artisan
sudo touch $ARTISAN
UPDATE=/var/www/html/utility/cipi-update/run.sh
sudo touch $UPDATE
PUBKEYGH=/var/www/html/ghkey_$SERVERID.php
sudo touch $PUBKEYGH
sudo cat > "$PUBKEYGH" <<EOF
<?php
echo exec("cat /etc/cipi/github.pub");
EOF





# COMPLETE
clear
echo "${bggreen}${black}${bold}"
echo "Cipi installation has been completed..."
echo "${reset}"
sleep 1s




# SETUP COMPLETE MESSAGE
clear
echo "***********************************************************"
echo "                    SETUP COMPLETE"
echo "***********************************************************"
echo ""
echo " SSH root user: cipi"
echo " SSH root pass: $PASS"
echo " MySQL root user: cipi"
echo " MySQL root pass: $DBPASS"
echo ""
echo "***********************************************************"
echo "          DO NOT LOSE AND KEEP SAFE THIS DATA"
echo "***********************************************************"
