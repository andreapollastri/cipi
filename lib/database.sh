#!/bin/bash

#############################################
# Database Management Functions
#############################################

# Get MySQL root password
get_mysql_root_password() {
    get_config "mysql_root_password" ""
}

# Create database
database_create() {
    local dbname=""
    local interactive=true
    
    # Parse arguments
    for arg in "$@"; do
        case $arg in
            --name=*)
                dbname="${arg#*=}"
                interactive=false
                ;;
        esac
    done
    
    # Interactive prompt
    if [ $interactive = true ]; then
        echo -e "${BOLD}Create Database${NC}"
        echo "─────────────────────────────────────"
        echo ""
        
        if [ -z "$dbname" ]; then
            default_dbname=$(generate_dbname)
            read -p "Database name [$default_dbname]: " dbname
            dbname=${dbname:-$default_dbname}
        fi
    fi
    
    # Generate default if still empty
    if [ -z "$dbname" ]; then
        dbname=$(generate_dbname)
    fi
    
    # Check if database already exists
    if json_has_key "${DATABASES_FILE}" "$dbname"; then
        echo -e "${RED}Error: Database '$dbname' already exists${NC}"
        exit 1
    fi
    
    echo ""
    echo -e "${CYAN}Creating database...${NC}"
    
    # Generate credentials
    local db_username=$(generate_db_username)
    local db_password=$(generate_password 16)
    local root_password=$(get_mysql_root_password)
    
    # Create database and user
    mysql -u root -p"${root_password}" <<EOF 2>/dev/null
