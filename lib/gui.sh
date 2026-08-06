#!/bin/bash
# lib/gui.sh — Cipi GUI panel provisioning (mirror lib/api.sh)
#
# Copy to cipi-sh/cipi/lib/gui.sh and wire in the main `cipi` router:
#   gui) require_root; source "${CIPI_LIB}/gui.sh"; gui_command "$@" ;;

# When sourced inside migrations/self-update, CIPI_GUI_* may already be readonly — only assign when unset.
if [[ -z "${CIPI_GUI_ROOT:-}" ]]; then
    readonly CIPI_GUI_ROOT="/opt/cipi/gui"
fi
if [[ -z "${CIPI_GUI_CONFIG:-}" ]]; then
    readonly CIPI_GUI_CONFIG="${CIPI_CONFIG}/gui.json"
fi
if [[ -z "${CIPI_GUI_REPO:-}" ]]; then
    readonly CIPI_GUI_REPO="https://github.com/cipi-sh/gui"
fi
if [[ -z "${CIPI_GUI_BRANCH:-}" ]]; then
    readonly CIPI_GUI_BRANCH="main"
fi

# Composer GitHub prep for cipi/gui. Mirrors _api_composer_prepare_github so
# self-update can source this file before common.sh is reloaded (old process
# still has pre-update helpers in memory).
_gui_composer_prepare_github() {
    if declare -f _cipi_composer_prepare_github >/dev/null 2>&1; then
        _cipi_composer_prepare_github "$@"
        return
    fi
    local dir="$1"
    [[ -n "$dir" && -d "$dir" ]] || return 0
    local ssh_dir="/root/.ssh"
    local kh="${ssh_dir}/known_hosts"
    mkdir -p "$ssh_dir"
    chmod 700 "$ssh_dir"
    if ! grep -qE '(^|[,[:space:]])github\.com[,[:space:]]' "$kh" 2>/dev/null; then
        if command -v timeout >/dev/null 2>&1; then
            timeout --foreground 10 ssh-keyscan -T 5 -H github.com >> "$kh" 2>/dev/null || true
        else
            ssh-keyscan -T 5 -H github.com >> "$kh" 2>/dev/null || true
        fi
        if ! grep -qE '(^|[,[:space:]])github\.com[,[:space:]]' "$kh" 2>/dev/null; then
            cat >> "$kh" <<'EOF'
github.com ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIOMqqnkVzrm0SdG6UOoqKLsabgH5C9okWi0dh2l9GKJl
github.com ecdsa-sha2-nistp256 AAAAE2VjZHNhLXNoYTItbmlzdHAyNTYAAAAIbmlzdHAyNTYAAABBBEmKSENjQEezOmxkZMy7opKgwFB9nkt5YRrYMjNuG5N87uRgg6CLrbo5wAdT/y6v0mKV0U2w0WZ2YB/++Tpockg=
github.com ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABgQCj7ndNxQowgcQnjshcLrqPEiiphnt+VTTvDP6mHBL9j1aNUkY4Ue1gvwnGLVlOhGeYrnZaMgRK6+PKCUXaDbC7qtbW8gIkhL7aGCsOr/C56SJMy/BCZfxd1nWzAOxSDPgVsmerOBYfNqltV9/hWCqBywINIR+5dIg6JTJ72pcEpEjcYgXkE2YEFXV1JHnsKgbLWNlhScqb2UmyRkQyytRLtL+38TGxkxCflmO+5Z8CSSNY7GidjMIZ7Q4zMjA2n1nGrlTDkzwDCsw+wqFPGQA179cnfGWOWRVruj16z6XyvxvjJwbz0wQZ75XK5tKSb7FNyeIEs4TT4jk+S4dhPeAUC5y+bDYirYgM4GC7uEnztnZyaVWQ7B381AK4Qdrwt51ZqExKbQpTUNn+EjqoTwvqNj4kqx5QUCI0ThS/YkOxJCXmPUWZbhjpCg56i+2aB6CmK2JGhn57K5mj0MNdBXA4/WnwH6XoPWJzK5Nyu2zB3nAZp+S5hpQs+p1vN1/wsjk=
EOF
        fi
    fi
    chmod 600 "$kh" 2>/dev/null || true
    export GIT_TERMINAL_PROMPT=0
    export COMPOSER_PROCESS_TIMEOUT="${COMPOSER_PROCESS_TIMEOUT:-300}"
    (cd "$dir" && composer config --json github-protocols '["https"]' 2>/dev/null) || true
    (cd "$dir" && composer config preferred-install dist 2>/dev/null) || true
}

