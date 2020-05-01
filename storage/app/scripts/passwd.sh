#!/bin/bash

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


echo "$USER:$PASS"| sudo chpasswd


sudo mysqladmin -u $USER -p$DBOLDPASS password $DBPASS


clear
echo "###CIPI###Ok"
