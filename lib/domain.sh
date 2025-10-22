#!/bin/bash

#############################################
# Domain Management Functions
#############################################

# Create domain
domain_create() {
    local domain=""
    local aliases=""
    local app=""
    local interactive=true
    
    # Parse arguments
    for arg in "$@"; do
        case $arg in
            --domain=*)
                domain="${arg#*=}"
                ;;
            --aliases=*)
                aliases="${arg#*=}"
                ;;
            --app=*)
                app="${arg#*=}"
                ;;
        esac
    done
    
    # If all parameters provided, non-interactive mode
    if [ -n "$domain" ] && [ -n "$app" ]; then
        interactive=false
    fi
    
    # Interactive prompts
    if [ $interactive = true ]; then
        echo -e "${BOLD}Create/Assign Domain${NC}"
        echo "─────────────────────────────────────"
        echo ""
        
        if [ -z "$domain" ]; then
            read -p "Domain name: " domain
        fi
        
        if [ -z "$aliases" ]; then
            read -p "Aliases (comma-separated, optional): " aliases
        fi
        
        if [ -z "$app" ]; then
            echo ""
            echo "Select virtual host:"
            
            # Get available apps (those without domains)
            local available_vhosts=()
            local all_vhosts=($(json_keys "${VIRTUALHOSTS_FILE}"))
            
            for vh in "${all_vhosts[@]}"; do
                if [ -z "$(get_domain_by_app "$vh")" ]; then
                    available_vhosts+=("$vh")
                fi
            done
            
            if [ ${#available_vhosts[@]} -eq 0 ]; then
                echo -e "${YELLOW}No available virtual hosts found. Creating new one...${NC}"
                echo ""
                app_create
                # Get the last created app
                app=$(json_keys "${VIRTUALHOSTS_FILE}" | tail -n 1)
            else
                local i=1
                for vh in "${available_vhosts[@]}"; do
                    echo "  $i. $vh"
                    ((i++))
                done
                read -p "Choice: " choice
                app=${available_vhosts[$((choice-1))]}
            fi
        fi
    fi
    
    # Validate inputs
    if [ -z "$domain" ]; then
        echo -e "${RED}Error: Domain required${NC}"
        exit 1
    fi
    
    # Check for wildcard domain
    if [[ "$domain" == *"*"* ]]; then
        echo -e "${YELLOW}${BOLD}Warning: Wildcard domains detected${NC}"
        echo ""
        echo "Wildcard domains (*.example.com) require DNS validation."
        echo "You'll need to:"
        echo "  1. Create the domain first"
        echo "  2. Use a DNS provider plugin with certbot"
        echo "  3. Manually configure DNS TXT records"
        echo ""
        echo "Supported DNS providers:"
        echo "  - Cloudflare (certbot-dns-cloudflare)"
        echo "  - Route53 (certbot-dns-route53)"
        echo "  - DigitalOcean (certbot-dns-digitalocean)"
        echo "  - And more..."
        echo ""
        read -p "Continue anyway? (y/N): " confirm
        if [ "$confirm" != "y" ] && [ "$confirm" != "Y" ]; then
            echo "Cancelled."
            exit 0
        fi
    fi
    
    # Check if domain already exists
    if domain_exists "$domain"; then
        local owner_info=$(domain_get_owner "$domain")
        local type=$(echo "$owner_info" | cut -d: -f1)
        local owner_domain=$(echo "$owner_info" | cut -d: -f2)
        local owner_app=$(echo "$owner_info" | cut -d: -f3)
        
        if [ "$type" = "domain" ]; then
            echo -e "${RED}Error: Domain '$domain' is already taken${NC}"
            echo -e "  Used as ${BOLD}primary domain${NC} by app: ${CYAN}$owner_app${NC}"
        else
            echo -e "${RED}Error: Domain '$domain' is already taken${NC}"
            echo -e "  Used as ${BOLD}alias${NC} of domain '${CYAN}$owner_domain${NC}' (app: ${CYAN}$owner_app${NC})"
        fi
        exit 1
    fi
    
    # Parse aliases
    local alias_array=()
    if [ -n "$aliases" ]; then
        IFS=',' read -ra alias_array <<< "$aliases"
        # Trim spaces
        for i in "${!alias_array[@]}"; do
            alias_array[$i]=$(echo "${alias_array[$i]}" | xargs)
        done
        
        # Check if any alias already exists and check for wildcards
        for alias in "${alias_array[@]}"; do
            # Check for wildcard in alias
            if [[ "$alias" == *"*"* ]]; then
                echo -e "${YELLOW}Note: Wildcard alias detected: $alias${NC}"
                echo "This will require DNS validation for SSL."
            fi
            
            if domain_exists "$alias"; then
                local owner_info=$(domain_get_owner "$alias")
                local type=$(echo "$owner_info" | cut -d: -f1)
                local owner_domain=$(echo "$owner_info" | cut -d: -f2)
                local owner_app=$(echo "$owner_info" | cut -d: -f3)
                
                if [ "$type" = "domain" ]; then
                    echo -e "${RED}Error: Alias '$alias' is already taken${NC}"
                    echo -e "  Used as ${BOLD}primary domain${NC} by app: ${CYAN}$owner_app${NC}"
                else
                    echo -e "${RED}Error: Alias '$alias' is already taken${NC}"
                    echo -e "  Used as ${BOLD}alias${NC} of domain '${CYAN}$owner_domain${NC}' (app: ${CYAN}$owner_app${NC})"
                fi
                exit 1
            fi
        done
    fi
    
    # Check if app exists
    if ! json_has_key "${VIRTUALHOSTS_FILE}" "$app"; then
        echo -e "${RED}Error: Virtual host '$app' not found${NC}"
        exit 1
    fi
    
    echo ""
    echo -e "${CYAN}Assigning domain...${NC}"
    
    # Get app data
    local vhost=$(json_get "${VIRTUALHOSTS_FILE}" "$app")
    local php_version=$(echo "$vhost" | jq -r '.php_version')
    
    # Build aliases string for nginx
    local aliases_str="${alias_array[*]}"
    
    # Update Nginx configuration
    echo "  → Updating Nginx configuration..."
    update_nginx_domain "$app" "$domain" "$aliases_str"
    
    # Reload nginx
    echo "  → Reloading Nginx..."
    nginx_reload
    
    # Save to storage
    local domain_data=$(jq -n \
        --arg vh "$app" \
        --argjson aliases "$(printf '%s\n' "${alias_array[@]}" | jq -R . | jq -s .)" \
        '{app: $vh, aliases: $aliases, ssl: false}')
    
    json_set "${DOMAINS_FILE}" "$domain" "$domain_data"
    
    echo ""
    echo -e "${GREEN}${BOLD}Domain assigned successfully!${NC}"
    echo "─────────────────────────────────────"
    echo -e "Domain:       ${CYAN}$domain${NC}"
    echo -e "Aliases:      ${CYAN}${aliases:-(none)}${NC}"
    echo -e "Virtual Host: ${CYAN}$app${NC}"
    echo ""
    echo -e "${YELLOW}To enable SSL, run:${NC}"
    echo -e "  sudo -u $app /home/$app/ssl.sh"
    echo ""
}

# List domains
domain_list() {
    init_storage
    
    echo -e "${BOLD}Domains${NC}"
    echo "─────────────────────────────────────"
    echo ""
    
    local domains=$(json_keys "${DOMAINS_FILE}")
    
    if [ -z "$domains" ]; then
        echo "No domains found."
        echo ""
        return
    fi
    
    printf "%-30s %-40s %-15s\n" "DOMAIN" "ALIASES" "VIRTUALHOST"
    echo "─────────────────────────────────────────────────────────────────────────────────────"
    
    for domain in $domains; do
        local domain_data=$(json_get "${DOMAINS_FILE}" "$domain")
        local app=$(echo "$domain_data" | jq -r '.app')
        local aliases=$(echo "$domain_data" | jq -r '.aliases[]?' 2>/dev/null | tr '\n' ', ' | sed 's/,$//')
        aliases=${aliases:-(none)}
        
        printf "%-30s %-40s %-15s\n" "$domain" "$aliases" "$app"
    done
    
    echo ""
}

# Delete domain
domain_delete() {
    local domain=$1
    local force=false
    
    # Parse arguments
    for arg in "$@"; do
        case $arg in
            --force)
                force=true
                ;;
            *)
                domain="$arg"
                ;;
        esac
    done
    
    if [ -z "$domain" ]; then
        echo -e "${RED}Error: Domain required${NC}"
        echo "Usage: cipi domain delete <domain> [--force]"
        exit 1
    fi
    
    if ! json_has_key "${DOMAINS_FILE}" "$domain"; then
        echo -e "${RED}Error: Domain '$domain' not found${NC}"
        exit 1
    fi
    
    # Get domain data
    local domain_data=$(json_get "${DOMAINS_FILE}" "$domain")
    local aliases=$(echo "$domain_data" | jq -r '.aliases[]?' 2>/dev/null)
    local has_ssl=$(echo "$domain_data" | jq -r '.ssl // false')
    
    # Check if domain has aliases
    if [ -n "$aliases" ]; then
        echo -e "${RED}Error: Cannot delete domain with aliases${NC}"
        echo "Please remove all aliases first:"
        echo "$aliases" | while read -r alias; do
            echo "  sudo cipi alias remove $domain $alias"
        done
        exit 1
    fi
    
    # Confirm deletion
    echo -e "${YELLOW}${BOLD}Warning: This will unassign the domain from the virtual host${NC}"
    if [ "$has_ssl" = "true" ]; then
        echo -e "${YELLOW}SSL certificate for this domain will be revoked and deleted${NC}"
    fi
    
    if [ "$force" != "true" ]; then
        read -p "Continue? (y/N): " confirm
        
        if [ "$confirm" != "y" ] && [ "$confirm" != "Y" ]; then
            echo "Deletion cancelled."
            exit 0
        fi
    fi
    
    local app=$(echo "$domain_data" | jq -r '.app')
    
    # Revoke and delete SSL certificate if exists
    if [ "$has_ssl" = "true" ] && [ -d "/etc/letsencrypt/live/$domain" ]; then
        echo ""
        echo -e "${CYAN}→ Revoking SSL certificate...${NC}"
        certbot revoke --cert-name "$domain" --non-interactive 2>/dev/null || true
        
        echo -e "${CYAN}→ Deleting SSL certificate...${NC}"
        certbot delete --cert-name "$domain" --non-interactive 2>/dev/null || true
    fi
    
    # Reset Nginx configuration to use username only
    echo -e "${CYAN}→ Resetting Nginx configuration...${NC}"
    local vhost=$(json_get "${VIRTUALHOSTS_FILE}" "$app")
    local php_version=$(echo "$vhost" | jq -r '.php_version')
    
    create_nginx_config "$app" "" "$php_version"
    
    # Remove from storage
    json_delete "${DOMAINS_FILE}" "$domain"
    
    # Reload nginx
    nginx_reload
    
    echo ""
    echo -e "${GREEN}${BOLD}Domain deleted successfully!${NC}"
    echo -e "The virtual host '${CYAN}$app${NC}' is now accessible only via its username"
    echo ""
}

