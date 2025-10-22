#!/bin/bash

#############################################
# Main Command Functions
#############################################

# Status command
cmd_status() {
    show_logo
    echo -e "${BOLD}SERVER STATUS${NC}"
    echo "─────────────────────────────────────"
    echo -e "IP:       ${CYAN}$(get_server_ip)${NC}"
    echo -e "HOSTNAME: ${CYAN}$(get_hostname)${NC}"
    echo -e "CPU:      ${CYAN}$(get_cpu_usage)${NC}"
    echo -e "RAM:      ${CYAN}$(get_memory_usage)${NC}"
    echo -e "HDD:      ${CYAN}$(get_disk_usage)${NC}"
    echo ""
    
    echo -e "${BOLD}SERVICES${NC}"
    echo "─────────────────────────────────────"
    echo -e "nginx:      $(get_service_status nginx)"
    echo -e "mysql:      $(get_service_status mysql)"
    echo -e "php8.4-fpm: $(get_service_status php8.4-fpm)"
    echo -e "supervisor: $(get_service_status supervisor)"
    echo -e "redis:      $(get_service_status redis-server)"
    echo ""
}

# Logs command
cmd_logs() {
    local lines=50
    
    # Parse arguments
    for arg in "$@"; do
        case $arg in
            --lines=*)
                lines="${arg#*=}"
                ;;
        esac
    done
    
    view_antivirus_logs "$lines"
}

# App commands
cmd_app() {
    local subcmd=$1
    shift
    
    case $subcmd in
        create)
            app_create "$@"
            ;;
        list)
            app_list "$@"
            ;;
        show)
            app_show "$@"
            ;;
        edit)
            app_edit "$@"
            ;;
        env)
            app_env "$@"
            ;;
        crontab)
            app_crontab "$@"
            ;;
        password)
            app_password "$@"
            ;;
        delete)
            app_delete "$@"
            ;;
        *)
            echo -e "${RED}Unknown app command: $subcmd${NC}"
            echo "Usage: cipi app {create|list|show|edit|env|crontab|password|delete}"
            exit 1
            ;;
    esac
}

# Domain commands
cmd_domain() {
    local subcmd=$1
    shift
    
    case $subcmd in
        create)
            domain_create "$@"
            ;;
        list)
            domain_list "$@"
            ;;
        delete)
            domain_delete "$@"
            ;;
        *)
            echo -e "${RED}Unknown domain command: $subcmd${NC}"
            echo "Usage: cipi domain {create|list|delete}"
            exit 1
            ;;
    esac
}

# Alias commands
cmd_alias() {
    local subcmd=$1
    shift
    
    case $subcmd in
        add)
            alias_add "$@"
            ;;
        remove)
            alias_remove "$@"
            ;;
        *)
            echo -e "${RED}Unknown alias command: $subcmd${NC}"
            echo "Usage: cipi alias {add|remove} <domain> <alias>"
            exit 1
            ;;
    esac
}

# Database commands
cmd_database() {
    local subcmd=$1
    shift
    
    case $subcmd in
        create)
            database_create "$@"
            ;;
        list)
            database_list "$@"
            ;;
        password)
            database_password "$@"
            ;;
        delete)
            database_delete "$@"
            ;;
        *)
            echo -e "${RED}Unknown database command: $subcmd${NC}"
            echo "Usage: cipi database {create|list|password|delete}"
            exit 1
            ;;
    esac
}

# PHP commands
cmd_php() {
    local subcmd=$1
    shift
    
    case $subcmd in
        list)
            php_list "$@"
            ;;
        install)
            php_install "$@"
            ;;
        switch)
            php_switch "$@"
            ;;
        *)
            echo -e "${RED}Unknown php command: $subcmd${NC}"
            echo "Usage: cipi php {list|install|switch}"
            exit 1
            ;;
    esac
}

# Service commands
cmd_service() {
    local subcmd=$1
    shift
    
    if [ "$subcmd" != "restart" ]; then
        echo -e "${RED}Unknown service command: $subcmd${NC}"
        echo "Usage: cipi service restart <service>"
        exit 1
    fi
    
    service_restart "$@"
}

# Update command
cmd_update() {
    update_cipi "$@"
}

