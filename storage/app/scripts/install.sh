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
if [ "$ID" = "ubuntu" ]; then
    case $VERSION in
        18.04)
            clear
            echo "Running on Ubuntu 18.04 LTS ;)"
            sleep 2s
            break
            ;;
        20.04)
            clear
            echo "Running on Ubuntu 20.04 LTS ;)"
            sleep 2s
            break
            ;;
        *)
            clear
            echo "You have to run this script on Ubuntu 18.04 LTS or Ubuntu 20.04 LTS"
            exit 1;
            break
            ;;
    esac
else
    clear
    echo "You have to run this script on Ubuntu 18.04 LTS or Ubuntu 20.04 LTS"
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



#CIPI START
clear
echo "Cipi remote configuration..."
sleep 3s

REMOTE=???
PORT=???
USER=???
PASS=???
DBPASS=???
SERVERCODE=???

sudo apt-get update
sudo apt-get -y install curl wget

curl --request GET --url $REMOTE/remote/start/$SERVERCODE

sudo mkdir /cipi/
wget $REMOTE/sh/ha/$SERVERCODE/ -O /cipi/host-add.sh
wget $REMOTE/sh/hd/$SERVERCODE/ -O /cipi/host-del.sh
wget $REMOTE/sh/aa/$SERVERCODE/ -O /cipi/alias-add.sh
wget $REMOTE/sh/ad/$SERVERCODE/ -O /cipi/alias-del.sh
wget $REMOTE/sh/pw/$SERVERCODE/ -O /cipi/passwd.sh
wget $REMOTE/sh/rt/$SERVERCODE/ -O /cipi/root.sh
wget $REMOTE/sh/st/ -O /cipi/status.sh
wget $REMOTE/sh/dy/ -O /cipi/deploy.sh
wget $REMOTE/sh/sc/ -O /cipi/ssl.sh
sudo chmod o-r /cipi

clear
echo "Cipi remote configuration: OK!"
sleep 3s



#SERVER BASIC CONFIGURATION
clear
echo "Server basic configuration..."
sleep 3s

sudo apt-get update
sudo apt-get -y install nano rpl sed zip unzip openssl expect dirmngr apt-transport-https lsb-release ca-certificates dnsutils dos2unix zsh htop

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

sudo dos2unix /cipi/deploy.sh
sudo dos2unix /cipi/passwd.sh
sudo dos2unix /cipi/root.sh
sudo dos2unix /cipi/status.sh
sudo dos2unix /cipi/ssl.sh
sudo dos2unix /cipi/host-add.sh
sudo dos2unix /cipi/host-del.sh
sudo dos2unix /cipi/alias-add.sh
sudo dos2unix /cipi/alias-del.sh

shopt -s expand_aliases
alias ll='ls -alF'

DATABASE=/cipi/dbroot
sudo touch $DATABASE
sudo cat > "$DATABASE" <<EOF
$DBPASS
EOF

clear
echo "Server basic configuration: OK!"
sleep 3s



#CIPI USER
clear
echo "User creation..."
sleep 3s

sudo pam-auth-update --package
sudo mount -o remount,rw /
sudo chmod 640 /etc/shadow
sudo useradd -m -s /bin/bash $USER
echo "$USER:$PASS"|sudo chpasswd
sudo usermod -aG sudo $USER

clear
echo "User creation: OK!"
sleep 3s
echo -e "\n"



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

echo "Firewall: OK!"
sleep 3s



#NGINX
clear
echo "nginix installation..."
sleep 3s

sudo apt-get -y install nginx
sudo systemctl start nginx.service
sudo rpl -i -w "http {" "http { limit_req_zone \$binary_remote_addr zone=one:10m rate=1r/s; fastcgi_read_timeout 300;" /etc/nginx/nginx.conf
sudo systemctl enable nginx.service

echo "nginx: OK!"
sleep 3s



#PHP
clear
echo "PHP installation..."
sleep 3s

sudo add-apt-repository -y ppa:ondrej/php
sudo apt-get update

sudo apt-get -y install php7.2-fpm
sudo apt-get -y install php7.2-common
sudo apt-get -y install php7.2-mbstring
sudo apt-get -y install php7.2-mysql
sudo apt-get -y install php7.2-xml
sudo apt-get -y install php7.2-zip
sudo apt-get -y install php7.2-bcmath
sudo apt-get -y install php7.2-imagick
PHPINI72=/etc/php/7.2/fpm/conf.d/cipi.ini
sudo touch $PHPINI72
sudo cat > "$PHPINI72" <<EOF
memory_limit = 256M
upload_max_filesize = 256M
post_max_size = 256M
max_execution_time = 180
max_input_time = 180
EOF
sudo service php7.2-fpm restart

