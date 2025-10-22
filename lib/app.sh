#!/bin/bash

#############################################
# App Management Functions
#############################################

source "${CIPI_LIB_DIR}/nginx.sh"
source "${CIPI_LIB_DIR}/php.sh"

# Create app
app_create() {
    local username=""
    local repository=""
    local branch="main"
    local php_version="8.4"
    local interactive=true
    
    # Parse arguments
    for arg in "$@"; do
        case $arg in
            --user=*)
                username="${arg#*=}"
                ;;
            --repository=*)
                repository="${arg#*=}"
                ;;
            --branch=*)
                branch="${arg#*=}"
                ;;
            --php=*)
                php_version="${arg#*=}"
                ;;
        esac
    done
    
    # If all parameters provided, non-interactive mode
    if [ -n "$username" ] && [ -n "$repository" ] && [ -n "$php_version" ]; then
        interactive=false
    fi
    
    # Interactive prompts
    if [ $interactive = true ]; then
        echo -e "${BOLD}Create New App${NC}"
        echo "─────────────────────────────────────"
        echo ""
        
        if [ -z "$username" ]; then
            default_username=$(generate_username)
            read -p "Username [$default_username]: " username
            username=${username:-$default_username}
        fi
        
        if [ -z "$repository" ]; then
            read -p "Git repository URL: " repository
        fi
        
        if [ -z "$branch" ]; then
            read -p "Git branch [main]: " branch
            branch=${branch:-main}
        fi
        
        if [ -z "$php_version" ]; then
            echo ""
            echo "Select PHP version:"
            local php_versions=($(get_installed_php_versions))
            local i=1
            for version in "${php_versions[@]}"; do
                echo "  $i. PHP $version"
                ((i++))
            done
            read -p "Choice [1]: " php_choice
            php_choice=${php_choice:-1}
            php_version=${php_versions[$((php_choice-1))]}
        fi
    fi
    
    # Validate inputs
    if [ -z "$username" ] || [ -z "$repository" ]; then
        echo -e "${RED}Error: Username and repository are required${NC}"
        exit 1
    fi
    
    # Check if app already exists
    if json_has_key "${VIRTUALHOSTS_FILE}" "$username"; then
        echo -e "${RED}Error: App '$username' already exists${NC}"
        exit 1
    fi
    
    # Check if PHP version is installed
    if ! is_php_installed "$php_version"; then
        echo -e "${YELLOW}PHP $php_version is not installed. Installing...${NC}"
        install_php_version "$php_version"
    fi
    
    echo ""
    echo -e "${CYAN}Creating virtual host...${NC}"
    
    # Generate password
    local password=$(generate_password 24)
    
    # Create system user
    echo "  → Creating system user..."
    if ! create_system_user "$username" "$password"; then
        echo -e "${RED}Error: Failed to create system user${NC}"
        exit 1
    fi
    
    # Create directory structure
    echo "  → Creating directory structure..."
    local home_dir="/home/$username"
    mkdir -p "$home_dir"/{wwwroot,logs,.ssh}
    chown -R "$username:$username" "$home_dir"
    chmod 750 "$home_dir"  # Only owner and group can access
    chmod 755 "$home_dir/logs"  # Logs readable by web server if needed
    chmod 700 "$home_dir/.ssh"  # SSH keys only for owner
    
    # Generate SSH key pair for Git
    echo "  → Generating SSH key pair for Git..."
    sudo -u "$username" ssh-keygen -t rsa -b 4096 -C "${username}@cipi" -f "$home_dir/.ssh/id_rsa" -N "" >/dev/null 2>&1
    cp "$home_dir/.ssh/id_rsa.pub" "$home_dir/gitkey.pub"
    chown "$username:$username" "$home_dir/gitkey.pub"
    chmod 644 "$home_dir/gitkey.pub"
    
    # Clone repository
    echo "  → Cloning repository..."
    sudo -u "$username" git clone -b "$branch" "$repository" "$home_dir/wwwroot" 2>/dev/null
    if [ $? -ne 0 ]; then
        echo -e "${RED}Error: Failed to clone repository${NC}"
        delete_system_user "$username"
        rm -rf "$home_dir"
        exit 1
    fi
    
    # Create PHP-FPM pool
    echo "  → Creating PHP-FPM pool..."
    create_php_pool "$username" "$php_version"
    
    # Create Nginx configuration
    echo "  → Creating Nginx configuration..."
    create_nginx_config "$username" "" "$php_version"
    
    # Create deployment script
    echo "  → Creating deployment script..."
    create_deploy_script "$username" "$repository" "$branch"
    
    # Create SSL script
    echo "  → Creating SSL script..."
    create_ssl_script "$username"
    
    # Setup log rotation
    echo "  → Setting up log rotation..."
    setup_log_rotation "$username"
    
    # Setup Laravel (if detected)
    if [ -f "$home_dir/wwwroot/artisan" ]; then
        echo "  → Detected Laravel application, setting up..."
        setup_laravel "$username"
    fi
    
    # Setup crontab for user
    echo "  → Setting up crontab..."
    setup_user_crontab "$username"
    
    # Reload nginx
    echo "  → Reloading Nginx..."
    nginx_reload
    
    # Save to storage (password not saved for security)
    local app_data=$(cat <<EOF
{
    "username": "$username",
    "repository": "$repository",
    "branch": "$branch",
    "php_version": "$php_version",
    "home_dir": "$home_dir",
    "created_at": "$(date -Iseconds)"
}
EOF
)
    json_set "${VIRTUALHOSTS_FILE}" "$username" "$app_data"
    
    # Display summary
    echo ""
    echo -e "${GREEN}${BOLD}App created successfully!${NC}"
    echo "─────────────────────────────────────"
    echo -e "Path:     ${CYAN}$home_dir${NC}"
    echo -e "Username: ${CYAN}$username${NC}"
    echo -e "Password: ${CYAN}$password${NC}"
    echo -e "PHP:      ${CYAN}$php_version${NC}"
    echo ""
    echo -e "${YELLOW}${BOLD}IMPORTANT: Save these credentials!${NC}"
    echo ""
    echo -e "${CYAN}Git SSH Public Key:${NC}"
    echo -e "Add this key to your Git provider (GitHub/GitLab) for private repositories:"
    echo ""
    cat "$home_dir/gitkey.pub"
    echo ""
    echo -e "Key also available at: ${CYAN}$home_dir/gitkey.pub${NC}"
    echo ""
    echo -e "${CYAN}${BOLD}Next Steps:${NC}"
    echo -e "1. Configure S3 backup: ${CYAN}nano $home_dir/backup.sh${NC}"
    echo -e "2. Setup deployment: ${CYAN}$home_dir/deploy.sh${NC}"
    echo -e "3. Assign domain: ${CYAN}cipi domain create${NC}"
    echo ""
}

