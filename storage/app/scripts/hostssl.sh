#!/usr/bin/env bash

DOMAIN=

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

#SSL CERTIFICATE
sudo certbot --apache -d $DOMAIN --non-interactive --agree-tos --email ???

CRON=/cipi/certbot_renew_$DOMAIN.sh
touch $CRON
cat > "$CRON" <<EOF
sudo certbot certonly --noninteractive --apache --agree-tos --email ??? --d $DOMAIN --post-hook "service apache2 reload"
EOF
TASK=/etc/cron.d/certbot_renew_$DOMAIN.crontab
touch $TASK
cat > "$TASK" <<EOF
0 1 * * * $DOMAIN /cipi/certbot_renew_$DOMAIN.sh
EOF
crontab /etc/cron.d/certbot_renew_$USER_NAME.crontab

#RESUME
clear
echo "###CIPI###Ok"