# Composer VCS repository for cipi/gui (https://github.com/cipi-sh/gui).
_gui_composer_vcs_repo() {
    local dir="$1"
    [[ -d "$dir" ]] || return 0
    _gui_composer_prepare_github "$dir"
    (cd "$dir" && composer config repositories.cipi-gui \
        "{\"type\":\"vcs\",\"url\":\"${CIPI_GUI_REPO}\"}" 2>/dev/null) || true
}

_gui_require_package() {
    local dir="$1"
    [[ -d "$dir" ]] || return 1
    _gui_composer_vcs_repo "$dir"
    (cd "$dir" && composer config minimum-stability dev 2>/dev/null) || true
    (cd "$dir" && composer config prefer-stable true 2>/dev/null) || true
    (cd "$dir" && composer require "cipi/gui:dev-${CIPI_GUI_BRANCH}" --no-interaction 2>/dev/null) \
        || (cd "$dir" && composer require cipi/gui --no-interaction 2>/dev/null) \
        || return 1
    return 0
}

_gui_open_basedir() {
    echo "${CIPI_GUI_ROOT}/:/tmp/:/proc/:/var/tmp/"
}

# Shell cwd may point at /tmp/cipi-gui-build or /opt/cipi/gui while we rm -rf them — escape first.
_gui_cd_safe() {
    pwd >/dev/null 2>&1 || cd / 2>/dev/null || cd /root 2>/dev/null || true
}

_gui_new_build_dir() {
    _gui_cd_safe
    rm -rf /tmp/cipi-gui-build /var/tmp/cipi-gui-build.* 2>/dev/null || true
    local dir="/var/tmp/cipi-gui-build.$$.$RANDOM"
    while [[ -e "$dir" ]]; do
        dir="/var/tmp/cipi-gui-build.$$.$RANDOM"
    done
    echo "$dir"
}

_gui_clear_host_routes() {
    local base="$1"
    echo '<?php' > "${base}/routes/web.php"
}

_gui_ensure_log_stack_env() {
    local envf="${CIPI_GUI_ROOT}/.env"
    [[ ! -f "$envf" ]] && return
    grep -q '^LOG_CHANNEL=' "$envf" \
        && sed -i 's|^LOG_CHANNEL=.*|LOG_CHANNEL=stack|' "$envf" \
        || echo 'LOG_CHANNEL=stack' >> "$envf"
    grep -q '^LOG_STACK=' "$envf" \
        && sed -i 's|^LOG_STACK=.*|LOG_STACK=single,stderr|' "$envf" \
        || echo 'LOG_STACK=single,stderr' >> "$envf"
}

_gui_ensure_session_driver_env() {
    local envf="${CIPI_GUI_ROOT}/.env"
    [[ ! -f "$envf" ]] && return
    grep -q '^SESSION_DRIVER=' "$envf" \
        && sed -i 's|^SESSION_DRIVER=.*|SESSION_DRIVER=file|' "$envf" \
        || echo 'SESSION_DRIVER=file' >> "$envf"
}

_gui_apply_sqlite_pragmas() {
    local envf="${CIPI_GUI_ROOT}/.env" db=""
    [[ ! -f "$envf" ]] && return
    grep -q '^DB_CONNECTION=sqlite' "$envf" 2>/dev/null || return
    local raw
    raw=$(grep '^DB_DATABASE=' "$envf" 2>/dev/null | head -1 | cut -d= -f2- | tr -d '[:space:]"\r')
    [[ -z "$raw" || "$raw" == "null" ]] && raw="database/database.sqlite"
    [[ "$raw" =~ ^/ ]] && db="$raw" || db="${CIPI_GUI_ROOT}/${raw}"
    [[ -f "$db" ]] && command -v sqlite3 >/dev/null 2>&1 \
        && sqlite3 "$db" "PRAGMA journal_mode=WAL; PRAGMA synchronous=NORMAL;" >/dev/null 2>&1 || true
}