CREATE DATABASE IF NOT EXISTS \`${dbname}\`;
CREATE USER IF NOT EXISTS '${db_username}'@'localhost' IDENTIFIED BY '${db_password}';
GRANT ALL PRIVILEGES ON \`${dbname}\`.* TO '${db_username}'@'localhost';
FLUSH PRIVILEGES;
EOF
    
    if [ $? -ne 0 ]; then
        echo -e "${RED}Error: Failed to create database${NC}"
        exit 1
    fi
    
    # Save to storage (password not saved for security)
    local db_data=$(jq -n \
        --arg name "$dbname" \
        --arg user "$db_username" \
        --arg created "$(date -Iseconds)" \
        '{database: $name, username: $user, created_at: $created}')
    
    json_set "${DATABASES_FILE}" "$dbname" "$db_data"
    
    # Get server IP for SSH tunnel
    local server_ip=$(get_server_ip)
    
    # Display summary
    echo ""
    echo -e "${GREEN}${BOLD}Database created successfully!${NC}"
    echo "─────────────────────────────────────"
    echo -e "Database: ${CYAN}$dbname${NC}"
    echo -e "Username: ${CYAN}$db_username${NC}"
    echo -e "Password: ${CYAN}$db_password${NC}"
    echo ""
    echo -e "${BOLD}LARAVEL ENV${NC}"
    echo "─────────────────────────────────────"
    echo "DB_CONNECTION=mysql"
    echo "DB_HOST=127.0.0.1"
    echo "DB_PORT=3306"
    echo "DB_DATABASE=$dbname"
    echo "DB_USERNAME=$db_username"
    echo "DB_PASSWORD=$db_password"
    echo ""
    echo -e "${BOLD}SSH TUNNEL CONNECTION${NC}"
    echo "─────────────────────────────────────"
    echo "mysql+ssh://[sshuser]@${server_ip}/${db_username}:${db_password}@127.0.0.1/${dbname}"
    echo ""
}

# List databases
database_list() {
    init_storage
    
    echo -e "${BOLD}Databases${NC}"
    echo "─────────────────────────────────────"
    echo ""
    
    local databases=$(json_keys "${DATABASES_FILE}")
    
    if [ -z "$databases" ]; then
        echo "No databases found."
        echo ""
        return
    fi
    
    printf "%-20s %-20s %-15s\n" "DATABASE" "USERNAME" "CREATED"
    echo "───────────────────────────────────────────────────────────"
    
    for dbname in $databases; do
        local db_data=$(json_get "${DATABASES_FILE}" "$dbname")
        local username=$(echo "$db_data" | jq -r '.username')
        local created=$(echo "$db_data" | jq -r '.created_at' | cut -d'T' -f1)
        
        printf "%-20s %-20s %-15s\n" "$dbname" "$username" "$created"
    done
    
    echo ""
}

# Change database password
database_password() {
    local dbname=$1
    local new_password=""
    
    # Parse arguments
    for arg in "$@"; do
        case $arg in
            --password=*)
                new_password="${arg#*=}"
                ;;
        esac
    done
    
    if [ -z "$dbname" ]; then
        echo -e "${RED}Error: Database name required${NC}"
        echo "Usage: cipi database password <name> [--password=XXX]"
        exit 1
    fi
    
    if ! json_has_key "${DATABASES_FILE}" "$dbname"; then
        echo -e "${RED}Error: Database '$dbname' not found${NC}"
        exit 1
    fi
    
    echo -e "${BOLD}Change Database Password: $dbname${NC}"
    echo "─────────────────────────────────────"
    echo ""
    
    # Get database data
    local db_data=$(json_get "${DATABASES_FILE}" "$dbname")
    local db_username=$(echo "$db_data" | jq -r '.username')
    local root_password=$(get_mysql_root_password)
    
    # Generate or use provided password
    if [ -z "$new_password" ]; then
        new_password=$(generate_password 16)
        echo "Generated new password: ${CYAN}$new_password${NC}"
    else
        echo "Using provided password"
    fi
    
    echo ""
    echo -e "${CYAN}Changing password...${NC}"
    
    # Change MySQL password
    mysql -u root -p"${root_password}" <<EOF 2>/dev/null
ALTER USER '${db_username}'@'localhost' IDENTIFIED BY '${new_password}';
FLUSH PRIVILEGES;
EOF

    if [ $? -ne 0 ]; then
        echo -e "${RED}Error: Failed to change database password${NC}"
        exit 1
    fi

    # Note: Password is not stored in JSON for security reasons
    
    # Get server IP for SSH tunnel
    local server_ip=$(get_server_ip)
    
    echo ""
    echo -e "${GREEN}${BOLD}Database password changed successfully!${NC}"
    echo "─────────────────────────────────────"
    echo -e "Database: ${CYAN}$dbname${NC}"
    echo -e "Username: ${CYAN}$db_username${NC}"
    echo -e "New Password: ${CYAN}$new_password${NC}"
    echo ""
    echo -e "${BOLD}LARAVEL ENV${NC}"
    echo "─────────────────────────────────────"
    echo "DB_CONNECTION=mysql"
    echo "DB_HOST=127.0.0.1"
    echo "DB_PORT=3306"
    echo "DB_DATABASE=$dbname"
    echo "DB_USERNAME=$db_username"
    echo "DB_PASSWORD=$new_password"
    echo ""
    echo -e "${BOLD}SSH TUNNEL CONNECTION${NC}"
    echo "─────────────────────────────────────"
    echo "mysql+ssh://[sshuser]@${server_ip}/${db_username}:${new_password}@127.0.0.1/${dbname}"
    echo ""
    echo -e "${YELLOW}${BOLD}IMPORTANT: Update your .env file with the new password!${NC}"
    echo ""
}

# Delete database
database_delete() {
    local dbname=$1
    
    if [ -z "$dbname" ]; then
        echo -e "${RED}Error: Database name required${NC}"
        echo "Usage: cipi database delete <name>"
        exit 1
    fi
    
    if ! json_has_key "${DATABASES_FILE}" "$dbname"; then
        echo -e "${RED}Error: Database '$dbname' not found${NC}"
        exit 1
    fi
    
    # Confirm deletion
    echo -e "${YELLOW}${BOLD}Warning: This will permanently delete the database and all its data!${NC}"
    read -p "Type the database name to confirm: " confirm
    
    if [ "$confirm" != "$dbname" ]; then
        echo "Deletion cancelled."
        exit 0
    fi
    
    echo ""
    echo -e "${CYAN}Deleting database...${NC}"
    
    # Get database data
    local db_data=$(json_get "${DATABASES_FILE}" "$dbname")
    local db_username=$(echo "$db_data" | jq -r '.username')
    local root_password=$(get_mysql_root_password)
    
    # Delete database and user
    mysql -u root -p"${root_password}" <<EOF 2>/dev/null
DROP DATABASE IF EXISTS \`${dbname}\`;
DROP USER IF EXISTS '${db_username}'@'localhost';
FLUSH PRIVILEGES;
EOF
    
    if [ $? -ne 0 ]; then
        echo -e "${RED}Error: Failed to delete database${NC}"
        exit 1
    fi
    
    # Remove from storage
    json_delete "${DATABASES_FILE}" "$dbname"
    
    echo ""
    echo -e "${GREEN}Database deleted successfully!${NC}"
    echo ""
}

