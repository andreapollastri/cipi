#!/bin/bash
USER=
PASS=$(openssl rand -base64 32)
DBPASS=$(openssl rand -base64 32)
# Check if user is root
if [ $(id -u) != "0" ]; then
    echo "Error: You must be root to run this script."
    exit 1
fi
while [ -n "$1" ] ; do
      case $1 in
      -u | --user )
              shift
              USER=$1
              ;;
      * )
              echo "ERROR: Unknown option: $1"
              exit -1
              ;;
      esac
      shift
done
#CHANGE LINUX USER PASSWORD
echo "$USER:$PASS"| sudo chpasswd
#CHANGE MYSQL PASSWORD
if [ -f "/cipi/$USER" ]
then
    DBOLDPASS=$(for word in $(cat /cipi/$USER); do echo $word; done)
    sudo mysqladmin -u $USER -p'$DBOLDPASS' password '$DBPASS'
    #FINAL MESSAGGE
    clear
    echo "###################################################################################"
    echo "                              USER PASSWORDS CHANGED "
    echo "###################################################################################"
    echo ""
    echo "SFTP/SSH User / Pass: $USER / $PASS"
    echo "MySql User / Pass: $USER / $DBPASS"
    echo ""
    echo "                       >>>>> DO NOT LOSE THIS DATA! <<<<<"
    echo ""
    echo "###################################################################################"
    echo ""
else
    #FINAL MESSAGGE
    clear
    echo "###################################################################################"
    echo "                               USER PASSWORD CHANGED "
    echo "###################################################################################"
    echo ""
    echo "SFTP/SSH User / Pass: $USER / $PASS"
    echo ""
    echo "                       >>>>> DO NOT LOSE THIS DATA! <<<<<"
    echo ""
    echo "###################################################################################"
    echo ""
fi