ensure_cipi_gui_permissions() {
    [[ ! -d "${CIPI_GUI_ROOT}" ]] && return
    mkdir -p "${CIPI_GUI_ROOT}/storage/logs" "${CIPI_GUI_ROOT}/database" "${CIPI_GUI_ROOT}/bootstrap/cache" 2>/dev/null || true
    chown -R www-data:www-data "${CIPI_GUI_ROOT}/storage" "${CIPI_GUI_ROOT}/database" "${CIPI_GUI_ROOT}/bootstrap/cache" 2>/dev/null || true
    chmod -R ug+rwx "${CIPI_GUI_ROOT}/storage" "${CIPI_GUI_ROOT}/bootstrap/cache" 2>/dev/null || true
    # self-update does chown -R root:root /opt/cipi — .env must stay www-data-readable
    # or PHP-FPM returns HTTP 500 (same class as ensure_cipi_api_permissions).
    if [[ -f "${CIPI_GUI_ROOT}/.env" ]]; then
        chown www-data:www-data "${CIPI_GUI_ROOT}/.env" 2>/dev/null || true
        chmod 640 "${CIPI_GUI_ROOT}/.env" 2>/dev/null || true
    fi
    [[ -f "${CIPI_GUI_ROOT}/database/database.sqlite" ]] \
        && chown www-data:www-data "${CIPI_GUI_ROOT}/database/database.sqlite" 2>/dev/null || true
}

# ── Admin credentials (first install + reset) ────────────────────

_gui_validate_admin_email() {
    local email="$1"
    [[ "$email" =~ ^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$ ]]
}