# List apps
app_list() {
    init_storage
    
    echo -e "${BOLD}Apps${NC}"
    echo "─────────────────────────────────────"
    echo ""
    
    local apps=$(json_keys "${VIRTUALHOSTS_FILE}")
    
    if [ -z "$apps" ]; then
        echo "No virtual hosts found."
        echo ""
        return
    fi
    
    printf "%-15s %-10s %-30s\n" "USERNAME" "PHP" "DOMAIN"
    echo "───────────────────────────────────────────────────────────"
    
    for username in $apps; do
        local vhost=$(json_get "${VIRTUALHOSTS_FILE}" "$username")
        local php_version=$(echo "$vhost" | jq -r '.php_version')
        local domain=$(get_domain_by_app "$username")
        domain=${domain:-"(no domain)"}
        
        printf "%-15s %-10s %-30s\n" "$username" "$php_version" "$domain"
    done
    
    echo ""
}

# Show app details
app_show() {
    local username=$1
    
    if [ -z "$username" ]; then
        echo -e "${RED}Error: Username required${NC}"
        echo "Usage: cipi app show <username>"
        exit 1
    fi
    
    if ! json_has_key "${VIRTUALHOSTS_FILE}" "$username"; then
        echo -e "${RED}Error: App '$username' not found${NC}"
        exit 1
    fi
    
    local vhost=$(json_get "${VIRTUALHOSTS_FILE}" "$username")
    local home_dir=$(echo "$vhost" | jq -r '.home_dir')
    local php_version=$(echo "$vhost" | jq -r '.php_version')
    local repository=$(echo "$vhost" | jq -r '.repository')
    local branch=$(echo "$vhost" | jq -r '.branch')
    local domain=$(get_domain_by_app "$username")
    local aliases=$(get_aliases_by_app "$username")
    local disk_space=$(get_disk_space "$home_dir")
    
    echo -e "${BOLD}App: $username${NC}"
    echo "─────────────────────────────────────"
    echo -e "Path:       ${CYAN}$home_dir${NC}"
    echo -e "Username:   ${CYAN}$username${NC}"
    echo -e "PHP:        ${CYAN}$php_version${NC}"
    echo -e "Repository: ${CYAN}$repository${NC}"
    echo -e "Branch:     ${CYAN}$branch${NC}"
    echo -e "Domain:     ${CYAN}${domain:-(no domain)}${NC}"
    echo -e "Aliases:    ${CYAN}${aliases:-(none)}${NC}"
    echo -e "Disk Space: ${CYAN}$disk_space${NC}"
    echo ""
    echo -e "${BOLD}Git SSH Public Key:${NC}"
    if [ -f "$home_dir/gitkey.pub" ]; then
        cat "$home_dir/gitkey.pub"
        echo ""
        echo -e "Location: ${CYAN}$home_dir/gitkey.pub${NC}"
    else
        echo -e "${RED}Not found${NC}"
    fi
    echo ""
}

