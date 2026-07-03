#!/bin/bash
#############################################
# Cipi — Database Management (PostgreSQL)
#############################################

_pg_is_installed() { command -v psql &>/dev/null && command -v pg_dump &>/dev/null; }

# Run a command as the postgres OS user (peer auth — no password needed as root).
_pg_run() { runuser -u postgres -- "$@"; }

pgdb_command() {
    local sub="${1:-}"; shift||true
    case "$sub" in
        install)  _pgdb_install "$@" ;;
        create)   _pgdb_create "$@" ;;
        list|ls)  _pgdb_list ;;
        delete)   _pgdb_delete "$@" ;;
        backup)   _pgdb_backup "$@" ;;
        restore)  _pgdb_restore "$@" ;;
        password) _pgdb_password "$@" ;;
        *) error "Use: install create list delete backup restore password"; exit 1 ;;
    esac
}

_pgdb_install() {
    if _pg_is_installed; then
        local ver; ver=$(_pg_run psql -t -c "SELECT version();" 2>/dev/null | grep -o 'PostgreSQL [0-9]*\.[0-9]*' | head -1)
        success "PostgreSQL is already installed${ver:+ (${ver})}"
        return 0
    fi
    step "Installing PostgreSQL..."
    apt-get install -y -qq postgresql postgresql-client 2>/dev/null
    systemctl enable postgresql 2>/dev/null
    systemctl start postgresql 2>/dev/null
    log_action "POSTGRESQL INSTALLED"
    success "PostgreSQL installed and running"
    info "Use 'cipi pgdb create' to create your first database"
}

_pgdb_create() {
    _pg_is_installed || { error "PostgreSQL is not installed. Run: cipi pgdb install"; exit 1; }
    parse_args "$@"
    local name="${ARG_name:-}" user="${ARG_user:-}"
    [[ -z "$name" ]] && read_input "Database name" "" name
    [[ -z "$name" ]] && { error "Name required"; exit 1; }
    validate_db_name "$name" || { error "Invalid name"; exit 1; }
    [[ -z "$user" ]] && user="$name"
    validate_db_name "$user" || { error "Invalid user"; exit 1; }
    local pass; pass=$(generate_password 40)
    _pg_run psql -v ON_ERROR_STOP=1 -c "CREATE USER \"${user}\" WITH ENCRYPTED PASSWORD '${pass}';" 2>&1 \
        || { error "Failed to create user '${user}' (may already exist)"; exit 1; }
    _pg_run psql -v ON_ERROR_STOP=1 -c "CREATE DATABASE \"${name}\" OWNER \"${user}\";" 2>&1 \
        || { error "Failed to create database '${name}' (may already exist)"; exit 1; }
    # Grant CREATE on public schema — required for apps on PostgreSQL 15+
    _pg_run psql -d "${name}" -c "GRANT ALL ON SCHEMA public TO \"${user}\";" 2>/dev/null || true
    vault_read pgdatabases.json | \
        jq --arg n "$name" --arg u "$user" '.[$n]={"user":$u,"created_at":(now|strftime("%Y-%m-%dT%H:%M:%SZ"))}' | \
        vault_write pgdatabases.json
    log_action "PGDB CREATED: $name"
    cipi_notify \
        "Cipi PostgreSQL database created: ${name} on $(hostname)" \
        "A PostgreSQL database was created.\n\nServer: $(hostname)\nDatabase: ${name}\nUser: ${user}\nTime: $(date '+%Y-%m-%d %H:%M:%S %Z')" \
        pgdb_create
    echo -e "\n${GREEN}✓${NC} Database: ${CYAN}${name}${NC}  User: ${CYAN}${user}${NC}  Password: ${CYAN}${pass}${NC}"
    echo -e "${YELLOW}Save this password!${NC}\n"
}

_pgdb_list() {
    _pg_is_installed || { error "PostgreSQL is not installed. Run: cipi pgdb install"; exit 1; }
    echo -e "\n${BOLD}PostgreSQL Databases${NC}"
    printf "  ${BOLD}%-20s %-15s %s${NC}\n" "DATABASE" "USER" "SIZE"
    _pg_run psql -t -A -F$'\t' -c "
        SELECT datname, pg_size_pretty(pg_database_size(datname))
        FROM pg_database
        WHERE datistemplate = false AND datname NOT IN ('postgres')
        ORDER BY datname;" 2>/dev/null | while IFS=$'\t' read -r db sz; do
        [[ -z "$db" ]] && continue
        local u; u=$(vault_read pgdatabases.json | jq -r --arg n "$db" '.[$n].user//"—"' 2>/dev/null)
        printf "  %-20s %-15s %s\n" "$db" "$u" "$sz"
    done; echo ""
}

