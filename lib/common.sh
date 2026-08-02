#!/bin/bash
#############################################
# Cipi — Common Functions
#############################################

# When sourced outside the main cipi binary (e.g. migrations), CIPI_* may be unset.
# The main cipi script sets them readonly — only assign when unset (never touch readonly).
if [[ -z "${CIPI_LIB:-}" ]]; then
    CIPI_LIB="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
fi
if [[ -z "${CIPI_CONFIG:-}" ]]; then
    CIPI_CONFIG="/etc/cipi"
fi
if [[ -z "${CIPI_LOG:-}" ]]; then
    CIPI_LOG="/var/log/cipi"
fi

source "${CIPI_LIB}/vault.sh"

info()    { echo -e "${GREEN}[INFO]${NC} $*"; }
warn()    { echo -e "${YELLOW}[WARN]${NC} $*"; }
error()   { echo -e "${RED}[ERROR]${NC} $*" >&2; }
success() { echo -e "${GREEN}✓${NC} $*"; }
step()    { echo -e "${CYAN}→${NC} $*"; }

log_action() {
    mkdir -p "${CIPI_LOG}"
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*" >> "${CIPI_LOG}/cipi.log"
}

log_event() {
    mkdir -p "${CIPI_LOG}"
    local ip; ip=$(_get_client_ip 2>/dev/null || echo "local")
    local key; key=$(_get_session_key_name 2>/dev/null || echo "n/a")
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] [${ip}] [key:${key}] $*" >> "${CIPI_LOG}/events.log"
}

_get_client_ip() {
    if [[ -n "${SSH_CLIENT:-}" ]]; then
        echo "${SSH_CLIENT%% *}"
    elif [[ -n "${SSH_CONNECTION:-}" ]]; then
        echo "${SSH_CONNECTION%% *}"
    else
        echo "local"
    fi
}

_get_session_key_name() {
    local fp=""

    # Method 1: SSH_USER_AUTH (requires ExposeAuthInfo=yes + env_keep in sudoers)
    # Format: publickey <key_type> <raw_key_data> — field 3 is raw key, not fingerprint
    local auth_file="${SSH_USER_AUTH:-}"
    if [[ -n "$auth_file" && -f "$auth_file" ]]; then
        local key_type key_data
        key_type=$(awk '/^publickey / {print $2; exit}' "$auth_file" 2>/dev/null)
        key_data=$(awk '/^publickey / {print $3; exit}' "$auth_file" 2>/dev/null)
        if [[ -n "$key_type" && -n "$key_data" ]]; then
            fp=$(echo "$key_type $key_data" | ssh-keygen -lf - 2>/dev/null | awk '{print $2}')
        fi
    fi

    # Method 2: auth.log fallback
    if [[ -z "$fp" && -f /var/log/auth.log ]]; then
        local client_ip; client_ip=$(_get_client_ip)
        local login_user="${SUDO_USER:-cipi}"
        local log_line
        log_line=$(grep "Accepted publickey for ${login_user} from ${client_ip}" /var/log/auth.log 2>/dev/null | tail -1)
        [[ -n "$log_line" ]] && fp=$(echo "$log_line" | grep -o 'SHA256:[^ ]*')
    fi

    [[ -z "$fp" ]] && { echo "n/a"; return; }

    local ak="/home/cipi/.ssh/authorized_keys"
    [[ -f "$ak" ]] || { echo "$fp"; return; }

    while IFS= read -r line; do
        [[ -z "$line" || "$line" == \#* ]] && continue
        local line_fp
        line_fp=$(echo "$line" | ssh-keygen -lf - 2>/dev/null | awk '{print $2}')
        if [[ "$line_fp" == "$fp" ]]; then
            local comment
            comment=$(echo "$line" | awk '{$1=$2=""; print}' | xargs)
            [[ -n "$comment" ]] && { echo "$comment"; return; }
        fi
    done < "$ak"
    echo "$fp"
}

generate_password() { openssl rand -base64 64 | tr -dc 'a-zA-Z0-9' | head -c "${1:-40}"; }
generate_token()    { openssl rand -hex 32; }
generate_app_key()  { echo "base64:$(openssl rand -base64 32)"; }

validate_username() {
    local n="$1"
    [[ ! "$n" =~ ^[a-z][a-z0-9]{2,31}$ ]] && return 1
    local bad=("root" "admin" "www" "nginx" "mysql" "mariadb" "postgres" "pgsql" "redis" "valkey" "git" "deploy" "cipi" "ubuntu" "debian" "supervisor" "nobody" "postfix" "sshd" "clamav" "daemon" "bin" "sys")
    for b in "${bad[@]}"; do [[ "$n" == "$b" ]] && return 1; done
    return 0
}

validate_domain() {
    [[ "$1" =~ ^[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*\.[a-zA-Z]{2,}$ ]]
}

# Git branch name. Restricted to a safe charset so it can never break out of
# the single-quoted PHP string literal it's substituted into when generating
# deploy.php (lib/app.sh _create_deployer_config_from_template / app_edit).
validate_git_branch() {
    local b="$1"
    [[ "$b" =~ ^[A-Za-z0-9][A-Za-z0-9._/-]{0,190}$ ]] || return 1
    [[ "$b" == *".."* ]] && return 1
    return 0
}

# Git remote — SSH shorthand (git@host:path), ssh://, https://, or http://.
# Same rationale as validate_git_branch: restricted charset so it can never
# break out of the PHP string literal (or a downstream Deployer run() call).
validate_git_repository() {
    [[ "$1" =~ ^[A-Za-z0-9][A-Za-z0-9._@:/-]{0,254}$ ]]
}

# Database/user identifier used in raw SQL text (mariadb -e "..."). Must never
# contain quotes, backticks, or shell/SQL metacharacters.
validate_db_name() {
    [[ "$1" =~ ^[a-z][a-z0-9_]{1,63}$ ]]
}

# Installable / usable PHP versions. Restricted to 8.3+ because the bundled
# Deployer (v8) requires PHP >= 8.3 and `dep` is invoked with the app's PHP
# version — running it under an older interpreter would break every deploy.
validate_php_version() {
    case "$1" in 8.3|8.4|8.5) return 0 ;; *) return 1 ;; esac
}

# Any PHP version Cipi has ever managed. Used where we must still operate on a
# legacy install (e.g. `cipi php remove 8.0` to clean up a pre-4.5.4 server).
validate_php_version_known() {
    case "$1" in 7.4|8.0|8.1|8.2|8.3|8.4|8.5) return 0 ;; *) return 1 ;; esac
}

