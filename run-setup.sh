#!/bin/bash

#################################################### CONFIGURATION ###
USERPASSWORD=$(openssl rand -base64 32|sha256sum|base64|head -c 32| tr '[:upper:]' '[:lower:]')
DATABASEPASSWORD=$(openssl rand -base64 24|sha256sum|base64|head -c 32| tr '[:upper:]' '[:lower:]')
GITREPOSITORY=andreapollastri/cipi
IPDOMAIN=.sslip.io
if [ -z "$1" ];
    GITBRANCH=4.x
then
    GITBRANCH=$1
fi


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
        22.04)
            break
            ;;
        *)
            echo "${bgred}${white}${bold}"
            echo "Cipi requires Linux Ubuntu 22.04 LTS"
            echo "${reset}"
            exit 1;
            break
            ;;
    esac
else
    echo "${bgred}${white}${bold}"
    echo "Cipi requires Linux Ubuntu 22.04 LTS"
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

sudo DEBIAN_FRONTEND=noninteractive apt-get -y update
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install software-properties-common curl wget nano vim rpl sed zip unzip expect dirmngr apt-transport-https lsb-release ca-certificates dnsutils dos2unix htop nodejs



# GET IP
clear
clear
echo "${bggreen}${black}${bold}"
echo "Getting IP..."
echo "${reset}"
sleep 1s

SERVERIP=$(curl -s https://checkip.amazonaws.com)
SERVERIPWITHDASH="$( echo "$SERVERIP" | tr  '.' '-'  )"


# MOTD WELCOME MESSAGE
clear
echo "${bggreen}${black}${bold}"
echo "Motd settings..."
echo "${reset}"
sleep 1s

WELCOMEFILE=/etc/motd
sudo touch $WELCOMEFILE
sudo cat > "$WELCOMEFILE" <<EOF

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
echo "cipi:$USERPASSWORD"|sudo chpasswd
sudo usermod -aG sudo cipi



# NGINX
clear
echo "${bggreen}${black}${bold}"
echo "nginx setup..."
echo "${reset}"
sleep 1s

sudo DEBIAN_FRONTEND=noninteractive apt-get -y update
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install nginx
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install nginx-extras
sudo systemctl start nginx.service
sudo rpl "http {" "http { \\n   limit_req_zone \$binary_remote_addr zone=one:10m rate=1r/s; fastcgi_read_timeout 300; \\n   more_set_headers 'Server: Managed by cipi.sh';" /etc/nginx/nginx.conf
sudo systemctl enable nginx.service
sudo systemctl restart nginx.service



# FIREWALL
clear
echo "${bggreen}${black}${bold}"
echo "fail2ban setup..."
echo "${reset}"
sleep 1s

sudo DEBIAN_FRONTEND=noninteractive apt-get -y update
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install fail2ban
JAILFILE=/etc/fail2ban/jail.local
sudo unlink $JAILFILE
sudo touch $JAILFILE
sudo cat > "$JAILFILE" <<EOF
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

sudo DEBIAN_FRONTEND=noninteractive add-apt-repository -y ppa:ondrej/php
sudo DEBIAN_FRONTEND=noninteractive apt-get -y update
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.3-fpm
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.3-common
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.3-curl
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.3-bcmath
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.3-mbstring
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.3-tokenizer
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.3-mysql
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.3-sqlite3
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.3-pgsql
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.3-redis
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.3-memcached
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.3-json
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.3-zip
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.3-xml
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.3-soap
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.3-gd
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.3-imagick
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.3-fileinfo
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.3-imap
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.3-cli
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.3-openssl
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.3-intl
PHPINI=/etc/php/8.3/fpm/conf.d/cipi.ini
sudo touch $PHPINI
sudo cat > "$PHPINI" <<EOF
memory_limit = 256M
upload_max_filesize = 256M
post_max_size = 256M
max_execution_time = 180
max_input_time = 180
EOF
sudo service php8.3-fpm restart



sudo DEBIAN_FRONTEND=noninteractive add-apt-repository -y ppa:ondrej/php
sudo DEBIAN_FRONTEND=noninteractive apt-get -y update
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.4-fpm
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.4-common
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.4-curl
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.4-bcmath
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.4-mbstring
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.4-tokenizer
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.4-mysql
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.4-sqlite3
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.4-pgsql
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.4-redis
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.4-memcached
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.4-json
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.4-zip
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.4-xml
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.4-soap
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.4-gd
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.4-imagick
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.4-fileinfo
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.4-imap
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.4-cli
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.4-openssl
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php8.4-intl
PHPINI=/etc/php/8.4/fpm/conf.d/cipi.ini
sudo touch $PHPINI
sudo cat > "$PHPINI" <<EOF
memory_limit = 256M
upload_max_filesize = 256M
post_max_size = 256M
max_execution_time = 180
max_input_time = 180
EOF
sudo service php8.4-fpm restart



# PHP CLI
clear
echo "${bggreen}${black}${bold}"
echo "PHP CLI configuration..."
echo "${reset}"
sleep 1s

sudo update-alternatives --set php /usr/bin/php8.4



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

sudo DEBIAN_FRONTEND=noninteractive apt-get -y update
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install git



# SUPERVISOR
clear
echo "${bggreen}${black}${bold}"
echo "Supervisor setup..."
echo "${reset}"
sleep 1s

sudo DEBIAN_FRONTEND=noninteractive apt-get -y update
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install supervisor
sudo service supervisor restart



# DEFAULT VHOST
clear
echo "${bggreen}${black}${bold}"
echo "Default vhost..."
echo "${reset}"
sleep 1s

NGINXCONFIG=/etc/nginx/sites-available/default
if test -f "$NGINXCONFIG"; then
    sudo unlink $NGINXCONFIG
fi
sudo touch $NGINXCONFIG
sudo cat > "$NGINXCONFIG" <<EOF
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    root /var/www/html/public;
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
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
    }
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF
sudo mkdir /etc/nginx/cipi/
sudo systemctl restart nginx.service