_pgdb_delete() {
    _pg_is_installed || { error "PostgreSQL is not installed. Run: cipi pgdb install"; exit 1; }
    local name="${1:-}"; [[ -z "$name" ]] && { error "Usage: cipi pgdb delete <name> [--force]"; exit 1; }
    validate_db_name "$name" || { error "Invalid name"; exit 1; }
    parse_args "$@"
    if [[ "${ARG_force:-}" != "true" ]] && [[ -t 0 ]]; then
        confirm "Delete PostgreSQL database '${name}'?" || { info "Cancelled"; return; }
    fi
    local u; u=$(vault_read pgdatabases.json | jq -r --arg n "$name" '.[$n].user//$n' 2>/dev/null)
    validate_db_name "$u" || { error "Invalid stored user for '${name}'"; exit 1; }
    _pg_run psql -c "DROP DATABASE IF EXISTS \"${name}\";" 2>/dev/null
    _pg_run psql -c "DROP ROLE IF EXISTS \"${u}\";" 2>/dev/null
    vault_read pgdatabases.json | jq --arg n "$name" 'del(.[$n])' | vault_write pgdatabases.json
    log_action "PGDB DELETED: $name"
    cipi_notify \
        "Cipi PostgreSQL database deleted: ${name} on $(hostname)" \
        "A PostgreSQL database was deleted.\n\nServer: $(hostname)\nDatabase: ${name}\nTime: $(date '+%Y-%m-%d %H:%M:%S %Z')" \
        pgdb_delete
    success "'${name}' deleted"
}

_pgdb_backup() {
    _pg_is_installed || { error "PostgreSQL is not installed. Run: cipi pgdb install"; exit 1; }
    local name="${1:-}"; [[ -z "$name" ]] && { error "Usage: cipi pgdb backup <name>"; exit 1; }
    validate_db_name "$name" || { error "Invalid name"; exit 1; }
    local dir="${CIPI_LOG}/backups"; mkdir -p "$dir"
    local f="${dir}/${name}_$(date +%Y%m%d_%H%M%S).pg.sql.gz"
    step "Backing up '${name}'..."
    _pg_run pg_dump "$name" 2>/dev/null | gzip > "$f"
    success "Saved: ${f} ($(du -h "$f" | cut -f1))"
}

_pgdb_restore() {
    _pg_is_installed || { error "PostgreSQL is not installed. Run: cipi pgdb install"; exit 1; }
    local name="${1:-}" file="${2:-}"
    [[ -z "$name" || -z "$file" ]] && { error "Usage: cipi pgdb restore <name> <file> [--force]"; exit 1; }
    validate_db_name "$name" || { error "Invalid name"; exit 1; }
    [[ ! -f "$file" ]] && { error "File not found: $file"; exit 1; }
    parse_args "$@"
    if [[ "${ARG_force:-}" != "true" ]] && [[ -t 0 ]]; then
        confirm "Restore '${name}' from '${file}'?" || { info "Cancelled"; return; }
    fi
    if [[ "$file" == *.gz ]]; then
        gunzip -c "$file" | _pg_run psql "$name" 2>/dev/null
    else
        _pg_run psql "$name" < "$file" 2>/dev/null
    fi
    success "'${name}' restored"
}

_pgdb_password() {
    _pg_is_installed || { error "PostgreSQL is not installed. Run: cipi pgdb install"; exit 1; }
    local name="${1:-}"; [[ -z "$name" ]] && { error "Usage: cipi pgdb password <name>"; exit 1; }
    validate_db_name "$name" || { error "Invalid name"; exit 1; }
    local u; u=$(vault_read pgdatabases.json | jq -r --arg n "$name" '.[$n].user//$name' 2>/dev/null)
    validate_db_name "$u" || { error "Invalid stored user for '${name}'"; exit 1; }
    local np; np=$(generate_password 40)
    _pg_run psql -c "ALTER USER \"${u}\" WITH ENCRYPTED PASSWORD '${np}';" 2>/dev/null
    echo -e "\n${GREEN}✓${NC} New password for '${u}': ${CYAN}${np}${NC}"
    echo -e "${YELLOW}Update DB_PASSWORD in your .env!${NC}\n"
}