php_is_installed() { dpkg -l "php${1}-fpm" &>/dev/null 2>&1; }

# Major version of the installed Deployer binary (e.g. "8"). Empty if `dep` is
# missing/unreadable. Used to gate the PHP >= 8.3 requirement: only Deployer 8+
# rejects older PHP — under Deployer 7 those apps still deploy fine.
deployer_major_version() {
    local v
    v=$(/usr/local/bin/dep --version 2>/dev/null | grep -oE '[0-9]+(\.[0-9]+)+' | head -n1)
    [[ -z "$v" ]] && return 1
    echo "${v%%.*}"
}

app_exists() {
    [[ -f "${CIPI_CONFIG}/apps.json" ]] && vault_read apps.json | jq -e --arg a "$1" '.[$a]' &>/dev/null
}

# Check if domain is used by another app (domain or alias). Exclude app name when editing.
# Returns 0 if in use, 1 if free. If in use, sets DOMAIN_USED_BY_APP for error message.
domain_is_used_by_other_app() {
    local dom="$1" exclude_app="${2:-}"
    DOMAIN_USED_BY_APP=""
    [[ ! -f "${CIPI_CONFIG}/apps.json" ]] && return 1
    DOMAIN_USED_BY_APP=$(vault_read apps.json | jq -r --arg d "$dom" --arg e "$exclude_app" '
        to_entries[] | select(.key != $e) |
        select(.value.domain == $d or ((.value.aliases // []) | index($d) != null)) |
        .key
    ' 2>/dev/null | head -1)
    [[ -n "$DOMAIN_USED_BY_APP" ]]
}

app_get() { vault_read apps.json | jq -r --arg a "$1" --arg k "$2" '.[$a][$k] // empty'; }

# Generate apps-public.json: a plaintext projection of apps.json containing
# only non-sensitive fields (domain, aliases, php, branch, repository, user,
# created_at, suspended, basic_auth, www_redirect, force_https, custom, docroot, engine).
# The encrypted apps.json keeps webhook tokens and git IDs safe.
_update_apps_public() {
    [[ -f "${CIPI_CONFIG}/apps.json" ]] || return 0
    _cipi_config_writable || return 0
    local json
    json=$(vault_read apps.json) || return 0
    echo "$json" | jq '
        with_entries(.value |= {domain, aliases, php, branch, repository, user, created_at, suspended, basic_auth, www_redirect, force_https, custom, docroot, engine})
    ' > "${CIPI_CONFIG}/apps-public.json" 2>/dev/null || return 0
    _cipi_safe_chmod 640 "${CIPI_CONFIG}/apps-public.json"
    chgrp cipi-api "${CIPI_CONFIG}/apps-public.json" 2>/dev/null || true
}

# When Cipi API is configured, ensure www-data can read apps-public.json via
# the cipi-api group. The encrypted apps.json stays root-only (600).
ensure_apps_json_api_access() {
    [[ -f "${CIPI_CONFIG}/api.json" ]] || return 0
    [[ -f "${CIPI_CONFIG}/apps.json" ]] || return 0
    _cipi_config_writable || return 0
    if ! getent group cipi-api &>/dev/null; then
        groupadd cipi-api 2>/dev/null || true
    fi
    if ! id -nG www-data 2>/dev/null | grep -qw cipi-api; then
        usermod -aG cipi-api www-data 2>/dev/null || true
    fi
    chgrp cipi-api "${CIPI_CONFIG}" 2>/dev/null || true
    _cipi_safe_chmod 750 "${CIPI_CONFIG}"
    _update_apps_public
}

# Panel API (/opt/cipi/api): SQLite, Laravel logs, and bootstrap cache must be
# writable by www-data (PHP-FPM, queue worker, artisan via sudo -u www-data).
# Call after root-run composer or manual edits under /opt/cipi/api.
ensure_cipi_api_permissions() {
    local root="${CIPI_API_ROOT:-/opt/cipi/api}"
    [[ -f "${root}/artisan" ]] || return 0
    mkdir -p "${root}/storage/logs" "${root}/database" "${root}/bootstrap/cache" 2>/dev/null || true
    chown -R www-data:www-data "${root}/storage" "${root}/database" "${root}/bootstrap/cache" 2>/dev/null || true
    # .env must stay www-data-readable (queue worker, artisan, PHP-FPM); root-only breaks or confuses tooling.
    if [[ -f "${root}/.env" ]]; then
        chown www-data:www-data "${root}/.env" 2>/dev/null || true
        chmod 640 "${root}/.env" 2>/dev/null || true
    fi
}

app_set() {
    vault_read apps.json | jq --arg a "$1" --arg k "$2" --arg v "$3" '.[$a][$k] = $v' | vault_write apps.json
    ensure_apps_json_api_access
}

app_save() {
    local json
    json=$(vault_read apps.json) || json="{}"
    local result
    if ! result=$(echo "$json" | jq --arg a "$1" --argjson d "$2" '.[$a] = $d' 2>/dev/null); then
        error "Failed to save app config (invalid JSON?)"; return 1
    fi
    echo "$result" | vault_write apps.json
    ensure_apps_json_api_access
}

app_remove() {
    vault_read apps.json | jq --arg a "$1" 'del(.[$a])' | vault_write apps.json
    ensure_apps_json_api_access
}

# Remove Linux user, primary group, and home (including ~/.ssh deploy keys).
# userdel -r often fails when processes still hold the UID (cron, leftover
# php-fpm, open SSH); without a fallback the home/keys become orphans.
remove_app_linux_user() {
    local app="${1:-}"
    [[ -z "$app" || "$app" == "cipi" ]] && return 0
    validate_username "$app" || return 1

    gpasswd -d www-data "$app" 2>/dev/null || true
    crontab -u "$app" -r 2>/dev/null || true

    if id "$app" &>/dev/null; then
        pkill -KILL -u "$app" 2>/dev/null || true
        local i=0
        while (( i < 10 )) && pgrep -u "$app" &>/dev/null; do
            sleep 0.2
            ((i++)) || true
        done
        if ! userdel -r "$app" 2>/dev/null; then
            userdel "$app" 2>/dev/null || true
        fi
    fi
    groupdel "$app" 2>/dev/null || true
    # Absolute cleanup — home + SSH keys must not linger after a partial userdel
    [[ -e "/home/${app}" ]] && rm -rf "/home/${app}"
    return 0
}

# Drop Linux users / homes that look like Cipi apps but are no longer in apps.json
# (incomplete delete, failed create, or old userdel -r failure). Requires a Cipi
# footprint so unrelated local accounts are never touched.
purge_orphan_app_users() {
    local registered="" u home footprint purged=0
    [[ -f "${CIPI_CONFIG}/apps.json" ]] || return 0
    registered=$(vault_read apps.json 2>/dev/null | jq -r 'keys[]' 2>/dev/null || true)

    for home in /home/*/; do
        [[ -d "$home" ]] || continue
        u=$(basename "$home")
        validate_username "$u" || continue
        [[ "$u" == "cipi" ]] && continue
        if printf '%s\n' "$registered" | grep -Fxq "$u"; then
            continue
        fi

        footprint=false
        if id "$u" &>/dev/null && id -nG "$u" 2>/dev/null | grep -qw cipi-apps; then
            footprint=true
        fi
        [[ -d "${home}.deployer" ]] && footprint=true
        [[ -f "${home}.ssh/id_ed25519" ]] && footprint=true
        [[ -f "/etc/nginx/sites-available/${u}" ]] && footprint=true
        [[ "$footprint" == "true" ]] || continue

        info "Purging orphan app user '${u}'..."
        rm -f "/etc/nginx/sites-enabled/${u}" "/etc/nginx/sites-available/${u}"
        rm -f /etc/php/*/fpm/pool.d/"${u}.conf"
        rm -f "/etc/supervisor/conf.d/${u}.conf"
        rm -f "/etc/sudoers.d/cipi-${u}"
        rm -f "/etc/nginx/cipi-basicauth/${u}.htpasswd"
        supervisorctl stop "${u}-worker-"* 2>/dev/null || true
        remove_app_linux_user "$u"
        ((purged++)) || true
    done

    if (( purged > 0 )); then
        nginx -t &>/dev/null && systemctl reload nginx 2>/dev/null || true
        supervisorctl reread &>/dev/null || true
        supervisorctl update &>/dev/null || true
    fi
    return 0
}

get_db_root_password() { vault_read server.json | jq -r '.db_root_password'; }

confirm() {
    echo -e -n "${YELLOW}${1:-Are you sure?} [y/N]: ${NC}"; read -r r; [[ "$r" =~ ^[Yy]$ ]]
}

read_input() {
    local prompt="$1" default="${2:-}" var="$3"
    if [[ -n "$default" ]]; then echo -e -n "${CYAN}${prompt}${NC} [${default}]: "
    else echo -e -n "${CYAN}${prompt}${NC}: "; fi
    read -r input; printf -v "$var" '%s' "${input:-$default}"
}

parse_args() {
    for arg in "$@"; do
        case "$arg" in
            --*=*) local k="${arg%%=*}"; k="${k#--}"; printf -v "ARG_${k//-/_}" '%s' "${arg#*=}" ;;
            --*)   local k="${arg#--}"; printf -v "ARG_${k//-/_}" '%s' "true" ;;
        esac
    done
}

reload_nginx() {
    if ! nginx -t 2>&1; then
        error "nginx config test failed. Fix config and run: nginx -t"
        return 1
    fi
    systemctl reload nginx || { error "systemctl reload nginx failed"; return 1; }
}

reload_php_fpm() {
    systemctl restart "php${1}-fpm" || { error "Failed to restart php${1}-fpm"; return 1; }
}

reload_supervisor() {
    supervisorctl reread 2>&1 || { error "supervisorctl reread failed"; return 1; }
    supervisorctl update 2>&1 || warn "Supervisor update had issues (worker may start after first deploy)"
}

# App home is 750 (app:app): user cipi cannot traverse /home/<app>/ without ACLs.
# Nginx writes vhost logs as www-data; directory uses setgid www-data so new files stay group-readable.
#
# Do NOT apply setfacl -R or default ACLs on shared/storage/logs — Deployer chmod on Laravel log
# files fails with "Operation not permitted" when per-file ACLs exist. Strip any file ACLs left
# from older Cipi versions, then re-apply directory ACLs only.
ensure_app_logs_permissions() {
    local app="${1:-}"
    [[ -z "$app" || "$app" == "cipi" ]] && return 0
    local home="/home/${app}"
    [[ -d "$home" ]] || return 0
    id "$app" &>/dev/null || return 0

    if command -v setfacl &>/dev/null; then
        if [[ -d "${home}/logs" ]]; then
            find "${home}/logs" -type f -exec setfacl -b {} \; 2>/dev/null || true
            setfacl -k "${home}/logs" 2>/dev/null || true
        fi
        if [[ -d "${home}/shared/storage/logs" ]]; then
            find "${home}/shared/storage/logs" -type f -name '*.log' -exec setfacl -b {} \; 2>/dev/null || true
            setfacl -k "${home}/shared/storage/logs" 2>/dev/null || true
        fi
    fi

    mkdir -p "${home}/logs"
    chown "${app}:www-data" "${home}/logs"
    chmod 2775 "${home}/logs"

    # Reclaim root-owned log files left by old logrotate "create" rules
    if [[ -d "${home}/logs" ]]; then
        find "${home}/logs" -type f -user root -exec chown "${app}:www-data" {} \; 2>/dev/null || true
    fi
    if [[ -d "${home}/shared/storage/logs" ]]; then
        find "${home}/shared/storage/logs" -type f -user root -exec chown "${app}:${app}" {} \; 2>/dev/null || true
        chmod 664 "${home}/shared/storage/logs"/*.log 2>/dev/null || true
    fi

    if command -v setfacl &>/dev/null && id cipi &>/dev/null; then
        setfacl -m u:cipi:rx "${home}" 2>/dev/null || true
        setfacl -m u:cipi:rx "${home}/logs" 2>/dev/null || true
        if [[ -d "${home}/shared" ]]; then
            setfacl -m u:cipi:rx "${home}/shared" 2>/dev/null || true
        fi
        if [[ -d "${home}/shared/storage" ]]; then
            setfacl -m u:cipi:rx "${home}/shared/storage" 2>/dev/null || true
        fi
        if [[ -d "${home}/shared/storage/logs" ]]; then
            setfacl -m u:cipi:rx "${home}/shared/storage/logs" 2>/dev/null || true
            find "${home}/shared/storage/logs" -type f -name '*.log' -exec setfacl -m u:cipi:r {} \; 2>/dev/null || true
        fi
    fi
}

_create_supervisor_worker() {
    local app="$1" v="$2" queue="${3:-default}" procs="${4:-1}" tries="${5:-3}" timeout="${6:-3600}"
    cat >> "/etc/supervisor/conf.d/${app}.conf" <<EOF
[program:${app}-worker-${queue}]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php${v} /home/${app}/current/artisan queue:work database --sleep=3 --tries=${tries} --max-time=${timeout} --queue=${queue}
autostart=true
autorestart=true
startretries=5
startsecs=3
stopasgroup=true
killasgroup=true
user=${app}
numprocs=${procs}
redirect_stderr=true
stdout_logfile=/home/${app}/logs/worker-${queue}.log
stdout_logfile_maxbytes=10MB
stopwaitsecs=${timeout}
EOF
}

# Init config on source — do not chmod /etc/cipi here: setup.sh sets permissions once.
# chmod on every `cipi` source breaks read-only / (db list, deploy status, …).
mkdir -p "${CIPI_CONFIG}" "${CIPI_LOG}" 2>/dev/null || true
vault_init
if _cipi_config_writable 2>/dev/null; then
    for f in apps.json databases.json; do
        if [[ ! -f "${CIPI_CONFIG}/$f" ]]; then
            echo "{}" | vault_write "$f" 2>/dev/null || true
        fi
    done
    ensure_apps_json_api_access
fi

# Email notifications (optional) — cipi_notify "Subject" "Body" [trigger_id]
[[ -f "${CIPI_LIB}/notifications.sh" ]] && source "${CIPI_LIB}/notifications.sh"
[[ -f "${CIPI_LIB}/smtp.sh" ]] && source "${CIPI_LIB}/smtp.sh"
