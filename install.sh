#!/bin/bash

#############################################
# Cipi Installer
# Version: 4.0.0
# Author: Andrea Pollastri
# License: MIT
#############################################

set -e

# Configuration
BUILD="4.0.0"
REPO="andreapollastri/cipi"
BRANCH="${1:-latest}"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'
BOLD='\033[1m'

# Logo
show_logo() {
    clear
    echo -e "${GREEN}${BOLD}"
    echo " ██████ ██ ██████  ██"
    echo "██      ██ ██   ██ ██"
    echo "██      ██ ██████  ██"
    echo "██      ██ ██      ██"
    echo " ██████ ██ ██      ██"
    echo ""
    echo "Installation started..."
    echo -e "${NC}"
    sleep 2
}

# Check Ubuntu version
check_os() {
    clear
    echo -e "${GREEN}${BOLD}OS Check...${NC}"
    sleep 1
    
    if [ ! -f /etc/os-release ]; then
        echo -e "${RED}Error: Cannot detect OS${NC}"
        exit 1
    fi
    
    . /etc/os-release
    
    if [ "$ID" != "ubuntu" ]; then
        echo -e "${RED}Error: Cipi requires Ubuntu${NC}"
        exit 1
    fi
    
    # Check version (24.04 or higher)
    version_check=$(echo "$VERSION_ID >= 24.04" | bc)
    if [ "$version_check" -ne 1 ]; then
        echo -e "${RED}Error: Cipi requires Ubuntu 24.04 LTS or higher${NC}"
        echo "Current version: $VERSION_ID"
        exit 1
    fi
    
    echo -e "${GREEN}✓ Ubuntu $VERSION_ID detected${NC}"
}

# Check root
check_root() {
    clear
    echo -e "${GREEN}${BOLD}Permission Check...${NC}"
    sleep 1
    
    if [ "$(id -u)" != "0" ]; then
        echo -e "${RED}Error: Cipi must be run as root${NC}"
        echo "Please run: sudo bash install.sh"
        exit 1
    fi
    
    echo -e "${GREEN}✓ Running as root${NC}"
}

# Install basic packages
install_basics() {
    clear
    echo -e "${GREEN}${BOLD}Installing Basic Packages...${NC}"
    sleep 1
    
    apt-get update
    apt-get install -y software-properties-common curl wget nano vim git \
        sed zip unzip openssl expect apt-transport-https \
        ca-certificates gnupg lsb-release jq bc python3-pip
    
    # Install AWS CLI v2
    cd /tmp
    curl "https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" -o "awscliv2.zip"
    unzip -q awscliv2.zip
    ./aws/install
    rm -rf awscliv2.zip aws
    cd -
    
    echo -e "${GREEN}✓ Basic packages installed${NC}"
}

# Setup MOTD
setup_motd() {
    clear
    echo -e "${GREEN}${BOLD}Setting up MOTD...${NC}"
    sleep 1
    
    cat > /etc/motd <<'EOF'

 ██████ ██ ██████  ██
██      ██ ██   ██ ██
██      ██ ██████  ██
██      ██ ██      ██
 ██████ ██ ██      ██

Welcome to Cipi!
Type 'cipi help' for available commands.

EOF
    
    echo -e "${GREEN}✓ MOTD configured${NC}"
}

# Setup swap
setup_swap() {
    clear
    echo -e "${GREEN}${BOLD}Setting up SWAP...${NC}"
    sleep 1
    
    if [ ! -f /var/swap.1 ]; then
        dd if=/dev/zero of=/var/swap.1 bs=1M count=2048
        mkswap /var/swap.1
        swapon /var/swap.1
        echo '/var/swap.1 none swap sw 0 0' >> /etc/fstab
    fi
    
    echo -e "${GREEN}✓ SWAP configured${NC}"
}

# Setup editor
setup_editor() {
    clear
    echo -e "${GREEN}${BOLD}Configuring default editor...${NC}"
    sleep 1
    
    # Set nano as default editor system-wide
    update-alternatives --set editor /usr/bin/nano
    
    # Add to profile for all users
    cat > /etc/profile.d/cipi-editor.sh <<'EOF'
export EDITOR=nano
export VISUAL=nano
EOF
    chmod +x /etc/profile.d/cipi-editor.sh
    
    # Export for current session
    export EDITOR=nano
    export VISUAL=nano
    
    echo -e "${GREEN}✓ Nano configured as default editor${NC}"
}

