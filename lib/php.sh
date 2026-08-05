#!/bin/bash
#############################################
# Cipi — PHP Version Management
#############################################

readonly _PHP_EXT="fpm common cli curl bcmath mbstring mysql sqlite3 pgsql memcached redis zip xml soap gd imagick intl"

php_command() {
    local sub="${1:-}"; shift||true
    case "$sub" in
        install) _php_install "$@" ;;
        remove)  _php_remove "$@" ;;
        switch)  _php_switch "$@" ;;
        upgrade) _php_upgrade "$@" ;;
        list|ls) _php_list "$@" ;;
        *) error "Use: install remove switch upgrade list"; exit 1 ;;
    esac
}

# Weekly cron + migration hook — idempotent root crontab entry.
_php_setup_upgrade_cron() {
    [[ "${EUID:-$(id -u)}" -ne 0 ]] && return 0
    if crontab -l 2>/dev/null | grep -qF 'cipi php upgrade'; then
        return 0
    fi
    if ! crontab -l 2>/dev/null | grep -q "CIPI CRON"; then
        return 0
    fi
    {
        crontab -l 2>/dev/null
        echo "# PHP security patch check (Sunday 3:30 AM)"
        echo '30 3 * * 0 /usr/local/bin/cipi-cron-notify php-upgrade /usr/local/bin/cipi php upgrade >> /var/log/cipi/php-upgrade.log 2>&1'
    } | crontab -
}

