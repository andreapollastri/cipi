#!/bin/bash

while [ -n "$1" ] ; do
    case $1 in
    -u | --user* )
        shift
        USER_NAME=$1
        ;;
    -p | --port* )
        shift
        PORT=$1
        ;;
    -r | --path )
        shift
        ROUTE=$1
        ;;
    * )
        echo "ERROR: Unknown option: $1"
        exit -1
        ;;
    esac
    shift
done


cd /home/$USER_NAME/web
npm install
sudo cat > ecosystem.config.js <<EOF
module.exports = {
  apps : [{
    name   : "$USER_NAME",
    script : "$ROUTE",
    instances : "1",
    exec_mode : "fork",
    env: {
      NODE_ENV: "production",
      PORT: $PORT,
    },
    log_file: "/home/$USER_NAME/log/node.log",
    max_restarts: 3
  }]
}

EOF

pm2 start ecosystem.config.js

#pm2 start npx --name "$USER_NAME" -- next -p $PORT -l "/home/$USER_NAME/log"
