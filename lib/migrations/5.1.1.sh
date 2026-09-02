#!/bin/bash
#############################################
# Cipi Migration 5.1.1
#
#  1. Rebuild every PHP-FPM pool. In 5.1.0 `_create_fpm_pool` ended its
#     heredoc with `${overrides}EOF` on one line, which does not terminate a
#     heredoc: the pool file was truncated by the redirection and the run then
#     died on an unbound variable, leaving a ZERO-BYTE pool behind. Migration
#     5.1.0 called that function for every app — so an upgraded server can be
#     one php-fpm restart away from every site returning 502.
#  2. Regenerate the nginx vhosts that no longer match apps.json. The same
#     unterminated heredoc swallowed `_nginx_reverb_location_block`, so every
#     vhost rewrite in 5.1.0 (alias add/remove, www, basicauth, PHP change,
#     suspend) aborted with "command not found" *after* apps.json had been
#     updated. Rendering those vhosts again closes the drift; a vhost that
#     already agrees with apps.json is left untouched.
#############################################

# `set -e` is deliberate, but every individual step below is written to
# tolerate failure and carry on. A migration that aborts makes self-update
# refuse the whole release and leave the server pinned on the old version,
# retrying and failing again every night — so a step that cannot be applied
# must say so and move on, never take the update down with it.
set -euo pipefail

export CIPI_LIB="${CIPI_LIB:-/opt/cipi/lib}"
export CIPI_CONFIG="${CIPI_CONFIG:-/etc/cipi}"
export CIPI_LOG="${CIPI_LOG:-/var/log/cipi}"

RED=$'\033[0;31m'; GREEN=$'\033[0;32m'; YELLOW=$'\033[1;33m'
CYAN=$'\033[0;36m'; DIM=$'\033[2m'; NC=$'\033[0m'; BOLD=$'\033[1m'

echo "Migration 5.1.1 — repairing FPM pools and nginx vhosts..."

# shellcheck source=/dev/null
source "${CIPI_LIB}/common.sh"

apps=""
if [[ -f "${CIPI_CONFIG}/apps.json" ]]; then
    apps=$(vault_read apps.json 2>/dev/null | jq -r 'keys[]' 2>/dev/null || true)
fi

[[ -n "$apps" ]] || { echo "Migration 5.1.1 complete (no apps)"; exit 0; }

# shellcheck source=/dev/null
source "${CIPI_LIB}/app.sh"

# ── 1. FPM pools ─────────────────────────────────────────────
touched=""
repaired=0
while IFS= read -r app; do
    [[ -n "$app" ]] || continue
    id "$app" &>/dev/null || continue
    php_ver=$(app_get "$app" php 2>/dev/null || true)
    [[ -n "$php_ver" ]] || continue
    [[ -d "/etc/php/${php_ver}/fpm/pool.d" ]] || continue
    [[ "$(app_get "$app" octane 2>/dev/null || true)" == "frankenphp" ]] && continue

    pool="/etc/php/${php_ver}/fpm/pool.d/${app}.conf"
    was_broken=false
    [[ -s "$pool" ]] || was_broken=true
    if [[ -s "$pool" ]] && ! grep -q "^\[${app}\]" "$pool"; then
        was_broken=true
    fi

    if _create_fpm_pool "$app" "$php_ver" 2>/dev/null && [[ -s "$pool" ]]; then
        touched="${touched} ${php_ver}"
        if [[ "$was_broken" == true ]]; then
            repaired=$((repaired + 1))
            echo "  ${app}: FPM pool was EMPTY — rebuilt (php ${php_ver})"
        else
            echo "  ${app}: FPM pool rewritten (php ${php_ver})"
        fi
    else
        echo "  WARNING: ${app}: could not write the FPM pool — check /etc/php/${php_ver}/fpm/pool.d/"
    fi
done <<< "$apps"

for v in $(echo "$touched" | tr ' ' '\n' | sort -u); do
    [[ -n "$v" ]] || continue
    if command -v "php-fpm${v}" &>/dev/null && ! "php-fpm${v}" -t &>/dev/null; then
        echo "  WARNING: php ${v}: FPM configuration test failed — not restarting"
        continue
    fi
    # Restart whether or not the unit is currently up: an emptied pool can have
    # left php-fpm dead ("no pool defined"), which is exactly the case to fix.
    systemctl cat "php${v}-fpm" &>/dev/null || continue
    if systemctl restart "php${v}-fpm" 2>/dev/null; then
        echo "  php ${v}: FPM restarted"
    else
        echo "  WARNING: php ${v}: FPM restart failed — run: systemctl status php${v}-fpm"
    fi
