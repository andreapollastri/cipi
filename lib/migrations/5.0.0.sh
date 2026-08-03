#!/bin/bash
#############################################
# Cipi Migration 5.0.0 — Octane + runtime/ops suite
#
# - Octane (FrankenPHP), convert, Reverb, Horizon, schedule CLI
# - Node build, app clone, predeploy snapshot, resource limits
# - DNS-01 Cloudflare SSL, HTTP healthchecks
# - apps-public projection, sudoers, health cron, nginx upgrade map
#############################################

set -e

CIPI_CONFIG="${CIPI_CONFIG:-/etc/cipi}"
CIPI_LIB="${CIPI_LIB:-/opt/cipi/lib}"
CIPI_LOG="${CIPI_LOG:-/var/log/cipi}"

echo "Migration 5.0.0 — Laravel Octane + 5.0 feature suite..."

if [[ -f "${CIPI_LIB}/common.sh" ]]; then
    # shellcheck source=/dev/null
    source "${CIPI_LIB}/common.sh"
fi

# Backfill schedule=on for Laravel apps missing the field
if type vault_read &>/dev/null && [[ -f "${CIPI_CONFIG}/apps.json" ]]; then
    json=$(vault_read apps.json 2>/dev/null || echo '{}')
    updated=$(echo "$json" | jq '
        with_entries(
            if (.value.custom == true or .value.custom == "true") then .
            else .value.schedule = (.value.schedule // "on")
            end
        )
    ')
    if [[ -n "$updated" ]]; then
        echo "$updated" | vault_write apps.json
        echo "  schedule backfill applied"
    fi
fi

if type _ensure_nginx_octane_map &>/dev/null; then
    _ensure_nginx_octane_map
    echo "  nginx Upgrade map ensured"
else
    echo "  _ensure_nginx_octane_map not found — skipped"
fi

# Install healthcheck helper + cron
mkdir -p "${CIPI_LOG}/health"
if [[ -f "${CIPI_LIB}/cipi-health-check.sh" ]]; then
    cp "${CIPI_LIB}/cipi-health-check.sh" /usr/local/bin/cipi-health-check
    chmod 755 /usr/local/bin/cipi-health-check
    echo "  cipi-health-check installed"
fi
if [[ ! -f /etc/cron.d/cipi-health ]]; then
    cat > /etc/cron.d/cipi-health <<'EOF'
# Cipi app HTTP healthchecks (every 5 minutes)
*/5 * * * * root /usr/local/bin/cipi-health-check >/dev/null 2>&1
EOF
    chmod 644 /etc/cron.d/cipi-health
    echo "  health cron installed"
fi

# Ensure cipi-worker is current (covers reverb/horizon)
if [[ -f "${CIPI_LIB}/cipi-worker.sh" ]]; then
    cp "${CIPI_LIB}/cipi-worker.sh" /usr/local/bin/cipi-worker
    chmod 755 /usr/local/bin/cipi-worker
    echo "  cipi-worker refreshed"
fi

if type _update_apps_public &>/dev/null; then
    _update_apps_public
    echo "  apps-public.json regenerated"
else
    echo "  _update_apps_public not found — skipped"
fi

if [[ -f "${CIPI_LIB}/cipi-api-sudoers.sh" ]]; then
    # shellcheck source=/dev/null
    source "${CIPI_LIB}/cipi-api-sudoers.sh"
    if type write_cipi_api_sudoers &>/dev/null; then
        write_cipi_api_sudoers
        echo "  cipi-api sudoers updated"
    fi
fi

# Validate nginx config if nginx is installed
if command -v nginx &>/dev/null && [[ -f /etc/nginx/nginx.conf ]]; then
    if nginx -t 2>/dev/null; then
        systemctl reload nginx 2>/dev/null || true
        echo "  nginx reloaded"
    else
        echo "  nginx -t failed — left config as-is (check manually)"
    fi
fi

echo "Migration 5.0.0 complete"