# Edit app
app_edit() {
    local username=""
    local new_php_version=""
    
    # Parse arguments
    for arg in "$@"; do
        case $arg in
            --php=*)
                new_php_version="${arg#*=}"
                ;;
            *)
                if [ -z "$username" ]; then
                    username="$arg"
                fi
                ;;
        esac
    done
    
    if [ -z "$username" ]; then
        echo -e "${RED}Error: Username required${NC}"
        echo "Usage: cipi app edit <username> --php=X.X"
        exit 1
    fi
    
    if ! json_has_key "${VIRTUALHOSTS_FILE}" "$username"; then
        echo -e "${RED}Error: App '$username' not found${NC}"
        exit 1
    fi
    
    # Get current app data
    local vhost=$(json_get "${VIRTUALHOSTS_FILE}" "$username")
    local current_php=$(echo "$vhost" | jq -r '.php_version')
    local home_dir=$(echo "$vhost" | jq -r '.home_dir')
    
    # Edit PHP version
    if [ -n "$new_php_version" ]; then
        echo -e "${CYAN}Changing PHP version from $current_php to $new_php_version...${NC}"
        
        # Check if new PHP version is installed
        if ! is_php_installed "$new_php_version"; then
            echo -e "${YELLOW}PHP $new_php_version is not installed. Installing...${NC}"
            install_php_version "$new_php_version"
        fi
        
        # Delete old PHP pool
        echo "  → Removing old PHP-FPM pool..."
        delete_php_pool "$username" "$current_php"
        
        # Create new PHP pool
        echo "  → Creating new PHP-FPM pool..."
        create_php_pool "$username" "$new_php_version"
        
        # Update Nginx configuration
        echo "  → Updating Nginx configuration..."
        local domain=$(get_domain_by_app "$username")
        if [ -n "$domain" ]; then
            local domain_data=$(json_get "${DOMAINS_FILE}" "$domain")
            local ssl=$(echo "$domain_data" | jq -r '.ssl')
            local aliases=$(echo "$domain_data" | jq -r '.aliases[]?' 2>/dev/null | tr '\n' ' ')
            
            if [ "$ssl" = "true" ]; then
                add_ssl_to_nginx "$username" "$domain" "$aliases" "$new_php_version"
            else
                create_nginx_config "$username" "$domain" "$new_php_version"
            fi
        else
            create_nginx_config "$username" "" "$new_php_version"
        fi
        
        # Update storage
        local tmp=$(mktemp)
        echo "$vhost" | jq ".php_version = \"$new_php_version\"" > "$tmp"
        local updated_vhost=$(cat "$tmp")
        rm "$tmp"
        json_set "${VIRTUALHOSTS_FILE}" "$username" "$updated_vhost"
        
        # Reload services
        echo "  → Reloading services..."
        nginx_reload
        
        echo ""
        echo -e "${GREEN}PHP version updated successfully!${NC}"
        echo -e "Old version: ${CYAN}$current_php${NC}"
        echo -e "New version: ${CYAN}$new_php_version${NC}"
        echo ""
    else
        echo -e "${RED}Error: No changes specified${NC}"
        echo "Usage: cipi app edit <username> --php=X.X"
        exit 1
    fi
}

