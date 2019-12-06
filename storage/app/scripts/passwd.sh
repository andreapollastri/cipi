#!/bin/bash

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
  -p | --pass )
      shift
      PASS=$1
      ;;
  -dbp | --dbpass )
      shift
      DBPASS=$1
      ;;
  -dbop | --dboldpass )
      shift
      DBOLDPASS=$1
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
sudo mysqladmin -u $USER -p$DBOLDPASS password $DBPASS

#RESUME
clear
echo "###CIPI###Ok"
