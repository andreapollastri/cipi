#!/usr/bin/env bash

DBROOT=???
IP=???

BASE_PATH=
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
            -u | --user )
                    shift
                    USER_NAME=$1
                    ;;
            -p | --pass )
                    shift
                    PASSWORD=$1
                    ;;
            -dbp | --dbpass )
                    shift
                    DBPASS=$1
                    ;;
            -b |  --base )
                    shift
                    BASE_PATH=$1
                    ;;
            -ai |  --autoinstall )
                    shift
                    AUTO_INSTALL=$1
                    ;;
            * )
                    echo "ERROR: Unknown option: $1"
                    exit -1
                    ;;
            esac
            shift
done

#AUTOINSTALL BASE_PATH MOD
if [ "$AUTO_INSTALL" = "laravel" ]; then
    BASE_PATH="laravel/public"
fi
if [ "$AUTO_INSTALL" = "wordpress" ]; then
    BASE_PATH="wordpress"
fi

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

mkdir /home/$USER_NAME/web/$BASE_PATH
cat > "$CONF" <<EOF
<VirtualHost *:80>
    ServerName $DOMAIN
        ServerAdmin webmaster@localhost
        DocumentRoot /home/$USER_NAME/web/$BASE_PATH
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
        <Directory /home/$USER_NAME/web/$BASE_PATH>
                Order allow,deny
                Options FollowSymLinks
                Allow from all
                AllowOverRide All
                Require all granted
                SetOutputFilter DEFLATE
        </Directory>
</VirtualHost>
EOF

HTACCESS=/home/$USER_NAME/web/$BASE_PATH/.htaccess
sudo touch $HTACCESS
sudo cat > "$HTACCESS" <<EOF
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
EOF


BASE=/home/$USER_NAME/web/$BASE_PATH/index.php
sudo touch $BASE
sudo cat > "$BASE" <<EOF
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Coming soon!</title>
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
            Coming soon...
        </div>
        <div class="links">
            <a href="https://cipi.sh">CIPI CONTROL PANEL</a>
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

#RESTART
sudo a2ensite $USER_NAME.conf
sudo systemctl restart apache2
sudo service apache2 restart

#MYSQL USER AND DB
DBNAME=$USER_NAME
DBUSER=$USER_NAME
/usr/bin/mysql -u root -p$DBROOT <<EOF
CREATE DATABASE IF NOT EXISTS $DBNAME;
CREATE USER $DBUSER@'localhost' IDENTIFIED BY '$DBPASS';
GRANT USAGE ON *.* TO '$DBUSER'@'localhost' IDENTIFIED BY '$DBPASS' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;
GRANT ALL PRIVILEGES ON $DBNAME.* TO $DBUSER@'localhost';
EOF

#RESUME
clear
echo "###CIPI###Ok"


#LARAVEL
if [ "$AUTO_INSTALL" = "laravel" ]; then
    cd /home/$USER_NAME/web/
    rm -rf $BASE_PATH
    composer create-project laravel/laravel laravel
    find . -type f -exec chmod 644 {} \;
    find . -type d -exec chmod 755 {} \;
    chmod 777 -R storage
fi


#WORDPRESS
if [ "$AUTO_INSTALL" = "wordpress" ]; then
    cd /home/$USER_NAME/web/
    rm -rf $BASE_PATH
    composer create-project johnpbloch/wordpress .
    WPSalts=$(wget https://api.wordpress.org/secret-key/1.1/salt/ -q -O -)
    TablePrefx=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 9 | head -n 1)_
    cat <<EOF > wordpress/wp-config-sample.php
    <?php
        define('DB_NAME', '$DBNAME');
        define('DB_USER', '$DBUSER');
        define('DB_PASSWORD', '$DBPASS');
        define('DB_HOST', 'localhost');
        define('DB_CHARSET', 'utf8');
        define('DB_COLLATE', '');
        #define('WP_SITEURL', 'http://$DOMAIN/' );
        #define('WP_HOME', 'http://$DOMAIN/' );
        #define('ALTERNATE_WP_CRON', true );
        #define('DISABLE_WP_CRON', 'true');
        #define('WP_CRON_LOCK_TIMEOUT', 900);
        #define('AUTOSAVE_INTERVAL', 300);
        #define('WP_MEMORY_LIMIT', '256M' );
        #define('FS_CHMOD_DIR', ( 0755 & ~ umask() ) );
        #define('FS_CHMOD_FILE', ( 0644 & ~ umask() ) );
        #define('WP_ALLOW_REPAIR', true);
        #define('FORCE_SSL_ADMIN', false);
        #define('AUTOMATIC_UPDATER_DISABLED', false);
        #define('WP_AUTO_UPDATE_CORE', true);
        $WPSalts
        \$table_prefix = '$TablePrefx';
        define('WP_DEBUG', false);
        if ( !defined('ABSPATH') )
            define('ABSPATH', dirname(__FILE__) . '/');
        require_once(ABSPATH . 'wp-settings.php');
EOF
    mv wordpress/wp-config-sample.php wordpress/wp-config.php
    find . -type f -exec chmod 644 {} \;
    find . -type d -exec chmod 755 {} \;
    chmod 777 -R wordpress/wp-content/uploads/
fi


#GIT INIT
if [ "$AUTO_INSTALL" = "git" ]; then
    sudo mkdir /home/$USER_NAME/git/
    sudo cp /cipi/deploy.sh /home/$USER_NAME/git/deploy.sh
    sudo rpl -q "###CIPI-USER###" "$USER_NAME" /home/$USER_NAME/deploy.sh
    sudo chown -R $USER_NAME:$USER_NAME /home/$USER_NAME/git/
fi

#PERMISSIONS
chown -R $USER_NAME:$USER_NAME /home/$USER_NAME/web/
chown -R $USER_NAME:$USER_NAME /home/$USER_NAME/web/$BASE_PATH/