# Edit app .env file
app_env() {
    local username=$1
    
    if [ -z "$username" ]; then
        echo -e "${RED}Error: Username required${NC}"
        echo "Usage: cipi app env <username>"
        exit 1
    fi
    
    if ! json_has_key "${VIRTUALHOSTS_FILE}" "$username"; then
        echo -e "${RED}Error: App '$username' not found${NC}"
        exit 1
    fi
    
    local vhost=$(json_get "${VIRTUALHOSTS_FILE}" "$username")
    local home_dir=$(echo "$vhost" | jq -r '.home_dir')
    local env_file="$home_dir/wwwroot/.env"
    
    if [ ! -f "$env_file" ]; then
        echo -e "${YELLOW}Warning: .env file not found at $env_file${NC}"
        echo ""
        read -p "Do you want to create it from .env.example? (y/N): " create_env
        
        if [ "$create_env" = "y" ] || [ "$create_env" = "Y" ]; then
            if [ -f "$home_dir/wwwroot/.env.example" ]; then
                cp "$home_dir/wwwroot/.env.example" "$env_file"
                chown "$username:$username" "$env_file"
                chmod 644 "$env_file"
                echo -e "${GREEN}Created .env from .env.example${NC}"
            else
                echo -e "${RED}Error: .env.example not found${NC}"
                exit 1
            fi
        else
            exit 0
        fi
    fi
    
    echo -e "${CYAN}Opening .env editor for: $username${NC}"
    echo -e "File: ${CYAN}$env_file${NC}"
    echo ""
    echo -e "${YELLOW}Tip: After editing, restart PHP-FPM if needed:${NC}"
    echo -e "  ${CYAN}cipi service restart php${NC}"
    echo ""
    sleep 2
    
    # Set nano as editor and open file as the app user
    export EDITOR=nano
    export VISUAL=nano
    sudo -u "$username" -E nano "$env_file"
    
    echo ""
    echo -e "${GREEN}.env file saved${NC}"
    echo ""
}

# Edit app crontab
app_crontab() {
    local username=$1
    
    if [ -z "$username" ]; then
        echo -e "${RED}Error: Username required${NC}"
        echo "Usage: cipi app crontab <username>"
        exit 1
    fi
    
    if ! json_has_key "${APPS_FILE}" "$username"; then
        echo -e "${RED}Error: App '$username' not found${NC}"
        exit 1
    fi
    
    echo -e "${BOLD}Edit Crontab for: $username${NC}"
    echo "─────────────────────────────────────"
    echo ""
    echo -e "${CYAN}Opening crontab editor...${NC}"
    echo ""
    echo -e "${YELLOW}Tip: Add this line for Laravel scheduler:${NC}"
    echo -e "  ${CYAN}* * * * * cd /home/$username/wwwroot && php artisan schedule:run >> /dev/null 2>&1${NC}"
    echo ""
    echo -e "${YELLOW}Tip: Example backup at 2 AM daily:${NC}"
    echo -e "  ${CYAN}0 2 * * * /home/$username/backup.sh >> /home/$username/logs/backup.log 2>&1${NC}"
    echo ""
    sleep 3
    
    # Set nano as editor and open crontab as the app user
    export EDITOR=nano
    export VISUAL=nano
    sudo -u "$username" -E crontab -e
    
    echo ""
    echo -e "${GREEN}Crontab updated${NC}"
    echo ""
    echo -e "${CYAN}View current crontab:${NC}"
    echo -e "  sudo crontab -u $username -l"
    echo ""
}

# Change app password
app_password() {
    local username=$1
    local new_password=""
    
    # Parse arguments
    for arg in "$@"; do
        case $arg in
            --password=*)
                new_password="${arg#*=}"
                ;;
        esac
    done
    
    if [ -z "$username" ]; then
        echo -e "${RED}Error: Username required${NC}"
        echo "Usage: cipi app password <username> [--password=XXX]"
        exit 1
    fi
    
    if ! json_has_key "${VIRTUALHOSTS_FILE}" "$username"; then
        echo -e "${RED}Error: App '$username' not found${NC}"
        exit 1
    fi
    
    echo -e "${BOLD}Change Password for: $username${NC}"
    echo "─────────────────────────────────────"
    echo ""
    
    # Generate or use provided password
    if [ -z "$new_password" ]; then
        new_password=$(generate_password 24)
        echo "Generated new password: ${CYAN}$new_password${NC}"
    else
        echo "Using provided password"
    fi
    
    echo ""
    echo -e "${CYAN}Changing password...${NC}"
    
    # Change system user password
    echo "$username:$new_password" | chpasswd
    
    if [ $? -ne 0 ]; then
        echo -e "${RED}Error: Failed to change password${NC}"
        exit 1
    fi
    
    # Note: Password is not stored in JSON for security reasons
    
    echo ""
    echo -e "${GREEN}${BOLD}Password changed successfully!${NC}"
    echo "─────────────────────────────────────"
    echo -e "Username: ${CYAN}$username${NC}"
    echo -e "New Password: ${CYAN}$new_password${NC}"
    echo ""
    echo -e "${YELLOW}${BOLD}IMPORTANT: Save this password!${NC}"
    echo ""
}

