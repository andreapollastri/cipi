#!/bin/bash

#############################################
# System Functions
#############################################

CLAMAV_LOG="/var/log/cipi/clamav-scan.log"

# Get server IP
get_server_ip() {
    curl -s https://checkip.amazonaws.com || echo "Unknown"
}

# Get hostname
get_hostname() {
    hostname
}

# Get CPU usage
get_cpu_usage() {
    local load=$(cat /proc/loadavg | awk '{print $1}')
    local cores=$(nproc)
    local usage=$(echo "scale=0; ($load / $cores) * 100" | bc)
    
    # Cap at 100%
    if [ "$usage" -gt 100 ]; then
        usage=100
    fi
    
    echo "${usage}%"
}

# Get memory usage
get_memory_usage() {
    free | grep Mem | awk '{printf "%.0f%%", $3/$2 * 100.0}'
}

# Get disk usage
get_disk_usage() {
    df -h / | awk 'NR==2 {print $5}'
}

# Get disk space for path
get_disk_space() {
    local path=$1
    if [ -d "$path" ]; then
        du -sh "$path" 2>/dev/null | awk '{print $1}'
    else
        echo "0"
    fi
}

# Check Ubuntu version
check_ubuntu_version() {
    if [ ! -f /etc/os-release ]; then
        return 1
    fi
    
    . /etc/os-release
    
    if [ "$ID" != "ubuntu" ]; then
        return 1
    fi
    
    # Check if version is 24.04 or higher
    version_compare=$(echo "$VERSION_ID >= 24.04" | bc)
    
    if [ "$version_compare" -eq 1 ]; then
        return 0
    else
        return 1
    fi
}

# Create system user
create_system_user() {
    local username=$1
    local password=$2
    
    useradd -m -s /bin/bash "$username"
    echo "$username:$password" | chpasswd
    
    return $?
}

# Delete system user
delete_system_user() {
    local username=$1
    userdel -r "$username" 2>/dev/null
    return $?
}

# Restart service
restart_service() {
    local service=$1
    systemctl restart "$service"
    return $?
}

# Check service status
check_service_status() {
    local service=$1
    systemctl is-active --quiet "$service"
    return $?
}

# Get service status
get_service_status() {
    local service=$1
    if check_service_status "$service"; then
        echo -e "${GREEN}●${NC} running"
    else
        echo -e "${RED}●${NC} stopped"
    fi
}

# View antivirus scan logs
view_antivirus_logs() {
    local lines=${1:-50}  # Default 50 lines
    
    if [ ! -f "$CLAMAV_LOG" ]; then
        echo -e "${YELLOW}No antivirus scan logs found yet.${NC}"
        echo ""
        echo "ClamAV scans run daily at 3 AM automatically."
        echo ""
        echo "To run a manual scan now:"
        echo -e "  ${CYAN}sudo /usr/local/bin/cipi-scan${NC}"
        echo ""
        return
    fi
    
    echo -e "${BOLD}ClamAV Antivirus Scan Logs${NC}"
    echo "─────────────────────────────────────"
    echo ""
    echo -e "${CYAN}Log file:${NC} $CLAMAV_LOG"
    echo -e "${CYAN}Showing last $lines lines:${NC}"
    echo ""
    
    tail -n "$lines" "$CLAMAV_LOG"
    
    echo ""
    echo "─────────────────────────────────────"
    echo -e "${CYAN}Commands:${NC}"
    echo "  View more lines:        cipi logs --lines=100"
    echo "  Follow live:            sudo tail -f $CLAMAV_LOG"
    echo "  Run manual scan:        sudo /usr/local/bin/cipi-scan"
    echo "  Update virus database:  sudo freshclam"
    echo ""
}