sudo apt-get -y install php7.3-fpm
sudo apt-get -y install php7.3-common
sudo apt-get -y install php7.3-mbstring
sudo apt-get -y install php7.3-mysql
sudo apt-get -y install php7.3-xml
sudo apt-get -y install php7.3-zip
sudo apt-get -y install php7.3-bcmath
sudo apt-get -y install php7.3-imagick
PHPINI73=/etc/php/7.3/fpm/conf.d/cipi.ini
sudo touch $PHPINI73
sudo cat > "$PHPINI73" <<EOF
memory_limit = 256M
upload_max_filesize = 256M
post_max_size = 256M
max_execution_time = 180
max_input_time = 180
EOF
sudo service php7.3-fpm restart

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
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

sudo mkdir /etc/nginx/cipi/

sudo systemctl restart nginx.service

echo "PHP: OK!"
sleep 3s

#MYSQL
clear
echo "Mysql installation..."
sleep 3s

if [ "$VERSION" = "20.04" ]; then

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
CREATE USER 'cipi'@'%' IDENTIFIED BY '$DBPASS';
GRANT ALL PRIVILEGES ON *.* TO 'cipi'@'%' WITH GRANT OPTION;
FLUSH PRIVILEGES;
EOF

else

sudo debconf-set-selections <<< "mysql-server mysql-server/root_password password $DBPASS"
sudo debconf-set-selections <<< "mysql-server mysql-server/root_password_again password $DBPASS"
sudo apt-get -y install mysql-server mysql-client

fi

clear
echo "Mysql: OK!"
sleep 3s



#REDIS
clear
echo "Redis installation..."
sleep 3s

sudo apt install -y redis-server
sudo rpl -i -w "supervised no" "supervised systemd" /etc/redis/redis.conf
sudo systemctl restart redis.service

clear
echo "Redis: OK!"
sleep 3s



#LET'S ENCRYPT
clear
echo "Let's Encrypt installation..."
sleep 3s

if [ "$VERSION" = "20.04" ]; then
sudo snap install --beta --classic certbot
else
sudo add-apt-repository -y ppa:certbot/certbot
sudo apt-get -y install python-certbot-nginx
fi

clear
echo "Let's Encrypt: OK!"
sleep 3s



#GIT
clear
echo "Git installation..."
sleep 3s

sudo apt-get -y install git
sudo ssh-keygen -t rsa -C "git@github.com" -f /cipi/github -q -P ""

clear
echo "Git installation: OK!"
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



#SUPERVISOR
echo "Supervisor installation..."
sleep 3s

sudo apt-get -y install supervisor
service supervisor restart

clear
echo "Supervisor installation: OK!"
sleep 3s



#NODE
clear
echo "node.js & npm installation..."
sleep 3s

if [ "$VERSION" = "20.04" ]; then

curl -s https://deb.nodesource.com/gpgkey/nodesource.gpg.key | sudo apt-key add -
curl -sL https://deb.nodesource.com/setup_14.x | sudo -E bash -
NODE=/etc/apt/sources.list.d/nodesource.list
sudo unlink NODE
sudo touch $NODE
sudo cat > "$NODE" <<EOF
deb https://deb.nodesource.com/node_14.x focal main
deb-src https://deb.nodesource.com/node_14.x focal main
EOF
sudo apt-get update
sudo apt -y install nodejs
sudo apt -y install npm

else

curl -sL https://deb.nodesource.com/setup_12.x | sudo -E bash -
sudo apt-get -y install nodejs

fi

clear
echo "node.js & npm: OK!"
sleep 3s



#POSTFIX
clear
echo "Postfix installation..."
sleep 3s

sudo DEBIAN_FRONTEND=noninteractive apt-get install -yq postfix

clear
echo "Postfix installation: OK!"
sleep 3s
echo -e "\n"


#PHPMYADMIN
clear
echo "phpmyadmin installation..."
sleep 3s

sudo apt-get install -y yarn
sudo apt-get install -y php-curl
composer create-project phpmyadmin/phpmyadmin /var/www/html/pma
sudo mkdir /var/www/html/pma/tmp/
sudo chmod 777 /var/www/html/pma/tmp/
sudo mv /var/www/html/pma/config.sample.inc.php /var/www/html/pma/config.inc.php
sudo rpl -i -w "['blowfish_secret'] = '';" "['blowfish_secret'] = 'M12SQBq5JKVGA0qZ4ZhPBwfmb0hBYkMA';" /var/www/html/pma/config.inc.php

if [ "$VERSION" = "20.04" ]; then

DBNAME=phpmyadmin
DBUSER=cipi
/usr/bin/mysql -u cipi -p$DBPASS <<EOF
CREATE DATABASE IF NOT EXISTS $DBNAME;
use mysql;
CREATE USER $DBUSER@'%' IDENTIFIED BY '$DBPASS';
GRANT ALL PRIVILEGES ON $DBNAME.* TO $DBUSER@'%' WITH GRANT OPTION;
FLUSH PRIVILEGES;
EOF

else

DBNAME=phpmyadmin
DBUSER=root
/usr/bin/mysql -u root -p$DBPASS <<EOF
CREATE DATABASE IF NOT EXISTS $DBNAME;
CREATE USER $DBUSER@'localhost' IDENTIFIED BY '$DBPASS';
GRANT USAGE ON *.* TO '$DBUSER'@'localhost' IDENTIFIED BY '$DBPASS' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;
GRANT ALL PRIVILEGES ON $DBNAME.* TO $DBUSER@'localhost';
EOF