_gui_validate_admin_password() {
    local pass="$1"
    [[ ${#pass} -ge 12 ]] || { echo "Password must be at least 12 characters"; return 1; }
    [[ "$pass" =~ [A-Z] ]] || { echo "Password must include an uppercase letter"; return 1; }
    [[ "$pass" =~ [a-z] ]] || { echo "Password must include a lowercase letter"; return 1; }
    [[ "$pass" =~ [0-9] ]] || { echo "Password must include a number"; return 1; }
    [[ "$pass" =~ [^a-zA-Z0-9] ]] || { echo "Password must include a special character"; return 1; }
    if [[ "$pass" =~ (.)\1\1\1\1 ]]; then
        echo "Password must not contain more than 4 identical characters in a row"
        return 1
    fi
    return 0
}

_gui_read_password() {
    local prompt="$1" var="$2"
    local _pw=""
    echo -ne "${CYAN}${prompt}${NC}: "
    if [[ -r /dev/tty ]]; then
        read -rs _pw < /dev/tty
    else
        read -rs _pw
    fi
    echo
    printf -v "$var" '%s' "$_pw"
}

_gui_prompt_admin_password() {
    local var="$1"
    local _pass="" _pass2="" err=""

    echo -e "${DIM}  Min 12 chars · upper + lower + digit + special · max 4 identical in a row${NC}"
    while true; do
        _gui_read_password "Admin password" _pass
        _gui_read_password "Confirm password" _pass2
        [[ "$_pass" == "$_pass2" ]] || { error "Passwords do not match"; continue; }
        if err=$(_gui_validate_admin_password "$_pass"); then
            printf -v "$var" '%s' "$_pass"
            return 0
        fi
        error "$err"
    done
}

_gui_prompt_admin_credentials() {
    local email="" pass="" err=""

    echo ""
    info "Create the GUI admin account"
    echo ""
    while true; do
        read_input "Admin email" "" email
        if _gui_validate_admin_email "$email"; then break; fi
        error "Invalid email address"
    done
    _gui_prompt_admin_password pass
    _GUI_ADMIN_EMAIL="$email"
    _GUI_ADMIN_PASSWORD="$pass"
}

_gui_resolve_admin_password() {
    local var="$1"
    local pass="${2:-}" err

    if [[ -n "$pass" ]]; then
        if err=$(_gui_validate_admin_password "$pass"); then
            printf -v "$var" '%s' "$pass"
            return 0
        fi
        error "$err"
        return 1
    fi

    if [[ -t 0 || -r /dev/tty ]]; then
        local pass=""
        _gui_prompt_admin_password pass
        printf -v "$var" '%s' "$pass"
        return 0
    fi

    error "Password required (--password=) or run from an interactive terminal"
    return 1
}

# ── Laravel host + cipi/gui package ─────────────────────────────

_gui_ensure_laravel_app() {
    if [[ ! -f "${CIPI_GUI_ROOT}/artisan" ]]; then
        _gui_cd_safe
        step "Installing Laravel GUI app..."
        local build_dir
        build_dir=$(_gui_new_build_dir) || {
            error "Failed to create GUI build directory"
            exit 1
        }

        composer create-project laravel/laravel "$build_dir" --no-interaction --prefer-dist 2>/dev/null || {
            rm -rf "$build_dir" 2>/dev/null || true
            error "Failed to create Laravel app. Ensure composer is available."
            exit 1
        }

        step "Installing cipi/gui from GitHub..."
        _gui_require_package "$build_dir" || {
            rm -rf "$build_dir" 2>/dev/null || true
            error "Failed to install cipi/gui from ${CIPI_GUI_REPO}"
            exit 1
        }

        sed -i "s|^APP_ENV=.*|APP_ENV=production|" "${build_dir}/.env"
        sed -i "s|^APP_DEBUG=.*|APP_DEBUG=false|" "${build_dir}/.env"
        sed -i "s|^SESSION_DRIVER=.*|SESSION_DRIVER=file|" "${build_dir}/.env"

        _gui_clear_host_routes "$build_dir"

        (cd "$build_dir" && php artisan vendor:publish --tag=cipi-gui-config --force 2>/dev/null) || true
        (cd "$build_dir" && php artisan key:generate --force 2>/dev/null) || true
        (cd "$build_dir" && php artisan migrate --force 2>/dev/null) || true

        local seed_cmd=(php artisan cipi:seed-gui-user)
        [[ -n "${_GUI_ADMIN_EMAIL:-}" ]] && seed_cmd+=(--email="${_GUI_ADMIN_EMAIL}")
        [[ -n "${_GUI_ADMIN_PASSWORD:-}" ]] && seed_cmd+=(--password="${_GUI_ADMIN_PASSWORD}")
        (cd "$build_dir" && "${seed_cmd[@]}") || {
            rm -rf "$build_dir" 2>/dev/null || true
            error "Failed to create GUI admin user"
            exit 1
        }

        _gui_cd_safe
        rm -rf "${CIPI_GUI_ROOT}" 2>/dev/null
        mv "$build_dir" "${CIPI_GUI_ROOT}"
        chown -R www-data:www-data "${CIPI_GUI_ROOT}"
        success "Laravel GUI app + cipi/gui package"
    else
        step "Updating cipi/gui package..."
        _gui_update_package
    fi
}

_gui_repair_runtime() {
    [[ ! -f "${CIPI_GUI_ROOT}/artisan" ]] && {
        error "Laravel GUI app not found."
        return 1
    }
    step "Repairing GUI runtime (open_basedir + permissions)..."
    ensure_cipi_gui_permissions
    local gui_php
    gui_php=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || echo "8.5")
    _gui_create_fpm_pool "$gui_php"
    (cd "${CIPI_GUI_ROOT}" && sudo -u www-data php artisan optimize:clear 2>/dev/null) || true
    reload_php_fpm "$gui_php" 2>/dev/null || true
}

_gui_refresh_theme() {
    [[ ! -f "${CIPI_GUI_ROOT}/artisan" ]] && return 0
    if ! (cd "${CIPI_GUI_ROOT}" && sudo -u www-data php artisan list --raw 2>/dev/null \
        | grep -qx 'cipi:gui-refresh-theme'); then
        return 0
    fi
    step "Refreshing GUI theme..."
    (cd "${CIPI_GUI_ROOT}" && sudo -u www-data php artisan cipi:gui-refresh-theme) || return 1
    return 0
}

_gui_update_package() {
    local composer_rc=0
    step "Composer VCS repo → ${CIPI_GUI_REPO}"
    _gui_composer_vcs_repo "${CIPI_GUI_ROOT}"
    _gui_composer_prepare_github "${CIPI_GUI_ROOT}"
    step "Composer update cipi/gui (timeout ${CIPI_COMPOSER_TIMEOUT:-600}s)..."
    if ! (cd "${CIPI_GUI_ROOT}" && composer update cipi/gui --no-interaction --prefer-dist --no-ansi); then
        composer_rc=$?
        warn "Composer update cipi/gui failed (exit ${composer_rc})"
    fi
    chown -R www-data:www-data "${CIPI_GUI_ROOT}" 2>/dev/null || true
    ensure_cipi_gui_permissions
    if declare -f _cipi_run_timed >/dev/null 2>&1; then
        (cd "${CIPI_GUI_ROOT}" && _cipi_run_timed 120 sudo -u www-data php artisan vendor:publish --tag=cipi-gui-config --force) || true
        (cd "${CIPI_GUI_ROOT}" && _cipi_run_timed 180 sudo -u www-data php artisan migrate --force) || true
    else
        (cd "${CIPI_GUI_ROOT}" && sudo -u www-data php artisan vendor:publish --tag=cipi-gui-config --force 2>/dev/null) || true
        (cd "${CIPI_GUI_ROOT}" && sudo -u www-data php artisan migrate --force 2>/dev/null) || true
    fi
    _gui_refresh_theme || true
    if [[ "$composer_rc" -ne 0 ]]; then
        return "$composer_rc"
    fi
    success "cipi/gui package updated"
    return 0
}

# ── PHP-FPM + Nginx ───────────────────────────────────────────────

_gui_create_fpm_pool() {
    # Optional $1 = PHP version (default: current system CLI, fallback 8.5).
    # Twin writer in php.sh (_php_write_gui_fpm_pool) is used by `cipi php switch`.
    local v="${1:-}" pv basedir
    if [[ -z "$v" ]]; then
        v=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || echo "8.5")
    fi
    basedir=$(_gui_open_basedir)
    mkdir -p "/etc/php/${v}/fpm/pool.d" /run/php
    cat > "/etc/php/${v}/fpm/pool.d/cipi-gui.conf" <<EOF
[cipi-gui]
user = www-data
group = www-data
listen = /run/php/cipi-gui.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660
pm = dynamic
pm.max_children = 20
pm.start_servers = 4
pm.min_spare_servers = 2
pm.max_spare_servers = 8
pm.max_requests = 500
request_terminate_timeout = 300s
slowlog = /var/log/cipi-gui-fpm-slow.log
request_slowlog_timeout = 30s
catch_workers_output = yes
php_admin_value[error_log] = /var/log/cipi-gui-php-error.log
php_admin_value[open_basedir] = ${basedir}
EOF
    for pv in 7.4 8.0 8.1 8.2 8.3 8.4 8.5; do
        [[ "$pv" == "$v" ]] && continue
        rm -f "/etc/php/${pv}/fpm/pool.d/cipi-gui.conf" 2>/dev/null || true
    done
    touch /var/log/cipi-gui-fpm-slow.log /var/log/cipi-gui-php-error.log 2>/dev/null || true
    chown www-data:adm /var/log/cipi-gui-fpm-slow.log /var/log/cipi-gui-php-error.log 2>/dev/null || true
}

_gui_create_nginx_vhost() {
    local domain="$1"
    cat > /etc/nginx/sites-available/cipi-gui <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name ${domain};
    root ${CIPI_GUI_ROOT}/public;
    index index.php;
    client_max_body_size 64M;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \\.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/cipi-gui.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        fastcgi_param HTTP_X_FORWARDED_PROTO \$scheme;
        fastcgi_param HTTPS \$https if_not_empty;
        include fastcgi_params;
        fastcgi_read_timeout 300s;
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
    }

    location ~ /\\.(?!well-known).* {
        deny all;
    }
}
EOF
}

