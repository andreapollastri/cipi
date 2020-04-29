#!/usr/bin/env bash

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
            * )
                    echo "ERROR: Unknown option: $1"
                    exit -1
                    ;;
            esac
            shift
done

sudo unlink /etc/nginx/sites-available/$DOMAIN.conf
sudo unlink /etc/nginx/sites-enabled/$DOMAIN.conf
sudo systemctl restart nginx.service


$ssh->setTimeout(60);
$ssh->exec('echo '.$alias->server->password.' | sudo -S unlink /etc/apache2/sites-available/'.$alias->application->username.'.conf');
$ssh->exec('echo '.$alias->server->password.' | sudo -S wget '.env('APP_URL').'/storage/'.$alias->application->username.'.conf  -O /etc/apache2/sites-available/'.$alias->application->username.'.conf');
$ssh->exec('echo '.$alias->server->password.' | sudo -S a2ensite '.$alias->application->username.'.conf');
$ssh->exec('echo '.$alias->server->password.' | sudo -S unlink /etc/cron.d/certbot_renew_'.$alias->domain.'.crontab');
$ssh->exec('echo '.$alias->server->password.' | sudo -S unlink /cipi/certbot_renew_'.$alias->domain.'.sh');
$ssh->exec('echo '.$alias->server->password.' | sudo -S service apache2 reload');
$ssh->exec('echo '.$alias->server->password.' | sudo -S systemctl reload apache2');