fi

clear
echo "phpmyadmin installation: OK!"
sleep 3s
echo -e "\n"


#CIPI PAGES
clear
echo "Cipi pages creation..."
sleep 3s

PING=/var/www/html/ping_$SERVERCODE.php
sudo touch $PING
sudo cat > "$PING" <<EOF
    UP!
EOF

STATUS=/var/www/html/status_$SERVERCODE.php
sudo touch $STATUS
sudo cat > "$STATUS" <<EOF
    <?php
    echo exec("sh /cipi/status.sh");
EOF

WELCOME=/var/www/html/index.php
sudo touch $WELCOME
sudo cat > "$WELCOME" <<EOF
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Server Up!</title>
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
    <style>
        html, body {
            background-color: #0F0F0F;
            color: #fff;
            font-family: 'Raleway', sans-serif;
            font-weight: 100;
            height: 100vh;
            margin: 0;
        }
        img {
            max-width: 600px;
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
            color: #fff;
            padding: 0 25px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .1rem;
            text-decoration: none;
            text-transform: uppercase;
        }
        .m-b-md {
            margin-bottom: 30px;
        }
        #particles-js canvas {
            position: absolute;
            width: 100%;
            height: 100%;
        }
        @media (max-width: 450px) {
            .title {
                font-size: 48px;
            }
            img {
                max-width: 300px;
            }
        }
    </style>
</head>
<body>
<div id="particles-js"></div>
<div class="flex-center position-ref full-height">
    <div class="content">
        <div class="title m-b-md">
            Hey...<br>This is Cipi ;)
        </div>
        <div class="links">
            <a href="https://cipi.sh">CLOUD CONTROL PANEL</a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
<script>
    (function () {
        particlesJS('particles-js',
            {
                "particles": {
                    "number": {
                        "value": 25,
                        "density": {
                            "enable": true,
                            "value_area": 800
                        }
                    },
                    "color": {
                        "value": "#ffffff"
                    },
                    "shape": {
                        "type": "circle",
                        "stroke": {
                            "width": 0,
                            "color": "#000000"
                        },
                        "polygon": {
                            "nb_sides": 5
                        },
                        "image": {
                            "src": "img/github.svg",
                            "width": 100,
                            "height": 100
                        }
                    },
                    "opacity": {
                        "value": 0.5,
                        "random": false,
                        "anim": {
                            "enable": false,
                            "speed": 1,
                            "opacity_min": 0.1,
                            "sync": false
                        }
                    },
                    "size": {
                        "value": 5,
                        "random": true,
                        "anim": {
                            "enable": false,
                            "speed": 40,
                            "size_min": 0.1,
                            "sync": false
                        }
                    },
                    "line_linked": {
                        "enable": true,
                        "distance": 150,
                        "color": "#ffffff",
                        "opacity": 0.4,
                        "width": 1
                    },
                    "move": {
                        "enable": true,
                        "speed": 6,
                        "direction": "none",
                        "random": false,
                        "straight": false,
                        "out_mode": "out",
                        "attract": {
                            "enable": false,
                            "rotateX": 600,
                            "rotateY": 1200
                        }
                    }
                },
                "retina_detect": true,
                "config_demo": {
                    "hide_card": false,
                    "background_color": "#b61924",
                    "background_image": "",
                    "background_position": "50% 50%",
                    "background_repeat": "no-repeat",
                    "background_size": "cover"
                }
            }
        );
    })();
</script>
</body>
</html>
EOF


clear
echo "Cipi pages creation: OK!"
sleep 3s
echo -e "\n"



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

sudo rpl -i -w "#PasswordAuthentication" "PasswordAuthentication" /etc/ssh/sshd_config
sudo rpl -i -w "# PasswordAuthentication" "PasswordAuthentication" /etc/ssh/sshd_config
sudo rpl -i -w "PasswordAuthentication no" "PasswordAuthentication yes" /etc/ssh/sshd_config
sudo rpl -i -w "PermitRootLogin yes" "PermitRootLogin no" /etc/ssh/sshd_config
sudo service sshd restart

curl --request GET --url $REMOTE/remote/finalize/$SERVERCODE

clear
echo "Cipi installation has been completed... Wait for your data!"
sleep 3s



#FINAL MESSAGGE
clear
echo "***********************************************************"
echo "                    SETUP COMPLETE"
echo "***********************************************************"
echo ""
echo "Use $REMOTE to manage your server."
echo ""
echo "If you need SSH into this server:"
echo "Cipi SSH username: $USER"
echo "Cipi SSH password: $PASS"
echo "Cipi MySql root/cipi pass: $DBPASS"
echo ""
echo "***********************************************************"
echo "          DO NOT LOSE AND KEEP SAFE THIS DATA"
echo "***********************************************************"
