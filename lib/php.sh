#!/bin/bash

#############################################
# PHP Management Functions
#############################################

# Get installed PHP versions
get_installed_php_versions() {
    if [ -d /etc/php ]; then
        ls /etc/php | grep -E '^[0-9]+\.[0-9]+$' | sort -V
    fi
}

# Check if PHP version is installed
is_php_installed() {
    local version=$1
    [ -d "/etc/php/${version}" ]
}

# Install PHP version
install_php_version() {
    local version=$1
    
    echo -e "${CYAN}Installing PHP ${version}...${NC}"
    
    # Add ondrej/php repository
    add-apt-repository -y ppa:ondrej/php >/dev/null 2>&1
    apt-get update >/dev/null 2>&1
    
    # List of extensions to install
    local extensions=(
        "php${version}-fpm"
        "php${version}-common"
        "php${version}-cli"
        "php${version}-curl"
        "php${version}-bcmath"
        "php${version}-mbstring"
        "php${version}-mysql"
        "php${version}-sqlite3"
        "php${version}-pgsql"
        "php${version}-redis"
        "php${version}-memcached"
        "php${version}-zip"
        "php${version}-xml"
        "php${version}-soap"
        "php${version}-gd"
        "php${version}-imagick"
        "php${version}-intl"
    )
    
    # Install packages
    DEBIAN_FRONTEND=noninteractive apt-get install -y "${extensions[@]}" >/dev/null 2>&1
    
    if [ $? -ne 0 ]; then
        echo -e "${RED}Error: Failed to install PHP ${version}${NC}"
        return 1
    fi
    
    # Configure PHP
    configure_php "$version"
    
    echo -e "${GREEN}PHP ${version} installed successfully!${NC}"
    return 0
}

# Configure PHP
configure_php() {
    local version=$1
    local ini_file="/etc/php/${version}/fpm/conf.d/cipi.ini"
    
    cat > "$ini_file" <<EOF
memory_limit = 256M
upload_max_filesize = 256M
post_max_size = 256M
max_execution_time = 300
max_input_time = 300
EOF
    
    # Restart PHP-FPM
    systemctl restart "php${version}-fpm" 2>/dev/null
}

# Create PHP-FPM pool for user
create_php_pool() {
    local username=$1
    local version=$2
    local pool_file="/etc/php/${version}/fpm/pool.d/${username}.conf"
    local socket_path="/var/run/php/php${version}-fpm-${username}.sock"
    local home_dir="/home/${username}"
    
    cat > "$pool_file" <<EOF
[${username}]
user = ${username}
group = ${username}
listen = ${socket_path}
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

pm = dynamic
pm.max_children = 5
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3

chdir = ${home_dir}/wwwroot
EOF
    
    systemctl restart "php${version}-fpm"
}

# Delete PHP-FPM pool for user
delete_php_pool() {
    local username=$1
    local version=$2
    local pool_file="/etc/php/${version}/fpm/pool.d/${username}.conf"
    
    if [ -f "$pool_file" ]; then
        rm -f "$pool_file"
        systemctl restart "php${version}-fpm" 2>/dev/null
    fi
}

# List PHP versions
php_list() {
    echo -e "${BOLD}Installed PHP Versions${NC}"
    echo "─────────────────────────────────────"
    
    local versions=($(get_installed_php_versions))
    local current=$(get_current_php_cli)
    
    if [ ${#versions[@]} -eq 0 ]; then
        echo "No PHP versions found."
        return
    fi
    
    for version in "${versions[@]}"; do
        if [ "$version" = "$current" ]; then
            echo -e "  ${GREEN}●${NC} PHP $version ${CYAN}(CLI default)${NC}"
        else
            echo -e "  ${BLUE}○${NC} PHP $version"
        fi
    done
    
    echo ""
    echo -e "${YELLOW}Note:${NC} Use 'cipi virtualhost edit <user> --php=X.X' to change PHP version for a specific virtual host"
    echo -e "      Use 'cipi php switch X.X' to change the global CLI version"
    echo ""
}

# Install PHP version command
php_install() {
    local version=""
    
    # Parse arguments
    for arg in "$@"; do
        case $arg in
            --version=*|--v=*)
                version="${arg#*=}"
                ;;
            *)
                if [ -z "$version" ]; then
                    version="$arg"
                fi
                ;;
        esac
    done
    
    # Validate version
    if [ -z "$version" ]; then
        read -p "PHP version to install (e.g., 8.4): " version
    fi
    
    if [ -z "$version" ]; then
        echo -e "${RED}Error: Version required${NC}"
        exit 1
    fi
    
    # Check if already installed
    if is_php_installed "$version"; then
        echo -e "${YELLOW}PHP $version is already installed${NC}"
        exit 0
    fi
    
    # Install
    install_php_version "$version"
}

# Switch PHP CLI version
php_switch() {
    local version=""
    
    # Parse arguments
    for arg in "$@"; do
        case $arg in
            --version=*|--v=*)
                version="${arg#*=}"
                ;;
            *)
                if [ -z "$version" ]; then
                    version="$arg"
                fi
                ;;
        esac
    done
    
    # Validate version
    if [ -z "$version" ]; then
        echo "Select PHP version for CLI:"
        local versions=($(get_installed_php_versions))
        local i=1
        for v in "${versions[@]}"; do
            echo "  $i. PHP $v"
            ((i++))
        done
        read -p "Choice: " choice
        version=${versions[$((choice-1))]}
    fi
    
    if [ -z "$version" ]; then
        echo -e "${RED}Error: Version required${NC}"
        exit 1
    fi
    
    # Check if installed
    if ! is_php_installed "$version"; then
        echo -e "${RED}Error: PHP $version is not installed${NC}"
        exit 1
    fi
    
    # Switch
    update-alternatives --set php "/usr/bin/php${version}" >/dev/null 2>&1
    
    echo -e "${GREEN}PHP CLI switched to $version${NC}"
    php -v | head -n 1
}

# Get current PHP CLI version
get_current_php_cli() {
    php -v 2>/dev/null | grep -oP 'PHP \K[0-9]+\.[0-9]+' | head -n 1
}