_gui_setup_cron() {
    cat > /etc/cron.d/cipi-gui <<EOF
# Cipi GUI — Laravel scheduler
* * * * * www-data /usr/bin/php ${CIPI_GUI_ROOT}/artisan schedule:run >> /dev/null 2>&1
EOF
    chmod 644 /etc/cron.d/cipi-gui
}

# ── Install detection + removal ─────────────────────────────────

_gui_is_installed() {
    local pv
    [[ -f "${CIPI_GUI_ROOT}/artisan" ]] && return 0
    [[ -f "${CIPI_GUI_CONFIG}" ]] && return 0
    [[ -f /etc/nginx/sites-available/cipi-gui ]] && return 0
    [[ -f /etc/cron.d/cipi-gui ]] && return 0
    for pv in 7.4 8.0 8.1 8.2 8.3 8.4 8.5; do
        [[ -f "/etc/php/${pv}/fpm/pool.d/cipi-gui.conf" ]] && return 0
    done
    return 1
}

# ── Commands ────────────────────────────────────────────────────

gui_setup() {
    local domain="${1:-}"
    [[ -z "$domain" ]] && { error "Usage: cipi gui <domain>"; exit 1; }
    validate_domain "$domain" || { error "Invalid domain '${domain}'"; exit 1; }

    _gui_cd_safe

    echo ""; info "Configuring Cipi GUI at ${domain}..."; echo ""

    mkdir -p "${CIPI_CONFIG}"
    echo "{\"domain\": \"${domain}\"}" | vault_write gui.json
    success "Config saved"

    if [[ ! -f "${CIPI_GUI_ROOT}/artisan" ]]; then
        if [[ ! -t 0 && ! -r /dev/tty ]]; then
            error "GUI first install requires an interactive terminal (admin email and password)"
            exit 1
        fi
        _gui_prompt_admin_credentials
    fi

    _gui_ensure_laravel_app
    ensure_cipi_gui_permissions

    if [[ -f "${CIPI_GUI_ROOT}/.env" ]]; then
        sed -i "s|^APP_URL=.*|APP_URL=https://${domain}|" "${CIPI_GUI_ROOT}/.env"
    fi
    _gui_ensure_log_stack_env
    _gui_ensure_session_driver_env
    _gui_apply_sqlite_pragmas

    step "PHP-FPM pool..."
    local gui_php
    gui_php=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || echo "8.5")
    _gui_create_fpm_pool "$gui_php"
    reload_php_fpm "$gui_php"
    success "PHP-FPM pool (cipi-gui on PHP ${gui_php})"

    step "Nginx vhost..."
    _gui_create_nginx_vhost "$domain"
    ln -sf /etc/nginx/sites-available/cipi-gui /etc/nginx/sites-enabled/cipi-gui
    reload_nginx
    success "Nginx → ${domain}"

    step "Scheduler cron..."
    _gui_setup_cron
    success "Cron (schedule:run)"

    log_action "GUI CONFIGURED: $domain"
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo -e " ${GREEN}${BOLD}Cipi GUI configured${NC}"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo -e " Domain:  ${CYAN}https://${domain}${NC}"
    echo -e " Login:   ${CYAN}https://${domain}/login${NC}"
    [[ -n "${_GUI_ADMIN_EMAIL:-}" ]] && echo -e " Admin:   ${CYAN}${_GUI_ADMIN_EMAIL}${NC}"
    echo ""
    echo -e " ${BOLD}Next:${NC}  cipi gui ssl"
    echo -e "         cipi gui status"
    echo -e " ${DIM}Use the admin password you set during setup.${NC}"
    echo -e " ${DIM}Requires cipi api on managed servers + API tokens.${NC}"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""

    unset _GUI_ADMIN_PASSWORD
}

