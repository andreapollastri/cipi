#!/bin/bash

#############################################
# Auto-Update Functions
#############################################

GITHUB_REPO="andreapollastri/cipi"
GITHUB_API="https://api.github.com/repos/${GITHUB_REPO}/releases/latest"
GITHUB_RAW="https://raw.githubusercontent.com/${GITHUB_REPO}"

# Get latest version from GitHub
get_latest_version() {
    curl -s "${GITHUB_API}" | grep '"tag_name":' | sed -E 's/.*"([^"]+)".*/\1/'
}

# Get current version
get_current_version() {
    echo "${CIPI_VERSION}"
}

# Compare versions
version_gt() {
    test "$(printf '%s\n' "$@" | sort -V | head -n 1)" != "$1"
}

# Update Cipi
update_cipi() {
    echo -e "${BOLD}Cipi Update${NC}"
    echo "─────────────────────────────────────"
    echo ""
    
    local current_version=$(get_current_version)
    echo -e "Current version: ${CYAN}${current_version}${NC}"
    
    echo -e "${CYAN}Checking for updates...${NC}"
    local latest_version=$(get_latest_version)
    
    if [ -z "$latest_version" ]; then
        echo -e "${RED}Error: Could not fetch latest version${NC}"
        exit 1
    fi
    
    echo -e "Latest version:  ${GREEN}${latest_version}${NC}"
    echo ""
    
    if ! version_gt "$latest_version" "$current_version"; then
        echo -e "${GREEN}Cipi is already up to date!${NC}"
        exit 0
    fi
    
    echo -e "${YELLOW}A new version is available!${NC}"
    read -p "Do you want to update? (y/N): " confirm
    
    if [ "$confirm" != "y" ] && [ "$confirm" != "Y" ]; then
        echo "Update cancelled."
        exit 0
    fi
    
    echo ""
    echo -e "${CYAN}Updating Cipi...${NC}"
    
    # Create temporary directory
    local tmp_dir=$(mktemp -d)
    
    # Download latest release
    echo "  → Downloading latest release..."
    cd "$tmp_dir"
    curl -sL "https://github.com/${GITHUB_REPO}/archive/refs/tags/${latest_version}.tar.gz" | tar xz
    
    if [ $? -ne 0 ]; then
        echo -e "${RED}Error: Failed to download update${NC}"
        rm -rf "$tmp_dir"
        exit 1
    fi
    
    # Find extracted directory
    local extract_dir=$(ls -d cipi-*/ | head -n 1)
    
    if [ -z "$extract_dir" ]; then
        echo -e "${RED}Error: Could not find extracted files${NC}"
        rm -rf "$tmp_dir"
        exit 1
    fi
    
    # Backup current installation
    echo "  → Creating backup..."
    cp -r "${CIPI_DIR}" "${CIPI_DIR}.backup"
    
    # Install new version
    echo "  → Installing new version..."
    cp -r "${tmp_dir}/${extract_dir}"/* "${CIPI_DIR}/"
    chmod +x "${CIPI_DIR}/cipi"
    
    # Cleanup
    echo "  → Cleaning up..."
    rm -rf "$tmp_dir"
    
    echo ""
    echo -e "${GREEN}${BOLD}Cipi updated successfully!${NC}"
    echo -e "New version: ${CYAN}${latest_version}${NC}"
    echo ""
    echo "Backup saved at: ${CIPI_DIR}.backup"
    echo ""
}

# Check for updates (used by cron)
check_updates() {
    local current_version=$(get_current_version)
    local latest_version=$(get_latest_version)
    
    if version_gt "$latest_version" "$current_version"; then
        echo "Update available: $current_version -> $latest_version"
        return 0
    else
        echo "Cipi is up to date ($current_version)"
        return 1
    fi
}