# Install nginx
install_nginx() {
    clear
    echo -e "${GREEN}${BOLD}Installing Nginx...${NC}"
    sleep 1
    
    apt-get install -y nginx
    systemctl start nginx
    systemctl enable nginx
    
    # Configure nginx - Hide version and optimize
    sed -i 's/# server_names_hash_bucket_size.*/server_names_hash_bucket_size 64;/' /etc/nginx/nginx.conf
    sed -i 's/# server_tokens off;/server_tokens off;/' /etc/nginx/nginx.conf
    
    # Add optimizations to http block if not present
    if ! grep -q "client_max_body_size" /etc/nginx/nginx.conf; then
        sed -i '/http {/a \    client_max_body_size 100M;' /etc/nginx/nginx.conf
    fi
    
    if ! grep -q "fastcgi_read_timeout" /etc/nginx/nginx.conf; then
        sed -i '/http {/a \    fastcgi_read_timeout 300;' /etc/nginx/nginx.conf
    fi
    
    if ! grep -q "limit_req_zone" /etc/nginx/nginx.conf; then
        sed -i '/http {/a \    limit_req_zone $binary_remote_addr zone=one:10m rate=1r/s;' /etc/nginx/nginx.conf
    fi
    
    # Optimize worker processes
    CPU_CORES=$(nproc)
    sed -i "s/worker_processes.*/worker_processes $CPU_CORES;/" /etc/nginx/nginx.conf
    
    # Optimize worker connections
    sed -i 's/worker_connections.*/worker_connections 2048;/' /etc/nginx/nginx.conf
    
    # Enable gzip compression if not already enabled
    if ! grep -q "gzip_vary on;" /etc/nginx/nginx.conf; then
        sed -i '/gzip on;/a \    gzip_vary on;' /etc/nginx/nginx.conf
        sed -i '/gzip_vary on;/a \    gzip_proxied any;' /etc/nginx/nginx.conf
        sed -i '/gzip_proxied any;/a \    gzip_comp_level 6;' /etc/nginx/nginx.conf
        sed -i '/gzip_comp_level 6;/a \    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/rss+xml font/truetype font/opentype application/vnd.ms-fontobject image/svg+xml;' /etc/nginx/nginx.conf
    fi
    
    # Create default config
    cat > /etc/nginx/sites-available/default <<'EOF'
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    root /var/www/html;
    index index.html index.php;
    server_name _;
    server_tokens off;
    
    client_max_body_size 100M;
    
    location / {
        try_files $uri $uri/ =404;
    }
}
EOF
    
    # Create landing page
    cat > /var/www/html/index.html <<'HTMLEOF'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>It Works</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Inter', 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #1a1a1a;
        }

        .container {
            background: #ffffff;
            border-radius: 24px;
            padding: 60px 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            text-align: center;
        }

        .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            margin: 0 auto 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 2px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        h1 {
            font-size: 42px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 16px;
            letter-spacing: -0.5px;
        }

        .subtitle {
            font-size: 18px;
            color: #6b7280;
            line-height: 1.6;
        }

        @media (max-width: 640px) {
            .container {
                padding: 40px 24px;
            }

            h1 {
                font-size: 32px;
            }

            .subtitle {
                font-size: 16px;
            }

            .logo {
                width: 60px;
                height: 60px;
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">✓</div>
        
        <h1>It Works</h1>
        <p class="subtitle">
            Your server is up and running.
        </p>
    </div>
</body>
</html>
HTMLEOF
    
    chmod 644 /var/www/html/index.html
    
    systemctl reload nginx
    
    echo -e "${GREEN}✓ Nginx installed${NC}"
}

# Install fail2ban and firewall
install_firewall() {
    clear
    echo -e "${GREEN}${BOLD}Installing Fail2ban & Firewall...${NC}"
    sleep 1
    
    apt-get install -y fail2ban
    
    cat > /etc/fail2ban/jail.local <<'EOF'
[DEFAULT]
bantime = 3600
banaction = iptables-multiport
maxretry = 5

[sshd]
enabled = true
port = ssh
logpath = /var/log/auth.log
maxretry = 5
EOF
    
    systemctl restart fail2ban
    systemctl enable fail2ban
    
    # UFW
    ufw --force enable
    ufw allow 22/tcp
    ufw allow 80/tcp
    ufw allow 443/tcp
    ufw reload
    
    echo -e "${GREEN}✓ Firewall configured${NC}"
}

# Install PHP 8.4
install_php() {
    clear
    echo -e "${GREEN}${BOLD}Installing PHP 8.4...${NC}"
    sleep 1
    
    add-apt-repository -y ppa:ondrej/php
    apt-get update
    
    # Install PHP 8.4 and extensions
    apt-get install -y php8.4-fpm php8.4-common php8.4-cli php8.4-curl \
        php8.4-bcmath php8.4-mbstring php8.4-mysql php8.4-sqlite3 \
        php8.4-pgsql php8.4-redis php8.4-memcached php8.4-zip \
        php8.4-xml php8.4-soap php8.4-gd php8.4-imagick php8.4-intl
    
    # Configure PHP
    cat > /etc/php/8.4/fpm/conf.d/cipi.ini <<'EOF'
memory_limit = 256M
upload_max_filesize = 256M
post_max_size = 256M
max_execution_time = 300
max_input_time = 300
EOF
    
    systemctl restart php8.4-fpm
    systemctl enable php8.4-fpm
    
    # Set PHP 8.4 as default CLI
    update-alternatives --set php /usr/bin/php8.4
    
    echo -e "${GREEN}✓ PHP 8.4 installed${NC}"
}

# Install Composer
install_composer() {
    clear
    echo -e "${GREEN}${BOLD}Installing Composer...${NC}"
    sleep 1
    
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --no-interaction --install-dir=/usr/local/bin --filename=composer
    php -r "unlink('composer-setup.php');"
    
    echo -e "${GREEN}✓ Composer installed${NC}"
}

# Install MySQL
install_mysql() {
    clear
    echo -e "${GREEN}${BOLD}Installing MySQL...${NC}"
    sleep 1
    
    # Generate root password
    MYSQL_ROOT_PASSWORD=$(openssl rand -base64 24 | sha256sum | base64 | head -c 32)
    
    # Install MySQL
    apt-get install -y mysql-server
    
    # Secure MySQL installation
    mysql <<EOF
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '${MYSQL_ROOT_PASSWORD}';
DELETE FROM mysql.user WHERE User='';
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
FLUSH PRIVILEGES;
EOF
    
    systemctl enable mysql
    
    # Save root password
    mkdir -p /etc/cipi
    chmod 700 /etc/cipi
    echo "{\"mysql_root_password\": \"${MYSQL_ROOT_PASSWORD}\"}" > /etc/cipi/config.json
    chmod 600 /etc/cipi/config.json
    
    echo -e "${GREEN}✓ MySQL installed${NC}"
    echo -e "${CYAN}MySQL root password saved in /etc/cipi/config.json${NC}"
}

# Install Redis
install_redis() {
    clear
    echo -e "${GREEN}${BOLD}Installing Redis...${NC}"
    sleep 1
    
    apt-get install -y redis-server
    
    # Configure Redis
    sed -i 's/^supervised no/supervised systemd/' /etc/redis/redis.conf
    
    systemctl restart redis-server
    systemctl enable redis-server
    
    echo -e "${GREEN}✓ Redis installed${NC}"
}

# Install ClamAV
install_clamav() {
    clear
    echo -e "${GREEN}${BOLD}Installing ClamAV Antivirus...${NC}"
    sleep 1
    
    apt-get install -y clamav clamav-daemon clamav-freshclam
    
    # Stop services to update
    systemctl stop clamav-daemon
    systemctl stop clamav-freshclam
    
    # Update virus definitions
    echo -e "${CYAN}Updating virus definitions (this may take a few minutes)...${NC}"
    freshclam
    
    # Start services
    systemctl start clamav-freshclam
    systemctl start clamav-daemon
    systemctl enable clamav-daemon
    systemctl enable clamav-freshclam
    
    # Create scan script
    cat > /usr/local/bin/cipi-scan <<'SCANEOF'
#!/bin/bash

#############################################
# ClamAV Daily Scan Script
# Auto-generated by Cipi
#############################################

LOG_DIR="/var/log/cipi"
SCAN_LOG="$LOG_DIR/clamav-scan.log"
REPORT_LOG="$LOG_DIR/clamav-report.log"

mkdir -p "$LOG_DIR"

echo "================================================" >> "$SCAN_LOG"
echo "ClamAV Scan Report - $(date)" >> "$SCAN_LOG"
echo "================================================" >> "$SCAN_LOG"
echo "" >> "$SCAN_LOG"

# Scan all app directories
for app_dir in /home/*/wwwroot; do
    if [ -d "$app_dir" ]; then
        username=$(basename $(dirname "$app_dir"))
        echo "Scanning: $username ($app_dir)" >> "$SCAN_LOG"
        
        # Run scan (exclude some Laravel directories for performance)
        clamscan -r "$app_dir" \
            --exclude-dir="$app_dir/vendor" \
            --exclude-dir="$app_dir/node_modules" \
            --exclude-dir="$app_dir/storage/framework" \
            --infected \
            --log="$REPORT_LOG" \
            2>&1 | grep -E "Infected files:|FOUND" >> "$SCAN_LOG"
        
        if [ $? -eq 1 ]; then
            echo "✓ Clean" >> "$SCAN_LOG"
        elif [ $? -eq 0 ]; then
            echo "⚠ THREATS DETECTED!" >> "$SCAN_LOG"
            # Send alert (you can customize this)
            echo "ALERT: Malware detected in $username" | mail -s "ClamAV Alert: $username" root
        fi
        echo "" >> "$SCAN_LOG"
    fi
done

echo "================================================" >> "$SCAN_LOG"
echo "Scan completed at $(date)" >> "$SCAN_LOG"
echo "================================================" >> "$SCAN_LOG"
echo "" >> "$SCAN_LOG"

# Keep only last 30 days of logs
find "$LOG_DIR" -name "clamav-*.log" -mtime +30 -delete
SCANEOF
    
    chmod +x /usr/local/bin/cipi-scan
    
    # Note: ClamAV scan cron job will be added in setup_cron()
    
    echo -e "${GREEN}✓ ClamAV installed and configured${NC}"
}

# Install Supervisor
install_supervisor() {
    clear
    echo -e "${GREEN}${BOLD}Installing Supervisor...${NC}"
    sleep 1
    
    apt-get install -y supervisor
    systemctl enable supervisor
    systemctl start supervisor
    
    echo -e "${GREEN}✓ Supervisor installed${NC}"
}

# Install Let's Encrypt
install_letsencrypt() {
    clear
    echo -e "${GREEN}${BOLD}Installing Let's Encrypt...${NC}"
    sleep 1
    
    apt-get install -y certbot python3-certbot-nginx
    
    echo -e "${GREEN}✓ Let's Encrypt installed${NC}"
}

# Install Node.js
install_nodejs() {
    clear
    echo -e "${GREEN}${BOLD}Installing Node.js...${NC}"
    sleep 1
    
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt-get install -y nodejs
    
    echo -e "${GREEN}✓ Node.js installed${NC}"
}

# Install Cipi
install_cipi() {
    clear
    echo -e "${GREEN}${BOLD}Installing Cipi CLI...${NC}"
    sleep 1
    
    # Create directories
    mkdir -p /opt/cipi/lib
    mkdir -p /etc/cipi
    chmod 700 /etc/cipi
    
    # Download Cipi
    cd /tmp
    git clone -b "$BRANCH" "https://github.com/${REPO}.git" cipi-install
    
    # Copy files
    cp cipi-install/cipi /usr/local/bin/cipi
    cp -r cipi-install/lib/* /opt/cipi/lib/
    
    # Set secure permissions (only root can read and execute)
    chmod 700 /usr/local/bin/cipi
    chmod 700 /opt/cipi/lib/*.sh
    chmod 700 /opt/cipi
    chown -R root:root /usr/local/bin/cipi /opt/cipi
    
    # Initialize storage
    for file in virtualhosts.json domains.json databases.json; do
        if [ ! -f "/etc/cipi/$file" ]; then
            echo "{}" > "/etc/cipi/$file"
            chmod 600 "/etc/cipi/$file"
        fi
    done
    
    # Cleanup
    rm -rf /tmp/cipi-install
    
    echo -e "${GREEN}✓ Cipi installed${NC}"
}

# Setup cron jobs
setup_cron() {
    clear
    echo -e "${GREEN}${BOLD}Setting up Cron Jobs...${NC}"
    sleep 1
    
    # Create log directory
    mkdir -p /var/log/cipi
    
    # Setup root cron jobs
    (crontab -l 2>/dev/null; cat <<'CRONEOF'

# ============================================
# CIPI AUTOMATIC CRON JOBS
# ============================================

# Update ClamAV Virus Definitions (Daily 2 AM)
0 2 * * * /usr/bin/freshclam >> /var/log/cipi/clamav-update.log 2>&1

# ClamAV Daily Scan (3 AM)
0 3 * * * /usr/local/bin/cipi-scan >> /var/log/cipi/clamav-scan.log 2>&1

# SSL Certificate Renewal (Weekly Sunday 4:10 AM)
10 4 * * 0 certbot renew --nginx --non-interactive --post-hook "systemctl restart nginx.service" >> /var/log/cipi/certbot.log 2>&1

# System Updates (Weekly Sunday 4:20 AM)
20 4 * * 0 apt-get -y update >> /var/log/cipi/updates.log 2>&1

# System Upgrade (Weekly Sunday 4:40 AM)
40 4 * * 0 DEBIAN_FRONTEND=noninteractive DEBIAN_PRIORITY=critical apt-get -q -y -o "Dpkg::Options::=--force-confdef" -o "Dpkg::Options::=--force-confold" dist-upgrade >> /var/log/cipi/updates.log 2>&1

# Clean APT Cache (Weekly Sunday 5:20 AM)
20 5 * * 0 apt-get clean && apt-get autoclean >> /var/log/cipi/updates.log 2>&1

# Clear RAM Cache and Swap (Daily 5:50 AM)
50 5 * * * echo 3 > /proc/sys/vm/drop_caches && swapoff -a && swapon -a
CRONEOF
    ) | crontab -
    
    echo -e "${GREEN}✓ Cron jobs configured${NC}"
}

# Final steps
final_steps() {
    clear
    echo -e "${GREEN}${BOLD}Final Steps...${NC}"
    sleep 1
    
    # Disable password authentication for root (optional)
    # sed -i 's/^PermitRootLogin yes/PermitRootLogin prohibit-password/' /etc/ssh/sshd_config
    # systemctl restart sshd
    
    # Get server info
    SERVER_IP=$(curl -s https://checkip.amazonaws.com)
    MYSQL_ROOT_PASSWORD=$(jq -r '.mysql_root_password' /etc/cipi/config.json)
    
    clear
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo -e "  ${GREEN}${BOLD}CIPI INSTALLATION COMPLETED!${NC}"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    echo -e "  ${BOLD}Server Information:${NC}"
    echo "  ────────────────────────────────────────────────"
    echo -e "  IP Address:    ${CYAN}$SERVER_IP${NC}"
    echo -e "  Cipi Version:  ${CYAN}$BUILD${NC}"
    echo ""
    echo -e "  ${BOLD}MySQL Root Credentials:${NC}"
    echo "  ────────────────────────────────────────────────"
    echo -e "  Username:      ${CYAN}root${NC}"
    echo -e "  Password:      ${CYAN}$MYSQL_ROOT_PASSWORD${NC}"
    echo ""
    echo -e "  ${YELLOW}${BOLD}⚠ IMPORTANT: Save these credentials!${NC}"
    echo ""
    echo -e "  ${BOLD}Getting Started:${NC}"
    echo "  ────────────────────────────────────────────────"
    echo -e "  Check server status:    ${CYAN}cipi status${NC}"
    echo -e "  View all commands:      ${CYAN}cipi help${NC}"
    echo -e "  Create app:             ${CYAN}cipi app create${NC}"
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
}

# Main installation
main() {
    show_logo
    check_os
    check_root
    install_basics
    setup_motd
    setup_swap
    setup_editor
    install_nginx
    install_firewall
    install_php
    install_composer
    install_mysql
    install_redis
    install_clamav
    install_supervisor
    install_letsencrypt
    install_nodejs
    install_cipi
    setup_cron
    final_steps
}

# Run installation
main

