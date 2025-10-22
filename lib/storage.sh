#!/bin/bash

#############################################
# Storage Functions - JSON Data Management
#############################################

STORAGE_DIR="/etc/cipi"
APPS_FILE="${STORAGE_DIR}/apps.json"
DOMAINS_FILE="${STORAGE_DIR}/domains.json"
DATABASES_FILE="${STORAGE_DIR}/databases.json"
CONFIG_FILE="${STORAGE_DIR}/config.json"

# Initialize storage
init_storage() {
    mkdir -p "${STORAGE_DIR}"
    chmod 700 "${STORAGE_DIR}"
    
    for file in "${APPS_FILE}" "${DOMAINS_FILE}" "${DATABASES_FILE}" "${CONFIG_FILE}"; do
        if [ ! -f "$file" ]; then
            echo "{}" > "$file"
            chmod 600 "$file"
        fi
    done
}

# Read JSON file
json_read() {
    local file=$1
    cat "$file" 2>/dev/null || echo "{}"
}

# Write JSON file
json_write() {
    local file=$1
    local content=$2
    echo "$content" | jq '.' > "$file"
    chmod 600 "$file"
}

# Get value from JSON
json_get() {
    local file=$1
    local key=$2
    jq -r ".[\"$key\"]" "$file" 2>/dev/null
}

# Set value in JSON
json_set() {
    local file=$1
    local key=$2
    local value=$3
    local tmp=$(mktemp)
    
    jq ".[\"$key\"] = $value" "$file" > "$tmp"
    mv "$tmp" "$file"
    chmod 600 "$file"
}

# Delete key from JSON
json_delete() {
    local file=$1
    local key=$2
    local tmp=$(mktemp)
    
    jq "del(.[\"$key\"])" "$file" > "$tmp"
    mv "$tmp" "$file"
    chmod 600 "$file"
}

# Get all keys from JSON
json_keys() {
    local file=$1
    jq -r 'keys[]' "$file" 2>/dev/null
}

# Check if key exists
json_has_key() {
    local file=$1
    local key=$2
    jq -e ".[\"$key\"]" "$file" >/dev/null 2>&1
}

# Generate unique username
generate_username() {
    while true; do
        username="u$(shuf -i 100000-999999 -n 1)"
        if ! json_has_key "${APPS_FILE}" "$username"; then
            echo "$username"
            break
        fi
    done
}

# Generate database name
generate_dbname() {
    while true; do
        dbname="db$(shuf -i 100000-999999 -n 1)"
        if ! json_has_key "${DATABASES_FILE}" "$dbname"; then
            echo "$dbname"
            break
        fi
    done
}

# Generate database username
generate_db_username() {
    echo "db$(shuf -i 100000-999999 -n 1)"
}

# Generate secure password
generate_password() {
    local length=${1:-24}
    tr -dc 'A-Za-z0-9!@#$%^&*()_+{}|:<>?=' < /dev/urandom | head -c "$length"
}

# Check if domain exists (as domain or alias)
domain_exists() {
    local domain=$1
    local domains=$(json_read "${DOMAINS_FILE}")
    
    # Check if it's a primary domain
    if echo "$domains" | jq -e ".[\"$domain\"]" >/dev/null 2>&1; then
        return 0
    fi
    
    # Check if it's an alias
    if echo "$domains" | jq -e ".[] | select(.aliases[]? == \"$domain\")" >/dev/null 2>&1; then
        return 0
    fi
    
    return 1
}

# Get where domain/alias is used (returns "domain:appname" or "alias:domain:appname")
domain_get_owner() {
    local search=$1
    local domains=$(json_read "${DOMAINS_FILE}")
    
    # Check if it's a primary domain
    local app=$(echo "$domains" | jq -r ".[\"$search\"].app // empty")
    if [ -n "$app" ]; then
        echo "domain:$search:$app"
        return 0
    fi
    
    # Check if it's an alias
    local result=$(echo "$domains" | jq -r "to_entries[] | select(.value.aliases[]? == \"$search\") | \"alias:\(.key):\(.value.app)\"" | head -n 1)
    if [ -n "$result" ]; then
        echo "$result"
        return 0
    fi
    
    return 1
}

# Get app by domain
get_app_by_domain() {
    local domain=$1
    json_read "${DOMAINS_FILE}" | jq -r ".[\"$domain\"].app" 2>/dev/null
}

# Get config value
get_config() {
    local key=$1
    local default=$2
    local value=$(json_get "${CONFIG_FILE}" "$key")
    
    if [ "$value" = "null" ] || [ -z "$value" ]; then
        echo "$default"
    else
        echo "$value"
    fi
}

# Set config value
set_config() {
    local key=$1
    local value=$2
    json_set "${CONFIG_FILE}" "$key" "\"$value\""
}

