#!/bin/bash

#START
clear
echo "Installation is started... It may take some time! Hold on :)"
sleep 5s
echo -e "\n"

#VARS
IP=$(dig +short myip.opendns.com @resolver1.opendns.com)
USER=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 12 | head -n 1)
PASS=$(openssl rand -base64 32)
DBPASS=$(openssl rand -base64 32)
clear
echo "Preconfiguration: OK!"
sleep 3s
echo -e "\n"

#CIPI CORE
sudo mkdir /cipi/
sudo mkdir /cipi/html/
wget https://raw.githubusercontent.com/andreapollastri/cipi/master/host-add.sh -O /cipi/host-add.sh
wget https://raw.githubusercontent.com/andreapollastri/cipi/master/host-del.sh -O /cipi/host-del.sh
wget https://raw.githubusercontent.com/andreapollastri/cipi/master/ssl.sh -O /cipi/ssl.sh
wget https://raw.githubusercontent.com/andreapollastri/cipi/master/passwd.sh -O /cipi/passwd.sh
wget https://raw.githubusercontent.com/andreapollastri/cipi/master/alias-add.sh -O /cipi/alias-add.sh
wget https://raw.githubusercontent.com/andreapollastri/cipi/master/alias-del.sh -O /cipi/alias-del.sh
wget https://raw.githubusercontent.com/andreapollastri/cipi/master/linux.png -O /cipi/html/linux.png
DBRFILE=/cipi/DBR
sudo touch $DBRFILE
sudo cat > "$DBRFILE" <<EOF
$DBPASS
EOF
sudo chmod o-r /cipi
clear
echo "Core download: OK!"
sleep 3s
echo -e "\n"

#ALIAS
shopt -s expand_aliases
alias ll='ls -alF'
clear
echo "Alias settings: OK!"
sleep 3s
echo -e "\n"

#NEWROOT USER
sudo useradd -m -s /bin/bash $USER
echo "$USER:$PASS"|chpasswd
sudo usermod -aG sudo $USER
clear
echo "New root user: OK!"
sleep 3s
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
sudo apt-get -y install rpl dos2unix fail2ban openssl apache2 php7.3 php7.3-common php7.3-cli php7.3-fpm php-pear php7.3-curl php7.3-dev php7.3-gd php7.3-mbstring php-gettext php7.3-zip php7.3-mysql php7.3-xml libmcrypt-dev mysql-client
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

#PHPMYADMIN INSTALLATION
set -euo pipefail
IFS=$'\n\t'
sudo apt-get update
sudo DEBIAN_FRONTEND=noninteractive apt-get -yq install phpmyadmin
sudo service apache2 restart
sudo apt-get clean
sudo ln -s /etc/phpmyadmin/apache.conf /etc/apache2/conf-available/phpmyadmin.conf
sudo a2enconf phpmyadmin.conf
sudo service apache2 reload
clear
echo "phpmyadmin installation: OK!"
sleep 3s
echo -e "\n"

#DEFAULT VIRTUALHOST
BASE=/cipi/html/index.html
sudo touch $BASE
sudo cat > "$BASE" <<EOF
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
            font-size: 10px;
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
            Server Up!
        </div>
        <div class="links">
            <a href="#">$IP</a>
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
sudo service apache2 restart

sudo unlink /etc/apache2/sites-available/000-default.conf
CONF=/etc/apache2/sites-available/000-default.conf
sudo touch $CONF

sudo cat > "$CONF" <<EOF
<VirtualHost *:80>
        ServerAdmin webmaster@localhost
        DocumentRoot /cipi/html
        <Directory />
          Order allow,deny
          Options FollowSymLinks
          Allow from all
          AllowOverRide All
          Require all granted
          SetOutputFilter DEFLATE
        </Directory>
        <Directory /cipi/html>
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

BASE=/etc/apache2/sites-available/base.conf
sudo touch $BASE

sudo cat > "$BASE" <<EOF
<VirtualHost $IP:80>
        ServerAdmin webmaster@localhost
        DocumentRoot /cipi/html
        <Directory />
          Order allow,deny
          Options FollowSymLinks
          Allow from all
          AllowOverRide All
          Require all granted
          SetOutputFilter DEFLATE
        </Directory>
        <Directory /cipi/html>
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
sudo a2ensite base.conf
sudo service apache2 reload
clear
echo "Default virtualhost: OK!"
sleep 3s
echo -e "\n"

#LET'S ENCRYPT
sudo add-apt-repository -y ppa:certbot/certbot
sudo apt-get update
sudo apt-get -y install python-certbot-apache
sudo service apache2 restart
clear
echo "Let's Encrypt installation: OK!"
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

#GIT INSTALL
sudo apt-get update
sudo apt-get -y install git
clear
echo "GIT installation: OK!"
sleep 3s
echo -e "\n"

#SSH AND ROOT ACCESS CONFIGURATION
PORT=$(( ((RANDOM<<15)|RANDOM) % 63001 + 2000 ))
sudo rpl -i -w "# Port 22" "Port 22" /etc/ssh/sshd_config
sudo rpl -i -w "#Port 22" "Port 22" /etc/ssh/sshd_config
sudo rpl -i -w "Port 22" "Port $PORT" /etc/ssh/sshd_config
sudo rpl -i -w "PermitRootLogin yes" "PermitRootLogin no" /etc/ssh/sshd_config
sudo service sshd restart
echo -e "\n"
clear
echo "SSH port configuration: OK!"
sleep 3s
echo -e "\n"

#OPTIMIZE
WELCOME=/etc/motd
sudo touch $WELCOME
sudo cat > "$WELCOME" <<EOF
  _____ _       _ _    _           _   _             _____                 _ 
 / ____(_)     (_| |  | |         | | (_)           |  __ \               | |
| |     _ _ __  _| |__| | ___  ___| |_ _ _ __   __ _| |__) __ _ _ __   ___| |
| |    | | '_ \| |  __  |/ _ \/ __| __| | '_ \ / _` |  ___/ _` | '_ \ / _ | |
| |____| | |_) | | |  | | (_) \__ | |_| | | | | (_| | |  | (_| | | | |  __| |
 \_____|_| .__/|_|_|  |_|\___/|___/\__|_|_| |_|\__, |_|   \__,_|_| |_|\___|_|
         | |                                    __/ |                        
         |_|                                   |___/                         
EOF
dos2unix /cipi/passwd.sh
dos2unix /cipi/host-add.sh
dos2unix /cipi/host-del.sh
dos2unix /cipi/alias-add.sh
dos2unix /cipi/alias-del.sh
dos2unix /cipi/ssl.sh
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
clear
echo "Optimization: OK!"
sleep 3s
echo -e "\n"

#FINAL MESSAGGE
clear
echo "SERVER IP: $IP"
echo "SSH PORT: $PORT"
echo "SFTP/SSH ROOT: $USER / $PASS"
echo "MYSQL ROOT: $DBPASS"
echo "PHPMYADMIN: http://$IP/phpmyadmin/"
