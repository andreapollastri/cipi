#!/bin/bash
#############################################
# Cipi Migration 5.1.0
#
#  1. Install the automatic-deploy wrapper and point every app's webhook cron
#     at it, so automatic deploys start producing a timestamped log.
#  2. Mirror the Cipi php.ini defaults into the CLI SAPI — until now only FPM
#     was configured, so queue workers, artisan and cron ran on the package
#     defaults and `cipi ini set` could not have reached them.
#  3. Rewrite FPM pools so they stop hardcoding upload_max_filesize /
#     post_max_size / max_execution_time, which shadowed the server-wide file
#     and made a global change look like it did nothing.
#  4. Convert the hardcoded nightly backup cron into a real backup profile and
#     take over the schedule with a managed crontab block.
#  5. Claim the :443 default server when nothing else does.
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

echo "Migration 5.1.0 — deploy logging, php.ini layering, backup profiles..."

# shellcheck source=/dev/null
source "${CIPI_LIB}/common.sh"

# ── 1. Automatic-deploy wrapper ──────────────────────────────
if [[ -f "${CIPI_LIB}/cipi-app-deploy.sh" ]]; then
    if cp "${CIPI_LIB}/cipi-app-deploy.sh" /usr/local/bin/cipi-app-deploy 2>/dev/null; then
        chmod 755 /usr/local/bin/cipi-app-deploy 2>/dev/null || true
        chown root:root /usr/local/bin/cipi-app-deploy 2>/dev/null || true
        echo "  installed /usr/local/bin/cipi-app-deploy"
    else
        echo "  WARNING: could not write /usr/local/bin/cipi-app-deploy — webhook deploys keep the old logging"
    fi
fi

apps=""
if [[ -f "${CIPI_CONFIG}/apps.json" ]]; then
    apps=$(vault_read apps.json 2>/dev/null | jq -r 'keys[]' 2>/dev/null || true)
fi