gui_ssl() {
    [[ ! -f "${CIPI_GUI_CONFIG}" ]] && { error "GUI not configured. Run: cipi gui <domain>"; exit 1; }
    local domain
    domain=$(vault_read gui.json | jq -r '.domain')
    [[ -z "$domain" || "$domain" == "null" ]] && { error "Domain not found in config."; exit 1; }
    step "Installing SSL for ${domain}..."
    certbot --nginx -d "${domain}" --non-interactive --agree-tos --redirect 2>/dev/null \
        || certbot --nginx -d "${domain}"
    reload_nginx
    success "SSL installed"
}

gui_update() {
    [[ ! -f "${CIPI_GUI_ROOT}/artisan" ]] && { error "Laravel GUI app not found."; exit 1; }
    step "Updating cipi/gui (composer, migrate, theme)..."
    _gui_update_package
    ensure_cipi_gui_permissions
    _gui_ensure_log_stack_env
    _gui_ensure_session_driver_env
    _gui_apply_sqlite_pragmas
    local gui_php
    gui_php=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || echo "8.5")
    _gui_create_fpm_pool "$gui_php"
    _gui_setup_cron
    reload_php_fpm "$gui_php"
    success "GUI updated — hard-refresh the browser (Cmd+Shift+R)"
}

gui_refresh_theme() {
    [[ ! -f "${CIPI_GUI_ROOT}/artisan" ]] && { error "Laravel GUI app not found."; exit 1; }
    ensure_cipi_gui_permissions
    _gui_refresh_theme || {
        error "cipi:gui-refresh-theme not available — run: cipi gui update"
        exit 1
    }
    success "GUI theme refreshed — hard-refresh the browser (Cmd+Shift+R)"
}