# Delete app
app_delete() {
    local username=$1
    
    if [ -z "$username" ]; then
        echo -e "${RED}Error: Username required${NC}"
        echo "Usage: cipi app delete <username>"
        exit 1
    fi
    
    if ! json_has_key "${VIRTUALHOSTS_FILE}" "$username"; then
        echo -e "${RED}Error: App '$username' not found${NC}"
        exit 1
    fi
    
    # Confirm deletion
    echo -e "${YELLOW}${BOLD}Warning: This will permanently delete the virtual host and all its data!${NC}"
    read -p "Type the username to confirm: " confirm
    
    if [ "$confirm" != "$username" ]; then
        echo "Deletion cancelled."
        exit 0
    fi
    
    echo ""
    echo -e "${CYAN}Deleting virtual host...${NC}"
    
    local vhost=$(json_get "${VIRTUALHOSTS_FILE}" "$username")
    local php_version=$(echo "$vhost" | jq -r '.php_version')
    
    # Delete associated domains
    echo "  → Deleting associated domains..."
    delete_domains_by_app "$username"
    
    # Delete Nginx configuration
    echo "  → Deleting Nginx configuration..."
    delete_nginx_config "$username"
    
    # Delete PHP-FPM pool
    echo "  → Deleting PHP-FPM pool..."
    delete_php_pool "$username" "$php_version"
    
    # Delete system user and home directory
    echo "  → Deleting system user and files..."
    delete_system_user "$username"
    
    # Remove from storage
    json_delete "${VIRTUALHOSTS_FILE}" "$username"
    
    # Reload nginx
    echo "  → Reloading Nginx..."
    nginx_reload
    
    echo ""
    echo -e "${GREEN}App deleted successfully!${NC}"
    echo ""
}

# Helper: Get domain by app
get_domain_by_app() {
    local username=$1
    local domains=$(json_read "${DOMAINS_FILE}")
    
    echo "$domains" | jq -r "to_entries[] | select(.value.app == \"$username\") | .key" | head -n 1
}

# Helper: Get aliases by app
get_aliases_by_app() {
    local username=$1
    local domain=$(get_domain_by_app "$username")
    
    if [ -n "$domain" ]; then
        local domain_data=$(json_get "${DOMAINS_FILE}" "$domain")
        echo "$domain_data" | jq -r '.aliases[]?' 2>/dev/null | tr '\n' ', ' | sed 's/,$//'
    fi
}

# Helper: Delete domains by app
delete_domains_by_app() {
    local username=$1
    local domains=$(json_read "${DOMAINS_FILE}")
    
    local domain_keys=$(echo "$domains" | jq -r "to_entries[] | select(.value.app == \"$username\") | .key")
    
    for domain in $domain_keys; do
        json_delete "${DOMAINS_FILE}" "$domain"
    done
}

# Helper: Setup Laravel
setup_laravel() {
    local username=$1
    local home_dir="/home/$username"
    local wwwroot="$home_dir/wwwroot"
    
    cd "$wwwroot"
    
    # Install composer dependencies
    sudo -u "$username" composer install --no-interaction --prefer-dist --optimize-autoloader 2>/dev/null
    
    # Create .env if not exists
    if [ ! -f "$wwwroot/.env" ] && [ -f "$wwwroot/.env.example" ]; then
        sudo -u "$username" cp "$wwwroot/.env.example" "$wwwroot/.env"
        sudo -u "$username" php artisan key:generate
    fi
    
    # Set permissions
    chown -R "$username:$username" "$wwwroot"
    chmod -R 755 "$wwwroot/storage"
    chmod -R 755 "$wwwroot/bootstrap/cache"
}

# Helper: Setup user crontab
setup_user_crontab() {
    local username=$1
    local home_dir="/home/$username"
    
    # Create crontab for Laravel scheduler (if Laravel detected)
    if [ -f "$home_dir/wwwroot/artisan" ]; then
        (crontab -u "$username" -l 2>/dev/null; echo "* * * * * cd $home_dir/wwwroot && php artisan schedule:run >> /dev/null 2>&1") | crontab -u "$username" -
    fi
}

# Helper: Setup log rotation
setup_log_rotation() {
    local username=$1
    local log_dir="/home/$username/logs"
    
    cat > "/etc/logrotate.d/cipi-$username" <<EOF
$log_dir/*.log {
    daily
    rotate 30
    missingok
    notifempty
    compress
    delaycompress
    sharedscripts
    postrotate
        systemctl reload nginx > /dev/null 2>&1
    endscript
}
EOF
}

