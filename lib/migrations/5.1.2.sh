#!/bin/bash
#############################################
# Cipi Migration 5.1.2 — WebSockets
#
#  1. Raise the file-descriptor ceilings a WebSocket server runs into. Every
#     open socket costs a descriptor in Reverb and two connections in nginx,
#     and the stock 1024 soft limit caps an app at roughly a thousand clients
#     with nothing in any log to explain it.
#  2. Replace the Reverb nginx location on every app that has it. It used to be
#     `location /app`, a *prefix* match — so `/appointments`, `/apple` and
#     `/application/...` were proxied into Reverb instead of reaching Laravel.
#     The new block is a regex that matches only the two paths the Pusher
#     protocol uses: /app/{key} and /apps/{id}/…
#  3. Fill in the Reverb .env keys Cipi never generated. Before this release
#     REVERB_APP_ID / _KEY / _SECRET were left to the admin — an empty set is
#     what stops `reverb:start` from booting — and the VITE_REVERB_* copies the
#     frontend build reads were missing entirely. Existing values are kept:
#     rotating a key would disconnect every client and invalidate any bundle
#     already built against it.
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

echo "Migration 5.1.2 — WebSocket support (Reverb)..."

# shellcheck source=/dev/null
source "${CIPI_LIB}/common.sh"

# ── 1. File-descriptor limits ────────────────────────────────
# Applied on every server, not only the ones running Reverb: the nginx side
# also lifts the ceiling on ordinary keep-alive traffic, and a server that
# enables Reverb later then has nothing left to do.
nginx_rc=0
_ensure_nginx_ws_limits || nginx_rc=$?
case "$nginx_rc" in
    0) echo "  nginx: worker limits already sufficient" ;;
    2) echo "  nginx: worker_rlimit_nofile ${CIPI_WS_NOFILE}, worker_connections ${CIPI_WS_WORKER_CONNECTIONS}"
       systemctl reload nginx 2>/dev/null || true ;;
    *) echo "  WARNING: nginx.conf could not be updated — it was restored unchanged (check: nginx -t)" ;;
esac

if ! systemctl cat supervisor.service &>/dev/null; then
    echo "  supervisor: not installed — nothing to raise"
elif _ensure_supervisor_fd_limit; then
    echo "  supervisor: already running with ${CIPI_WS_NOFILE} open files"
else
    echo "  supervisor: limit raised to ${CIPI_WS_NOFILE}, applies on the next restart"
    supervisor_restart_pending=true
fi

apps=""
if [[ -f "${CIPI_CONFIG}/apps.json" ]]; then
    apps=$(vault_read apps.json 2>/dev/null | jq -r 'keys[]' 2>/dev/null || true)
fi
[[ -n "$apps" ]] || { echo "Migration 5.1.2 complete (no apps)"; exit 0; }

# shellcheck source=/dev/null
source "${CIPI_LIB}/app.sh"

# ── 2 + 3. Per-app Reverb repair ─────────────────────────────
backup_dir="/var/lib/cipi/vhost-backup-5.1.2"
rewrote=""
touched=0

while IFS= read -r app; do
    [[ -n "$app" ]] || continue
    [[ -n "$(app_get "$app" reverb 2>/dev/null || true)" ]] || continue
    id "$app" &>/dev/null || continue
    touched=$((touched + 1))

    # .env: add what is missing, never overwrite what the app already has.
    envf="/home/${app}/shared/.env"
    if [[ ! -f "$envf" ]]; then
        echo "  WARNING: ${app}: no ${envf} — Reverb credentials not written"
    elif _reverb_sync_env "$app" --fill 2>/dev/null; then
        echo "  ${app}: .env checked (credentials and VITE_REVERB_* filled in where missing)"
    else
        echo "  WARNING: ${app}: could not update ${envf}"
    fi

    vhost="/etc/nginx/sites-available/${app}"
    [[ -f "$vhost" ]] || continue
    # Only the vhosts still carrying the greedy prefix location are rewritten.
    grep -qE '^[[:space:]]*location /app[[:space:]]*\{' "$vhost" 2>/dev/null || continue

    dom=$(app_get "$app" domain 2>/dev/null || true)
    php_ver=$(app_get "$app" php 2>/dev/null || true)
    [[ -n "$dom" && -n "$php_ver" ]] || continue

    # Regenerating drops certbot's :443 block until `certbot install` puts it
    # back — without certbot that would silently take the app off HTTPS.
    if grep -q 'listen 443' "$vhost" 2>/dev/null && ! command -v certbot &>/dev/null; then
        echo "  WARNING: ${app}: /app is still a prefix location, but certbot is missing — vhost left unchanged"
        continue
    fi

    mkdir -p "$backup_dir" 2>/dev/null || true
    cp "$vhost" "${backup_dir}/${app}" 2>/dev/null || continue
    if _create_nginx_vhost "$app" "$dom" "$php_ver" 2>/dev/null; then
        rewrote="${rewrote} ${app}"
        echo "  ${app}: nginx /app was a prefix match (it swallowed /appointments and friends) — now ^/apps?(/|\$)"
    else
        cp "${backup_dir}/${app}" "$vhost" 2>/dev/null || true
        echo "  WARNING: ${app}: vhost could not be regenerated — previous file kept"
    fi
done <<< "$apps"

if [[ -n "$rewrote" ]]; then
    if nginx -t &>/dev/null; then
        # certbot's :443 block lived in the same file and was just overwritten —
        # put the certificate back before reloading.
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

if [[ "$touched" -gt 0 && "${supervisor_restart_pending:-false}" == "true" ]]; then
    echo ""
    echo "  ${YELLOW}This server runs Reverb and Supervisor still has the old limit of"
    echo "  open files — around a thousand concurrent clients. Restarting it"
    echo "  applies ${CIPI_WS_NOFILE}, and also restarts every queue worker:${NC}"
    echo "      ${CYAN}systemctl restart supervisor${NC}"
fi

echo "Migration 5.1.2 complete"
