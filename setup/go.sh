#!/bin/bash

ID=$(grep -oP '(?<=^ID=).+' /etc/os-release | tr -d '"')
VERSION=$(grep -oP '(?<=^VERSION_ID=).+' /etc/os-release | tr -d '"')
if [ "$ID" = "ubuntu" ]; then
    case $VERSION in
        18.04)
            clear
            wget -O - https://raw.githubusercontent.com/andreapollastri/cipi/develop/setup/18.sh | bash
            break
            ;;
        20.04)
            clear
            wget -O - https://raw.githubusercontent.com/andreapollastri/cipi/develop/setup/20.sh | bash
            break
            ;;
        *)
            clear
            echo "You have to run this script on Ubuntu 18.04 LTS or Ubuntu 20.04 LTS"
            exit 1;
            break
            ;;
    esac
else
    clear
    echo "You have to run this script on Ubuntu 18.04 LTS or Ubuntu 20.04 LTS"
    exit 1
fi