# PANEL VHOST
clear
echo "${bggreen}${black}${bold}"
echo "Panel vhost..."
echo "${reset}"
sleep 1s

PANELCONFIG=/etc/nginx/sites-available/panel.conf
sudo touch $PANELCONFIG
sudo cat > "$PANELCONFIG" <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name cipi-$SERVERIPWITHDASH$IPDOMAIN;
    root /var/www/html/public;
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
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
    }
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF
sudo ln -s $PANELCONFIG /etc/nginx/sites-enabled/panel.conf
sudo systemctl restart nginx.service

# MYSQL
clear
echo "${bggreen}${black}${bold}"
echo "MySQL setup..."
echo "${reset}"
sleep 1s

sudo DEBIAN_FRONTEND=noninteractive apt-get -y update
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install mysql-server
SECUREMYSQL=$(expect -c "
set timeout 10
spawn mysql_secure_installation
expect \"Press y|Y for Yes, any other key for No:\"
send \"n\r\"
expect \"New password:\"
send \"$DATABASEPASSWORD\r\"
expect \"Re-enter new password:\"
send \"$DATABASEPASSWORD\r\"
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
echo "$CIPISECUREMYSQL"
/usr/bin/mysql -u root -p$DATABASEPASSWORD <<EOF
use mysql;
CREATE USER 'cipi'@'%' IDENTIFIED WITH mysql_native_password BY '$DATABASEPASSWORD';
GRANT ALL PRIVILEGES ON *.* TO 'cipi'@'%' WITH GRANT OPTION;
FLUSH PRIVILEGES;
EOF



# REDIS
clear
echo "${bggreen}${black}${bold}"
echo "Redis setup..."
echo "${reset}"
sleep 1s

sudo DEBIAN_FRONTEND=noninteractive apt-get -y update
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install redis-server
sudo rpl -i -w "supervised no" "supervised systemd" /etc/redis/redis.conf
sudo systemctl restart redis.service



# LET'S ENCRYPT
clear
echo "${bggreen}${black}${bold}"
echo "Let's Encrypt setup..."
echo "${reset}"
sleep 1s

sudo DEBIAN_FRONTEND=noninteractive apt-get -y update
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install certbot
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install python3-certbot-nginx




#PANEL INSTALLATION
clear
echo "${bggreen}${black}${bold}"
echo "Panel installation..."
echo "${reset}"
sleep 1s

/usr/bin/mysql -u root -p$DATABASEPASSWORD <<EOF
CREATE DATABASE IF NOT EXISTS cipi;
EOF
clear

sudo rm -rf /var/www/html
cd /var/www && git clone https://github.com/$GITREPOSITORY.git html
cd /var/www/html && git pull
cd /var/www/html && git checkout $GITBRANCH
cd /var/www/html && git pull
sudo chmod -R o+w /var/www/html/storage
sudo chmod -R 775 /var/www/html/storage
sudo chmod -R o+w /var/www/html/bootstrap/cache
sudo chmod -R 775 /var/www/html/bootstrap/cache
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 775 /var/www/html

cd /var/www/html && unlink .env
cd /var/www/html && cp .env.example .env
cd /var/www/html && composer install --no-interaction
sudo chown -R www-data:www-data /var/www/html
rpl -i -w "APP_ENV=local" "APP_ENV=production" /var/www/html/.env
rpl -i -w "APP_DEBUG=true" "APP_DEBUG=false" /var/www/html/.env
rpl -i -w "APP_URL=http://localhost" "APP_URL=https://cipi-$SERVERIPWITHDASH$IPDOMAIN" /var/www/html/.env
rpl -i -w "DB_PASSWORD=changeme" "DB_PASSWORD=$DATABASEPASSWORD" /var/www/html/.env
rpl -i -w "PANEL_SERVER_DOMAIN=changeme" "PANEL_SERVER_DOMAIN=cipi-$SERVERIPWITHDASH$IPDOMAIN" /var/www/html/.env
rpl -i -w "PANEL_SERVER_IP=changeme" "PANEL_SERVER_IP=$SERVERIP" /var/www/html/.env
rpl -i -w "PANEL_SERVER_NAME=changeme" "PANEL_SERVER_NAME=cipi-$SERVERIPWITHDASH" /var/www/html/.env
rpl -i -w "PANEL_CIPI_PASSWORD=changeme" "PANEL_CIPI_PASSWORD=$USERPASSWORD" /var/www/html/.env
rpl -i -w "PANEL_MYSQL_PASSWORD=changeme" "PANEL_MYSQL_PASSWORD=$DATABASEPASSWORD" /var/www/html/.env
sudo su -l www-data -s /bin/bash -c "cd /var/www/html && composer install --no-interaction"
sudo su -l www-data -s /bin/bash -c "cd /var/www/html && php artisan key:generate"
cd /var/www/html && php artisan config:clear
cd /var/www/html && php artisan migrate --seed --force
cd /var/www/html && php artisan storage:link
cd /var/www/html && php artisan config:cache
cd /var/www/html && php artisan route:cache
cd /var/www/html && php artisan view:cache
cd /var/www/html && php artisan optimize
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 775 /var/www/html
git config --global --add safe.directory /var/www/html


# FINE TUNING
clear
echo "${bggreen}${black}${bold}"
echo "Fine tuning..."
echo "${reset}"
sleep 1s

sudo echo 'DefaultStartLimitIntervalSec=1s' >> /usr/lib/systemd/system/user@.service
sudo echo 'DefaultStartLimitBurst=50' >> /usr/lib/systemd/system/user@.service
sudo echo 'StartLimitBurst=0' >> /usr/lib/systemd/system/user@.service
sudo systemctl daemon-reload

sudo -S sudo fuser -k 80/tcp
sudo -S sudo fuser -k 443/tcp
sudo systemctl restart nginx.service
ufw disable
certbot --nginx -d cipi-$SERVERIPWITHDASH$IPDOMAIN --non-interactive --agree-tos --register-unsafely-without-email
sudo sed -i 's/443 ssl/443 ssl http2/g' /etc/nginx/sites-enabled/default.conf
sudo ufw --force enable
sudo systemctl restart nginx.service

TASK=/etc/cron.d/cipi.crontab
touch $TASK
cat > "$TASK" <<EOF
0 6 * * 0 certbot renew -n -q --pre-hook "service nginx stop" --post-hook "service nginx start"
0 4 * * 4 certbot renew --nginx --non-interactive --post-hook "systemctl restart nginx.service"
20 4 * * 7 apt-get -y update
40 4 * * 7 DEBIAN_FRONTEND=noninteractive DEBIAN_PRIORITY=critical sudo apt-get -q -y -o "Dpkg::Options::=--force-confdef" -o "Dpkg::Options::=--force-confold" dist-upgrade
20 5 * * 7 apt-get clean && apt-get autoclean
50 5 * * * echo 3 > /proc/sys/vm/drop_caches && swapoff -a && swapon -a
* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1
5 2 * * * cd /var/www/html && sh run-update.sh >> /dev/null 2>&1
EOF
crontab $TASK
sudo systemctl restart nginx.service
sudo rpl -i -w "#PasswordAuthentication" "PasswordAuthentication" /etc/ssh/sshd_config
sudo rpl -i -w "# PasswordAuthentication" "PasswordAuthentication" /etc/ssh/sshd_config
sudo rpl -i -w "PasswordAuthentication no" "PasswordAuthentication yes" /etc/ssh/sshd_config
sudo rpl -i -w "PermitRootLogin yes" "PermitRootLogin no" /etc/ssh/sshd_config
sudo service sshd restart
TASK=/etc/supervisor/conf.d/cipi.conf
touch $TASK
cat > "$TASK" <<EOF
[program:cipi-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=8
redirect_stderr=true
stdout_logfile=/var/www/worker.log
stopwaitsecs=3600
EOF
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
sudo service supervisor restart



# COMPLETE
clear
echo "${bggreen}${black}${bold}"
echo "Cipi installation has been completed..."
echo "${reset}"
sleep 1s



# SETUP COMPLETE MESSAGE
clear
echo "***********************************************************"
echo "                    SETUP COMPLETE "
echo "***********************************************************"
echo ""
echo " SSH root user: cipi"
echo " SSH root pass: $USERPASSWORD"
echo " MySQL root user: cipi"
echo " MySQL root pass: $DATABASEPASSWORD"
echo ""
echo " To manage your server visit: "
echo " https://cipi-$SERVERIPWITHDASH$IPDOMAIN/panel"
echo " Default credentials are: admin@cipi.sh / C1p1P4n3!#4.sh"
echo ""
echo " If panel is not available via HTTPS, try to run:"
echo " certbot --nginx -d cipi-$SERVERIPWITHDASH$IPDOMAIN --non-interactive --agree-tos --register-unsafely-without-email"
echo ""
echo "***********************************************************"
echo "          DO NOT LOSE AND KEEP SAFE THIS DATA"
echo "***********************************************************"
