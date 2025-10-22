#!/bin/bash

#############################################
# Nginx Management Functions
#############################################

NGINX_SITES_AVAILABLE="/etc/nginx/sites-available"
NGINX_SITES_ENABLED="/etc/nginx/sites-enabled"

# Create Nginx configuration
create_nginx_config() {
    local username=$1
    local domain=$2
    local php_version=$3
    
    local server_name="${domain:-$username}"
    local root_path="/home/$username/wwwroot/public"
    local log_path="/home/$username/logs"
    local php_socket="/var/run/php/php${php_version}-fpm-${username}.sock"
    
    cat > "${NGINX_SITES_AVAILABLE}/${username}" <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name ${server_name};
    root ${root_path};
    
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";
    
    client_body_timeout 10s;
    client_header_timeout 10s;
    client_max_body_size 256M;
    
    index index.html index.php;
    charset utf-8;
    server_tokens off;
    
    access_log ${log_path}/access.log;
    error_log ${log_path}/error.log;
    
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
    
    error_page 404 /index.php;
    
    location ~ \.php\$ {
        fastcgi_pass unix:${php_socket};
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF
    
    # Enable site
    ln -sf "${NGINX_SITES_AVAILABLE}/${username}" "${NGINX_SITES_ENABLED}/${username}"
}

# Update Nginx configuration with domain and aliases
update_nginx_domain() {
    local username=$1
    local domain=$2
    local aliases=$3
    
    local config_file="${NGINX_SITES_AVAILABLE}/${username}"
    
    if [ ! -f "$config_file" ]; then
        return 1
    fi
    
    # Build server_name line
    local server_names="$domain"
    if [ -n "$aliases" ]; then
        server_names="$domain $aliases"
    fi
    
    # Replace server_name line
    sed -i "s/server_name .*/server_name $server_names;/" "$config_file"
}

# Add SSL to Nginx configuration
add_ssl_to_nginx() {
    local username=$1
    local domain=$2
    local aliases=$3
    local php_version=$4
    
    local root_path="/home/$username/wwwroot/public"
    local log_path="/home/$username/logs"
    local php_socket="/var/run/php/php${php_version}-fpm-${username}.sock"
    
    local server_names="$domain"
    if [ -n "$aliases" ]; then
        server_names="$domain $aliases"
    fi
    
    cat > "${NGINX_SITES_AVAILABLE}/${username}" <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name ${server_names};
    return 301 https://\$server_name\$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name ${server_names};
    root ${root_path};
    
    ssl_certificate /etc/letsencrypt/live/${domain}/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/${domain}/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";
    
    client_body_timeout 10s;
    client_header_timeout 10s;
    client_max_body_size 256M;
    
    index index.html index.php;
    charset utf-8;
    server_tokens off;
    
    access_log ${log_path}/access.log;
    error_log ${log_path}/error.log;
    
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
    
    error_page 404 /index.php;
    
    location ~ \.php\$ {
        fastcgi_pass unix:${php_socket};
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF
}

# Delete Nginx configuration
delete_nginx_config() {
    local username=$1
    
    rm -f "${NGINX_SITES_ENABLED}/${username}"
    rm -f "${NGINX_SITES_AVAILABLE}/${username}"
}

# Test Nginx configuration
nginx_test() {
    nginx -t 2>&1
}

# Reload Nginx
nginx_reload() {
    if nginx_test > /dev/null 2>&1; then
        systemctl reload nginx
        return $?
    else
        echo -e "${RED}Error: Nginx configuration test failed${NC}"
        nginx_test
        return 1
    fi
}

# Restart Nginx
nginx_restart() {
    systemctl restart nginx
    return $?
}

