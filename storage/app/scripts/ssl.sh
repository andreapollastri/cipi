#!/usr/bin/env bash

DOMAIN=

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

#SSL CERTIFICATE
sudo certbot --nginx certonly --noninteractive --nginx --agree-tos --register-unsafely-without-email --expand -d $DOMAIN

CRON=/cipi/certbot_renew_$DOMAIN.sh
touch $CRON
cat > "$CRON" <<EOF
sudo certbot --nginx certonly --noninteractive --nginx --agree-tos --register-unsafely-without-email --expand -d $DOMAIN --post-hook "systemctl restart nginx.service"
EOF
TASK=/etc/cron.d/certbot_renew_$DOMAIN.crontab
touch $TASK
cat > "$TASK" <<EOF
0 1 * * * $DOMAIN /cipi/certbot_renew_$DOMAIN.sh
EOF
crontab $TASK

sudo systemctl restart nginx.service

#RESUME
clear
echo "###CIPI###Ok"
