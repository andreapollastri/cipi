
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
sudo certbot --apache -d $DOMAIN --non-interactive --agree-tos --email admin@admin.com