done
if [[ "$repaired" -gt 0 ]]; then
    echo "  ${repaired} pool(s) had been emptied by 5.1.0 and are now back"
fi

# ── 2. nginx vhosts that drifted from apps.json ──────────────
# Only the vhosts that no longer match what apps.json says are rewritten: a
# healthy vhost is left alone, because regenerating it drops certbot's :443
# block until `certbot install` puts it back.
_vhost_drifted() {
    local app="$1" vhost="$2" names dom
    names=$(grep -h 'server_name' "$vhost" 2>/dev/null | sed 's/.*server_name//; s/;.*//' | tr ' ' '\n' | grep -v '^$' || true)

    dom=$(app_get "$app" domain 2>/dev/null || true)
    # A www redirect moves a host into its own server block, but every name is
    # still somewhere in the file — so a name that is missing is real drift.
    if [[ -n "$dom" ]] && ! grep -qxF "$dom" <<< "$names"; then
        return 0
    fi
    local a
    while IFS= read -r a; do
        [[ -n "$a" ]] || continue
        grep -qxF "$a" <<< "$names" || return 0
    done < <(vault_read apps.json 2>/dev/null | jq -r --arg a "$app" '(.[$a].aliases // [])[]' 2>/dev/null || true)

    local want_auth has_auth
    want_auth=$(app_get "$app" basic_auth 2>/dev/null || true)
    has_auth=false; grep -q 'auth_basic ' "$vhost" 2>/dev/null && has_auth=true
    [[ "$want_auth" == "true" && "$has_auth" == false ]] && return 0
    [[ "$want_auth" != "true" && "$has_auth" == true ]] && return 0

    local want_susp has_susp
    want_susp=$(app_get "$app" suspended 2>/dev/null || true)
    has_susp=false; grep -q 'return 503' "$vhost" 2>/dev/null && has_susp=true
    [[ "$want_susp" == "true" && "$has_susp" == false ]] && return 0
    [[ "$want_susp" != "true" && "$has_susp" == true ]] && return 0

    return 1
}

if command -v nginx &>/dev/null && [[ -d /etc/nginx/sites-available ]]; then
    backup_dir="/var/lib/cipi/vhost-backup-5.1.1"
    rewrote=""
    while IFS= read -r app; do
        [[ -n "$app" ]] || continue
        vhost="/etc/nginx/sites-available/${app}"
        [[ -f "$vhost" ]] || continue
        dom=$(app_get "$app" domain 2>/dev/null || true)
        php_ver=$(app_get "$app" php 2>/dev/null || true)
        [[ -n "$dom" && -n "$php_ver" ]] || continue
        _vhost_drifted "$app" "$vhost" || continue
        # Regenerating drops certbot's :443 block until `certbot install` puts
        # it back — without certbot that would silently take the app off HTTPS.
        if grep -q 'listen 443' "$vhost" 2>/dev/null && ! command -v certbot &>/dev/null; then
            echo "  WARNING: ${app}: vhost does not match apps.json, but certbot is missing — left unchanged"
            continue
        fi
        mkdir -p "$backup_dir" 2>/dev/null || true
        cp "$vhost" "${backup_dir}/${app}" 2>/dev/null || continue
        if _create_nginx_vhost "$app" "$dom" "$php_ver" 2>/dev/null; then
            rewrote="${rewrote} ${app}"
            echo "  ${app}: vhost did not match apps.json — regenerated"
        else
            cp "${backup_dir}/${app}" "$vhost" 2>/dev/null || true
            echo "  WARNING: ${app}: vhost could not be regenerated — previous file kept"
        fi
    done <<< "$apps"

    if [[ -n "$rewrote" ]]; then
        if nginx -t &>/dev/null; then
            # certbot's :443 block lived in the same file and was just
            # overwritten — put the certificate back before reloading.
            for app in $rewrote; do
                _nginx_reapply_ssl "$app" >/dev/null 2>&1 || true
            done
            if nginx -t &>/dev/null; then
                systemctl reload nginx 2>/dev/null || true
                echo "  nginx: reloaded (previous vhosts kept in ${backup_dir})"
            else
                echo "  WARNING: nginx test failed after reapplying SSL — check: nginx -t"
            fi
        else
            for app in $rewrote; do
                cp "${backup_dir}/${app}" "/etc/nginx/sites-available/${app}" 2>/dev/null || true
            done
            echo "  WARNING: nginx test failed after regeneration — every vhost was restored from ${backup_dir}"
        fi
    fi
fi

echo "Migration 5.1.1 complete"