# Add alias to domain
alias_add() {
    local domain=$1
    local alias=$2
    
    if [ -z "$domain" ] || [ -z "$alias" ]; then
        echo -e "${RED}Error: Domain and alias required${NC}"
        echo "Usage: cipi alias add <domain> <alias>"
        exit 1
    fi
    
    if ! json_has_key "${DOMAINS_FILE}" "$domain"; then
        echo -e "${RED}Error: Domain '$domain' not found${NC}"
        exit 1
    fi
    
    # Check for wildcard in alias
    if [[ "$alias" == *"*"* ]]; then
        echo -e "${YELLOW}${BOLD}Warning: Wildcard alias detected${NC}"
        echo "Wildcard domains (*.example.com) require DNS validation for SSL."
        echo ""
    fi
    
    if domain_exists "$alias"; then
        local owner_info=$(domain_get_owner "$alias")
        local type=$(echo "$owner_info" | cut -d: -f1)
        local owner_domain=$(echo "$owner_info" | cut -d: -f2)
        local owner_app=$(echo "$owner_info" | cut -d: -f3)
        
        if [ "$type" = "domain" ]; then
            echo -e "${RED}Error: Alias '$alias' is already taken${NC}"
            echo -e "  Used as ${BOLD}primary domain${NC} by app: ${CYAN}$owner_app${NC}"
        else
            echo -e "${RED}Error: Alias '$alias' is already taken${NC}"
            echo -e "  Used as ${BOLD}alias${NC} of domain '${CYAN}$owner_domain${NC}' (app: ${CYAN}$owner_app${NC})"
        fi
        exit 1
    fi
    
    echo ""
    echo -e "${CYAN}Adding alias...${NC}"
    
    # Get domain data
    local domain_data=$(json_get "${DOMAINS_FILE}" "$domain")
    local app=$(echo "$domain_data" | jq -r '.app')
    local has_ssl=$(echo "$domain_data" | jq -r '.ssl // false')
    
    # Add alias to array
    local tmp=$(mktemp)
    echo "$domain_data" | jq ".aliases += [\"$alias\"]" > "$tmp"
    local new_domain_data=$(cat "$tmp")
    rm "$tmp"
    
    # Update storage
    json_set "${DOMAINS_FILE}" "$domain" "$new_domain_data"
    
    # Get all aliases for nginx
    local all_aliases=$(echo "$new_domain_data" | jq -r '.aliases[]?' 2>/dev/null | tr '\n' ' ')
    
    # Update Nginx
    echo -e "  → Updating Nginx configuration..."
    local vhost=$(json_get "${VIRTUALHOSTS_FILE}" "$app")
    local php_version=$(echo "$vhost" | jq -r '.php_version')
    
    if [ "$has_ssl" = "true" ]; then
        # Renew certificate with new alias
        echo -e "  → Renewing SSL certificate with new alias..."
        local domain_list="-d $domain"
        for a in $all_aliases; do
            domain_list="$domain_list -d $a"
        done
        
        certbot certonly --nginx $domain_list --non-interactive --agree-tos --expand --cert-name "$domain" 2>&1 | grep -v "^Saving debug log"
        
        if [ ${PIPESTATUS[0]} -eq 0 ]; then
            add_ssl_to_nginx "$app" "$domain" "$all_aliases" "$php_version"
        else
            echo -e "${YELLOW}Warning: SSL certificate renewal failed. Alias added but SSL not updated.${NC}"
            echo -e "Run this manually: sudo -u $app /home/$app/ssl.sh"
        fi
    else
        update_nginx_domain "$app" "$domain" "$all_aliases"
    fi
    
    nginx_reload
    
    echo ""
    echo -e "${GREEN}${BOLD}Alias added successfully!${NC}"
    if [ "$has_ssl" = "true" ]; then
        echo -e "SSL certificate updated to include: ${CYAN}$alias${NC}"
    fi
    echo ""
}