gui_upgrade() {
    [[ ! -f "${CIPI_GUI_CONFIG}" ]] && { error "GUI not configured."; exit 1; }
    [[ ! -f "${CIPI_GUI_ROOT}/artisan" ]] && { error "Laravel GUI app not found."; exit 1; }

    _gui_cd_safe

    local backup_dir="/var/tmp/cipi-gui-backup-$(date +%s)"
    local build_dir
    mkdir -p "$backup_dir"
    [[ -f "${CIPI_GUI_ROOT}/.env" ]] && cp "${CIPI_GUI_ROOT}/.env" "${backup_dir}/.env"
    [[ -f "${CIPI_GUI_ROOT}/database/database.sqlite" ]] && cp "${CIPI_GUI_ROOT}/database/database.sqlite" "${backup_dir}/database.sqlite"

    build_dir=$(_gui_new_build_dir) || exit 1
    composer create-project laravel/laravel "$build_dir" --no-interaction --prefer-dist || {
        rm -rf "$build_dir" 2>/dev/null || true
        exit 1
    }

    step "Installing cipi/gui from GitHub..."
    _gui_require_package "$build_dir" || {
        rm -rf "$build_dir" >/dev/null || true
        error "Failed to install cipi/gui from ${CIPI_GUI_REPO}"
        exit 1
    }

    [[ -f "${backup_dir}/.env" ]] && cp "${backup_dir}/.env" "${build_dir}/.env"
    [[ -f "${backup_dir}/database.sqlite" ]] && cp "${backup_dir}/database.sqlite" "${build_dir}/database/database.sqlite"

    _gui_clear_host_routes "$build_dir"
    (cd "$build_dir" && php artisan vendor:publish --tag=cipi-gui-config --force)
    (cd "$build_dir" && php artisan migrate --force)

    _gui_cd_safe
    rm -rf "${CIPI_GUI_ROOT}.old" 2>/dev/null
    mv "${CIPI_GUI_ROOT}" "${CIPI_GUI_ROOT}.old" 2>/dev/null || true
    mv "$build_dir" "${CIPI_GUI_ROOT}"
    rm -rf "$backup_dir" 2>/dev/null || true
    chown -R www-data:www-data "${CIPI_GUI_ROOT}"
    ensure_cipi_gui_permissions
    local gui_php pv
    gui_php=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || echo "8.5")
    for pv in 7.4 8.0 8.1 8.2 8.3 8.4 8.5; do
        if [[ -f "/etc/php/${pv}/fpm/pool.d/cipi-gui.conf" ]]; then
            gui_php="$pv"
            break
        fi
    done
    reload_php_fpm "$gui_php"
    success "GUI upgraded (old: ${CIPI_GUI_ROOT}.old)"
}

gui_status() {
    [[ ! -f "${CIPI_GUI_CONFIG}" ]] && { error "GUI not configured."; exit 1; }
    local domain
    domain=$(vault_read gui.json | jq -r '.domain')
    echo ""
    echo -e "${BOLD}Cipi GUI Status${NC}"
    echo -e "  Domain:  ${CYAN}https://${domain}${NC}"
    echo -e "  Root:    ${CIPI_GUI_ROOT}"
    [[ -f "${CIPI_GUI_ROOT}/artisan" ]] \
        && echo -e "  Laravel: $(cd "${CIPI_GUI_ROOT}" && sudo -u www-data php artisan --version 2>/dev/null)" \
        || echo -e "  Laravel: ${RED}not installed${NC}"
    [[ -f "${CIPI_GUI_ROOT}/artisan" ]] \
        && echo -e "  Package: $(cd "${CIPI_GUI_ROOT}" && composer show cipi/gui 2>/dev/null | sed -n 's/^[[:space:]]*versions[[:space:]]*:[[:space:]]*//p' | head -1)"
    echo ""
}

gui_fix_permissions() {
    _gui_repair_runtime || exit 1
    success "GUI permissions and runtime repaired"
}