_php_upgrade() {
    local LOCK=/run/cipi-php-upgrade.lock apt_opts='-o DPkg::Lock::Timeout=300'
    exec 9>"$LOCK"
    if ! flock -n 9; then
        info "PHP upgrade check already running — skip"
        return 0
    fi

    local has_php=false v
    for v in 7.4 8.0 8.1 8.2 8.3 8.4 8.5; do
        if php_is_installed "$v"; then
            has_php=true
            break
        fi
    done
    [[ "$has_php" == false ]] && { info "No PHP installed — skip"; return 0; }

    local -a installed=()
    while IFS= read -r pkg; do
        [[ -n "$pkg" ]] && installed+=("$pkg")
    done < <(dpkg-query -W -f='${Package}\n' 2>/dev/null | grep -E '^(php[0-9]|php-|libphp)' || true)
    [[ ${#installed[@]} -eq 0 ]] && { info "No PHP packages found — skip"; return 0; }

    step "Checking for PHP package upgrades..."
    apt-get $apt_opts update -qq

    local out rc upgraded
    out=$(DEBIAN_FRONTEND=noninteractive apt-get $apt_opts install -y \
        -o Dpkg::Options::="--force-confdef" \
        -o Dpkg::Options::="--force-confold" \
        --only-upgrade "${installed[@]}" 2>&1) || rc=$?
    rc=${rc:-0}
    echo "$out"

    upgraded=$(echo "$out" | grep -oE '^[0-9]+ upgraded' | grep -oE '^[0-9]+' | head -1)
    upgraded=${upgraded:-0}

    if [[ "$upgraded" -eq 0 ]]; then
        info "PHP packages are up to date"
        return 0
    fi

    step "Restarting PHP-FPM services..."
    for v in 7.4 8.0 8.1 8.2 8.3 8.4 8.5; do
        php_is_installed "$v" && reload_php_fpm "$v" 2>/dev/null || true
    done

    log_action "PHP UPGRADE: ${upgraded} package(s)"
    cipi_notify \
        "Cipi PHP upgraded on $(hostname)" \
        "Installed PHP packages were upgraded.\n\nServer: $(hostname)\nPackages upgraded: ${upgraded}\nTime: $(date '+%Y-%m-%d %H:%M:%S %Z')" \
        php_upgrade
    success "PHP packages upgraded (${upgraded})"
    return "$rc"
}

_php_install() {
    local v="${1:-}"
    [[ -z "$v" ]] && { error "Usage: cipi php install <8.3|8.4|8.5>"; exit 1; }
    validate_php_version "$v" || { error "Invalid PHP version: $v (Deployer 8 requires PHP >= 8.3; use 8.3, 8.4 or 8.5)"; exit 1; }
    php_is_installed "$v" && { info "PHP $v already installed"; return; }
    # shellcheck source=/dev/null
    source "${CIPI_LIB}/php-apt.sh"
    step "Configuring PHP APT sources..."
    if ! php_setup_apt_sources; then
        info "No multi-PHP repo — using Ubuntu archive for PHP ${v} (single version only)"
    else
        info "PHP packages via $(php_apt_source_label)"
    fi
    apt-get update -qq
    step "Installing PHP ${v}..."
    local pkgs=""
    for e in $_PHP_EXT; do pkgs+=" php${v}-${e}"; done
    apt-get install -y -qq $pkgs
    cat > "/etc/php/${v}/fpm/conf.d/99-cipi.ini" <<EOF
memory_limit = 256M
upload_max_filesize = 256M
post_max_size = 256M
max_execution_time = 300
max_input_time = 300
expose_php = Off
EOF
    cat > "/etc/php/${v}/fpm/pool.d/www.conf" <<POOLEOF
[www]
user = www-data
group = www-data
listen = /run/php/php${v}-fpm.sock
listen.owner = www-data
listen.group = www-data
pm = ondemand
pm.max_children = 2
pm.process_idle_timeout = 10s
POOLEOF
    systemctl restart "php${v}-fpm"; systemctl enable "php${v}-fpm"
    log_action "PHP INSTALLED: $v"
    cipi_notify \
        "Cipi PHP installed: ${v} on $(hostname)" \
        "A PHP version was installed.\n\nServer: $(hostname)\nVersion: ${v}\nTime: $(date '+%Y-%m-%d %H:%M:%S %Z')" \
        php_install
    success "PHP ${v} installed"
}

# update-alternatives + FPM pool moves need a writable /etc (same remount-ro
# class as smtp / basicauth / vault).
_php_ensure_etc_writable() {
    if ! _cipi_ensure_config_writable; then
        error "Cannot write config (read-only filesystem)"
        [[ -n "${_CIPI_REMOUNT_ERR:-}" ]] && error "Remount failed: ${_CIPI_REMOUNT_ERR}"
        error "Try as root: mount -n -o remount,rw / && cipi php switch <ver>"
        return 1
    fi
    if ! _cipi_path_writable /etc; then
        _cipi_remount_rw /etc || _cipi_remount_rw / || true
    fi
    if ! _cipi_path_writable /etc; then
        error "Cannot write /etc (read-only file system)"
        [[ -n "${_CIPI_REMOUNT_ERR:-}" ]] && error "Remount failed: ${_CIPI_REMOUNT_ERR}"
        error "Try as root: mount -n -o remount,rw / && cipi php switch <ver>"
        return 1
    fi
    # Stale atomic-swap leftovers from a previous RO failure block --set.
    rm -f /etc/alternatives/php.dpkg-tmp \
          /etc/alternatives/phar.dpkg-tmp \
          /etc/alternatives/phar.phar.dpkg-tmp \
          /etc/alternatives/phpdbg.dpkg-tmp 2>/dev/null || true
    return 0
}

# Move cipi-api FPM pool onto PHP $1 when it still lives under another version.
_php_migrate_api_pool() {
    local v="$1"
    [[ -f "${CIPI_CONFIG}/api.json" ]] || return 0

    local old_pool="" new_pool="/etc/php/${v}/fpm/pool.d/cipi-api.conf"
    local pv
    for pv in 7.4 8.0 8.1 8.2 8.3 8.4 8.5; do
        [[ "$pv" == "$v" ]] && continue
        if [[ -f "/etc/php/${pv}/fpm/pool.d/cipi-api.conf" ]]; then
            old_pool="/etc/php/${pv}/fpm/pool.d/cipi-api.conf"
            break
        fi
    done
    [[ -n "$old_pool" ]] || return 0

    _php_ensure_etc_writable || return 1

    step "Migrating API FPM pool..."
    mv "$old_pool" "$new_pool"
    local old_ver; old_ver=$(echo "$old_pool" | grep -oP '/php/\K[0-9]+\.[0-9]+')
    reload_php_fpm "$old_ver" 2>/dev/null || true
    reload_php_fpm "$v"
    success "API FPM pool → PHP ${v}"
}

_php_switch() {
    local v="${1:-}"
    # Panel API builds args with escapeshellarg; strip leftover wrapping quotes.
    v="${v#\'}"; v="${v%\'}"; v="${v#\"}"; v="${v%\"}"
    [[ -z "$v" ]] && { error "Usage: cipi php switch <8.3|8.4|8.5>"; exit 1; }
    validate_php_version "$v" || { error "Invalid PHP version: $v (use 8.3, 8.4 or 8.5)"; exit 1; }
    php_is_installed "$v" || { error "PHP $v not installed. Run: cipi php install $v"; exit 1; }

    local cur; cur=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || echo "")
    local already=false
    [[ "$cur" == "$v" ]] && already=true

    if [[ "$already" == "true" ]]; then
        info "PHP $v is already the system default"
        # A previous attempt may have switched CLI then aborted before
        # migrating the API pool — finish that work if needed.
        _php_migrate_api_pool "$v" || exit 1
        return 0
    fi

    local alt_bin="/usr/bin/php${v}"
    [[ -x "$alt_bin" ]] || { error "PHP binary not found: ${alt_bin}"; exit 1; }

    # Kernel may have remounted / read-only; update-alternatives needs to
    # rewrite /etc/alternatives/* (fails with php.dpkg-tmp on RO root).
    _php_ensure_etc_writable || exit 1

    step "Switching system PHP CLI to ${v}..."
    # Prefer exit code, but also verify the resolved /usr/bin/php target
    # (slave-link warnings can leave a non-zero status after a good master set).
    local alt_out="" alt_rc=0
    alt_out=$(/usr/bin/update-alternatives --set php "$alt_bin" 2>&1) && alt_rc=0 || alt_rc=$?
    [[ -n "$alt_out" ]] && echo "$alt_out"

    # If RO aborted mid-swap, remount + clean tmp and retry once.
    if [[ "$alt_rc" -ne 0 ]] && echo "$alt_out" | grep -qiE 'Read-only file system|php\.dpkg-tmp'; then
        info "Retrying php switch after remount..."
        _php_ensure_etc_writable || exit 1
        alt_out=$(/usr/bin/update-alternatives --set php "$alt_bin" 2>&1) && alt_rc=0 || alt_rc=$?
        [[ -n "$alt_out" ]] && echo "$alt_out"
    fi

    local now resolved
    now=$(readlink -f /usr/bin/php 2>/dev/null || true)
    resolved=$(readlink -f "$alt_bin" 2>/dev/null || true)
    if [[ -z "$resolved" || "$now" != "$resolved" ]]; then
        error "Failed to switch system PHP to ${v} (php → ${now:-unknown}, want ${resolved:-$alt_bin})"
        if echo "$alt_out" | grep -qi 'Read-only file system'; then
            error "Root filesystem is read-only. As root: mount -n -o remount,rw / && cipi php switch ${v}"
        fi
        exit 1
    fi
    if [[ "$alt_rc" -ne 0 ]]; then
        info "update-alternatives exited ${alt_rc} (slave link warning); CLI already points to PHP ${v}"
    fi
    success "CLI: php → ${alt_bin}"

    _php_migrate_api_pool "$v" || exit 1

    # Restart cipi-queue (uses /usr/bin/php which is now the new version)
    if systemctl is-active --quiet cipi-queue 2>/dev/null; then
        step "Restarting API queue worker..."
        systemctl restart cipi-queue
        success "Queue worker restarted"
    fi

    log_action "PHP SWITCH: ${cur:-?} → $v"

    cipi_notify \
        "Cipi system PHP switched on $(hostname)" \
        "The system default PHP was changed.\n\nServer: $(hostname)\nFrom: ${cur:-unknown}\nTo: ${v}\nTime: $(date '+%Y-%m-%d %H:%M:%S %Z')" \
        php_switch

    echo ""
    echo -e "  ${GREEN}${BOLD}System PHP switched to ${v}${NC}"
    [[ -n "$cur" ]] && echo -e "  ${DIM}Previous: ${cur}${NC}"
    echo -e "  ${DIM}App PHP versions are independent (per-app setting)${NC}"
    echo ""
}

_php_remove() {
    local v="${1:-}"; shift || true
    parse_args "$@"
    [[ -z "$v" ]] && { error "Usage: cipi php remove <ver> [--force]"; exit 1; }
    validate_php_version_known "$v" || { error "Invalid: $v"; exit 1; }
    local sys_ver; sys_ver=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || echo "")
    [[ "$v" == "$sys_ver" ]] && { error "PHP $v is the system default. Switch first: cipi php switch <other-ver>"; exit 1; }
    if [[ -f "${CIPI_CONFIG}/apps.json" ]]; then
        local using; using=$(vault_read apps.json | jq -r --arg v "$v" 'to_entries[]|select(.value.php==$v)|.key' 2>/dev/null)
        [[ -n "$using" ]] && { error "In use by: $using"; exit 1; }
    fi
    if [[ "${ARG_force:-}" != "true" ]] && [[ -t 0 ]]; then
        confirm "Remove PHP ${v}?" || return
    fi
    systemctl stop "php${v}-fpm" 2>/dev/null; systemctl disable "php${v}-fpm" 2>/dev/null
    apt-get purge -y "php${v}-*" &>/dev/null; apt-get autoremove -y &>/dev/null
    log_action "PHP REMOVED: $v"
    cipi_notify \
        "Cipi PHP removed: ${v} on $(hostname)" \
        "A PHP version was removed.\n\nServer: $(hostname)\nVersion: ${v}\nTime: $(date '+%Y-%m-%d %H:%M:%S %Z')" \
        php_remove
    success "PHP ${v} removed"
}

_php_list() {
    parse_args "$@"
    local sys_ver; sys_ver=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || echo "")

    if [[ "${ARG_json:-}" == "true" ]]; then
        local items="[]"
        for v in 7.4 8.0 8.1 8.2 8.3 8.4 8.5; do
            if php_is_installed "$v"; then
                local st="stopped"
                systemctl is-active --quiet "php${v}-fpm" 2>/dev/null && st="running"
                local n=0
                [[ -f "${CIPI_CONFIG}/apps.json" ]] && n=$(vault_read apps.json | jq --arg v "$v" '[to_entries[]|select(.value.php==$v)]|length' 2>/dev/null || echo 0)
                n=${n:-0}
                [[ "$n" =~ ^[0-9]+$ ]] || n=0
                local is_default=false
                [[ "$v" == "$sys_ver" ]] && is_default=true
                items=$(echo "$items" | jq -c --arg v "$v" --arg s "$st" --argjson n "$n" --argjson d "$is_default" \
                    '. + [{version:$v, status:$s, apps:$n, default:$d}]')
            fi
        done
        jq -n --argjson versions "$items" --arg default "$sys_ver" \
            --argjson installable '["8.3","8.4","8.5"]' \
            '{default: (if $default == "" then null else $default end), installable: $installable, versions: $versions}'
        return 0
    fi

    echo -e "\n${BOLD}PHP Versions${NC}"
    for v in 7.4 8.0 8.1 8.2 8.3 8.4 8.5; do
        if php_is_installed "$v"; then
            local st="${RED}stopped" c="${RED}"
            systemctl is-active --quiet "php${v}-fpm" 2>/dev/null && st="running" && c="${GREEN}"
            local n=0; [[ -f "${CIPI_CONFIG}/apps.json" ]] && n=$(vault_read apps.json | jq --arg v "$v" '[to_entries[]|select(.value.php==$v)]|length' 2>/dev/null||echo 0)
            local def=""; [[ "$v" == "$sys_ver" ]] && def=" ${CYAN}← system default${NC}"
            printf "  PHP %-6s ${c}● %-8s${NC}  %d apps%b\n" "$v" "$st" "$n" "$def"
        fi
    done; echo ""
}