# Remove alias from domain
alias_remove() {
    local domain=$1
    local alias=$2
    
    if [ -z "$domain" ] || [ -z "$alias" ]; then
        echo -e "${RED}Error: Domain and alias required${NC}"
        echo "Usage: cipi alias remove <domain> <alias>"
        exit 1
    fi
    
    if ! json_has_key "${DOMAINS_FILE}" "$domain"; then
        echo -e "${RED}Error: Domain '$domain' not found${NC}"
        exit 1
    fi
    
    echo ""
    echo -e "${CYAN}Removing alias...${NC}"
    
    # Get domain data
    local domain_data=$(json_get "${DOMAINS_FILE}" "$domain")
    local app=$(echo "$domain_data" | jq -r '.app')
    local has_ssl=$(echo "$domain_data" | jq -r '.ssl // false')
    
    # Check if alias exists in this domain
    local alias_exists=$(echo "$domain_data" | jq -r ".aliases[]? | select(. == \"$alias\")")
    if [ -z "$alias_exists" ]; then
        echo -e "${RED}Error: Alias '$alias' not found in domain '$domain'${NC}"
        exit 1
    fi
    
    # Remove alias from array
    local tmp=$(mktemp)
    echo "$domain_data" | jq "del(.aliases[] | select(. == \"$alias\"))" > "$tmp"
    local new_domain_data=$(cat "$tmp")
    rm "$tmp"
    
    # Update storage
    json_set "${DOMAINS_FILE}" "$domain" "$new_domain_data"
    
    # Get all aliases for nginx
    local all_aliases=$(echo "$new_domain_data" | jq -r '.aliases[]?' 2>/dev/null | tr '\n' ' ')
    
    # Update Nginx
    echo -e "  → Updating Nginx configuration..."
    local vhost=$(json_get "${VIRTUALHOSTS_FILE}" "$app")
    local php_version=$(echo "$vhost" | jq -r '.php_version')
    
    if [ "$has_ssl" = "true" ]; then
        # Renew certificate without removed alias
        echo -e "  → Renewing SSL certificate without removed alias..."
        local domain_list="-d $domain"
        if [ -n "$all_aliases" ]; then
            for a in $all_aliases; do
                domain_list="$domain_list -d $a"
            done
        fi
        
        certbot certonly --nginx $domain_list --non-interactive --agree-tos --expand --cert-name "$domain" 2>&1 | grep -v "^Saving debug log"
        
        if [ ${PIPESTATUS[0]} -eq 0 ]; then
            add_ssl_to_nginx "$app" "$domain" "$all_aliases" "$php_version"
        else
            echo -e "${YELLOW}Warning: SSL certificate renewal failed. Alias removed but SSL not updated.${NC}"
            echo -e "Run this manually: sudo -u $app /home/$app/ssl.sh"
        fi
    else
        update_nginx_domain "$app" "$domain" "$all_aliases"
    fi
    
    nginx_reload
    
    echo ""
    echo -e "${GREEN}${BOLD}Alias removed successfully!${NC}"
    if [ "$has_ssl" = "true" ]; then
        echo -e "SSL certificate updated to exclude: ${CYAN}$alias${NC}"
    fi
    echo ""
}