gui_remove() {
    parse_args "$@"

    if ! _gui_is_installed; then
        info "GUI is not installed — nothing to remove"
        return 0
    fi

    local domain=""
    if [[ -f "${CIPI_GUI_CONFIG}" ]]; then
        domain=$(vault_read gui.json 2>/dev/null | jq -r '.domain // empty' 2>/dev/null) || domain=""
        [[ "$domain" == "null" ]] && domain=""
    fi

    if [[ "${ARG_force:-}" != "true" ]]; then
        echo ""
        warn "Will remove the Cipi GUI panel and all related config"
        [[ -n "$domain" ]] && echo -e "  ${DIM}Domain: ${domain}${NC}"
        confirm "Remove GUI?" || { info "Cancelled"; return; }
    fi

    step "Nginx..."
    rm -f /etc/nginx/sites-enabled/cipi-gui /etc/nginx/sites-available/cipi-gui
    reload_nginx 2>/dev/null || true

    step "PHP-FPM..."
    local pv
    for pv in 7.4 8.0 8.1 8.2 8.3 8.4 8.5; do
        if [[ -f "/etc/php/${pv}/fpm/pool.d/cipi-gui.conf" ]]; then
            rm -f "/etc/php/${pv}/fpm/pool.d/cipi-gui.conf"
            reload_php_fpm "$pv" 2>/dev/null || true
        fi
    done
    rm -f /run/php/cipi-gui.sock 2>/dev/null || true

    step "Cron..."
    rm -f /etc/cron.d/cipi-gui

    if [[ -n "$domain" ]]; then
        step "SSL..."
        certbot delete --cert-name "$domain" --non-interactive 2>/dev/null || true
    fi

    step "Application..."
    rm -rf "${CIPI_GUI_ROOT}" "${CIPI_GUI_ROOT}.old" /tmp/cipi-gui-build /var/tmp/cipi-gui-build.* 2>/dev/null || true

    step "Config..."
    rm -f "${CIPI_GUI_CONFIG}" 2>/dev/null || true

    rm -f /var/log/cipi-gui-fpm-slow.log /var/log/cipi-gui-php-error.log 2>/dev/null || true

    log_action "GUI REMOVED${domain:+: $domain}"
    success "GUI removed"
    echo -e "  ${DIM}Reinstall with: cipi gui <domain>${NC}"
}

# Reset primary admin (email/name/password + clear 2FA). Works with any cipi/gui version.
_gui_reset_primary_admin() {
    local email="${1:-}" password="$2" name="${3:-}"
    local workdir="${CIPI_GUI_ROOT}/storage/app"
    local payload="${workdir}/cipi-reset-$$.json"
    local runner="${CIPI_LIB}/gui-reset-admin.php"
    local rc=0

    [[ -f "$runner" ]] || { error "Missing ${runner}"; return 1; }

    mkdir -p "$workdir"
    ensure_cipi_gui_permissions

    jq -n \
        --arg email "$email" \
        --arg password "$password" \
        --arg name "$name" \
        '{
            email: (if ($email | length) > 0 then $email else null end),
            password: $password,
            name: (if ($name | length) > 0 then $name else null end)
        }' > "$payload"
    chown www-data:www-data "$payload"
    chmod 600 "$payload"

    if ! (cd "${CIPI_GUI_ROOT}" && sudo -u www-data php "$runner" "$payload" "${CIPI_GUI_ROOT}"); then
        rc=1
    fi
    rm -f "$payload"
    return "$rc"
}

gui_reset_user() {
    [[ ! -f "${CIPI_GUI_ROOT}/artisan" ]] && {
        error "Laravel GUI app not found. Run: cipi gui <domain>"
        exit 1
    }

    unset ARG_email ARG_password ARG_name
    parse_args "$@"

    local email="${ARG_email:-}" password="" err
    if [[ -n "$email" ]] && ! _gui_validate_admin_email "$email"; then
        error "Invalid email address"
        exit 1
    fi
    _gui_resolve_admin_password password "${ARG_password:-}" || exit 1

    step "Resetting GUI admin user..."
    ensure_cipi_gui_permissions

    # Always use lib/gui-reset-admin.php — cipi/gui's artisan --reset double-hashes
    # passwords on Laravel 12+ (User model "hashed" cast) and may target the wrong email.
    if ! _gui_reset_primary_admin "$email" "$password" "${ARG_name:-}"; then
        error "Failed to reset GUI admin user"
        exit 1
    fi

    log_action "GUI ADMIN USER RESET"
    success "GUI admin user reset (2FA disabled — re-enable from Settings after login)"
    unset password
}

gui_command() {
    local sub="${1:-}"
    shift || true

    case "$sub" in
        "")
            error "Usage: cipi gui <domain>"
            echo "       cipi gui ssl | update | upgrade | status | fix-permissions | repair"
            echo "       cipi gui refresh-theme"
            echo "       cipi gui remove [--force] | uninstall [--force]"
            echo "       cipi gui reset-user [--email=] [--password=] [--name=]"
            exit 1
            ;;
        ssl) gui_ssl ;;
        update) gui_update ;;
        upgrade) gui_upgrade ;;
        status) gui_status ;;
        fix-permissions|repair) gui_fix_permissions ;;
        refresh-theme) gui_refresh_theme ;;
        remove|uninstall) gui_remove "$@" ;;
        reset-user|reset-password) gui_reset_user "$@" ;;
        *)
            validate_domain "$sub" && gui_setup "$sub" || exit 1
            ;;
    esac
}