if [[ -x /usr/local/bin/cipi-app-deploy ]]; then
    while IFS= read -r app; do
        [[ -n "$app" ]] || continue
        id "$app" &>/dev/null || continue
        php_ver=$(app_get "$app" php 2>/dev/null || true)
        [[ -n "$php_ver" ]] || continue
        cur=$(crontab -u "$app" -l 2>/dev/null || true)
        [[ -n "$cur" ]] || continue
        grep -q '\.deploy-trigger' <<< "$cur" || continue
        grep -q 'cipi-app-deploy' <<< "$cur" && continue
        new=$(printf '%s\n' "$cur" | awk -v app="$app" -v v="$php_ver" '
            /\.deploy-trigger/ {
                printf "* * * * * test -f /home/%s/.deploy-trigger && rm -f /home/%s/.deploy-trigger && /usr/local/bin/cipi-app-deploy %s %s webhook >/dev/null 2>&1\n", app, app, app, v
                next
            }
            { print }
        ')
        if printf '%s\n' "$new" | crontab -u "$app" - 2>/dev/null; then
            echo "  ${app}: webhook deploy now writes a timestamped log"
        else
            echo "  WARNING: ${app}: could not rewrite the crontab — left unchanged"
        fi
    done <<< "$apps"
fi

# ── 1b. Post-deploy healthcheck sudoers rule ─────────────────
# The webhook deploy runs as the app user and must be able to ask Cipi to
# verify the release it just published. One exact command, that app only.
while IFS= read -r app; do
    [[ -n "$app" ]] || continue
    id "$app" &>/dev/null || continue
    sudoers="/etc/sudoers.d/cipi-${app}"
    [[ -f "$sudoers" ]] || continue
    grep -q "cipi health postdeploy ${app} --auto\$" "$sudoers" && continue
    cp "$sudoers" "${sudoers}.cipi-bak" 2>/dev/null || continue
    echo "${app} ALL=(root) NOPASSWD: /usr/local/bin/cipi health postdeploy ${app} --auto" >> "$sudoers"
    chmod 440 "$sudoers"
    if visudo -cf "$sudoers" &>/dev/null; then
        rm -f "${sudoers}.cipi-bak"
        echo "  ${app}: post-deploy healthcheck allowed from the webhook path"
    else
        mv "${sudoers}.cipi-bak" "$sudoers"
        chmod 440 "$sudoers"
        echo "  ${app}: sudoers rule rejected — left unchanged"
    fi
done <<< "$apps"

# ── 2. php.ini for the CLI SAPI ──────────────────────────────
for verdir in /etc/php/*/; do
    v=$(basename "$verdir")
    [[ -d "/etc/php/${v}/fpm/conf.d" ]] || continue
    [[ -d "/etc/php/${v}/cli/conf.d" ]] || continue
    src="/etc/php/${v}/fpm/conf.d/99-cipi.ini"
    dst="/etc/php/${v}/cli/conf.d/99-cipi.ini"
    if [[ -f "$src" && ! -f "$dst" ]]; then
        if cp "$src" "$dst" 2>/dev/null; then
            chmod 644 "$dst" 2>/dev/null || true
            echo "  PHP ${v}: CLI now uses the same Cipi settings as FPM"
        else
            echo "  WARNING: PHP ${v}: could not write ${dst}"
        fi
    fi
done

# ── 3. FPM pools inherit the server-wide file again ──────────
if [[ -n "$apps" ]]; then
    # shellcheck source=/dev/null
    source "${CIPI_LIB}/app.sh"
    touched=""
    while IFS= read -r app; do
        [[ -n "$app" ]] || continue
        php_ver=$(app_get "$app" php 2>/dev/null || true)
        [[ -n "$php_ver" ]] || continue
        pool="/etc/php/${php_ver}/fpm/pool.d/${app}.conf"
        [[ -f "$pool" ]] || continue
        [[ "$(app_get "$app" octane)" == "frankenphp" ]] && continue
        if _create_fpm_pool "$app" "$php_ver" 2>/dev/null; then
            touched="${touched} ${php_ver}"
            echo "  ${app}: FPM pool rewritten (inherits server-wide php.ini)"
        else
            echo "  WARNING: ${app}: could not rewrite the FPM pool — left unchanged"
        fi
    done <<< "$apps"
    for v in $(echo "$touched" | tr ' ' '\n' | sort -u); do
        [[ -n "$v" ]] || continue
        systemctl is-active --quiet "php${v}-fpm" 2>/dev/null || continue
        systemctl restart "php${v}-fpm" 2>/dev/null || true
    done
fi

# ── 4. Backup: legacy schedule → managed profile ─────────────
if [[ -f "${CIPI_CONFIG}/backup.json" ]]; then
    # shellcheck source=/dev/null
    source "${CIPI_LIB}/backup.sh"

    cfg=$(vault_read backup.json 2>/dev/null || echo '{}')
    if [[ "$(echo "$cfg" | jq '(.profiles // {}) | length')" -eq 0 ]]; then
        # Retention carried over from the old `backup prune --weeks=N` line so
        # nothing starts keeping more or less than it did yesterday.
        # `set -o pipefail` plus a grep that matches nothing makes this
        # assignment fail, which under `set -e` would abort the whole
        # migration on every server that has no legacy prune line.
        weeks=$(crontab -l 2>/dev/null | grep -oE 'cipi backup prune --weeks=[0-9]+' \
                | grep -oE '[0-9]+$' | head -1 || true)
        [[ "$weeks" =~ ^[0-9]+$ ]] || weeks=4
        [[ "$weeks" -gt 0 ]] || weeks=4
        dest='["s3"]'
        [[ -n "$(echo "$cfg" | jq -r '.bucket // ""')" ]] || dest='["local"]'
        prof=$(jq -n --argjson w "$weeks" --argjson d "$dest" '{
            scope: "all", apps: ["*"], databases: ["*"],
            exclude_databases: [], exclude_tables: [],
            cron: "0 2 * * *", interval_seconds: 86400,
            destinations: $d, retention: {keep: 0, days: 0, weeks: $w},
            encrypt: false, enabled: true
        }')
        if echo "$cfg" \
            | jq --argjson p "$prof" --arg l "/var/backups/cipi" \
                '.local_dir = (.local_dir // $l) | .profiles = {default: $p}' \
            | vault_write backup.json; then
            mkdir -p /var/backups/cipi 2>/dev/null && chmod 700 /var/backups/cipi 2>/dev/null || true
            echo "  backup: nightly job converted to profile 'default' (kept ${weeks} weeks)"
            echo "          it now covers ALL databases on the server, not just one per app,"
            echo "          and --custom apps (htdocs/) instead of failing on a missing shared/"
        else
            echo "  WARNING: could not write backup.json — the old schedule is left in place"
        fi
    fi
    if _bk_write_cron 2>/dev/null; then
        echo "  backup: schedule moved into a managed crontab block"
    else
        echo "  WARNING: could not rewrite the backup crontab block"
    fi
fi

# ── 5. HTTPS default server ──────────────────────────────────
if command -v nginx &>/dev/null && [[ -f "${CIPI_LIB}/nginx.sh" ]]; then
    # shellcheck source=/dev/null
    source "${CIPI_LIB}/nginx.sh"
    if ! nginx_default_server_enabled; then
        nrc=0
        nginx_enable_default_server >/dev/null 2>&1 || nrc=$?
        case $nrc in
            0) echo "  nginx: catch-all default server enabled (cipi nginx default-server off to revert)" ;;
            2) echo "  nginx: :80 and :443 already have a default server — nothing changed" ;;
            *) echo "  nginx: could not enable the catch-all default server — existing config kept" ;;
        esac
    fi
fi

echo "Migration 5.1.0 complete"
