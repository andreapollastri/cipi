#!/bin/bash

while [ -n "$1" ] ; do
    case $1 in
    -p | --pass )
        shift
        PASS=$1
        ;;
    * )
        echo "ERROR: Unknown option: $1"
        exit -1
        ;;
    esac
    shift
done


echo "cipi:$PASS"| sudo chpasswd


clear
echo "###CIPI###Ok"
