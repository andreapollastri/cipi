#!/bin/bash

#############################################
# Service Management Functions
#############################################

# Restart service
service_restart() {
    local service=$1
    
    if [ -z "$service" ]; then
        echo -e "${RED}Error: Service name required${NC}"
        echo "Usage: cipi service restart <service>"
        echo ""
        echo "Available services: nginx, php, mysql, supervisor, redis"
        exit 1
    fi
    
    # Map service names
    case $service in
        php)
            # Restart all PHP-FPM services
            echo -e "${CYAN}Restarting all PHP-FPM services...${NC}"
            local php_versions=($(get_installed_php_versions))
            for version in "${php_versions[@]}"; do
                echo "  → Restarting php${version}-fpm..."
                systemctl restart "php${version}-fpm"
            done
            echo -e "${GREEN}All PHP-FPM services restarted!${NC}"
            ;;
        nginx)
            echo -e "${CYAN}Restarting nginx...${NC}"
            systemctl restart nginx
            echo -e "${GREEN}Nginx restarted!${NC}"
            ;;
        mysql)
            echo -e "${CYAN}Restarting MySQL...${NC}"
            systemctl restart mysql
            echo -e "${GREEN}MySQL restarted!${NC}"
            ;;
        supervisor)
            echo -e "${CYAN}Restarting Supervisor...${NC}"
            systemctl restart supervisor
            echo -e "${GREEN}Supervisor restarted!${NC}"
            ;;
        redis)
            echo -e "${CYAN}Restarting Redis...${NC}"
            systemctl restart redis-server
            echo -e "${GREEN}Redis restarted!${NC}"
            ;;
        *)
            echo -e "${RED}Error: Unknown service '$service'${NC}"
            echo "Available services: nginx, php, mysql, supervisor, redis"
            exit 1
            ;;
    esac
}

