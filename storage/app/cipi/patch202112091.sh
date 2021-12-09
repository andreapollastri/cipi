# NGINX RELOAD FIX
sudo echo 'StartLimitBurst=0' >> /usr/lib/systemd/system/user@.service
sudo systemctl daemon-reload

# PHP 8.1
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
sudo apt-get -y install php-dev php-pear
sudo apt-get -y install php-dev php-pear

# NODE 16
sudo curl -sL https://deb.nodesource.com/setup_16.x | sudo bash
sudo apt-get -y install --only-upgrade nodejs
