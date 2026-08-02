#!/bin/bash
#############################################
# Cipi Migration 4.8.0 — www redirects + multi-engine DB (PostgreSQL)
#
# - Expose www_redirect / force_https / engine on apps-public.json
# - Refresh panel API sudoers whitelist (www + ssl force + db engines)
# - Initialize db_default_engine / db_engines in server.json
# - Nest legacy flat databases.json under mariadb
#############################################

set -e

CIPI_CONFIG="${CIPI_CONFIG:-/etc/cipi}"
CIPI_LIB="${CIPI_LIB:-/opt/cipi/lib}"

echo "Migration 4.8.0 — www redirects + PostgreSQL engine support..."

if [[ -f "${CIPI_LIB}/common.sh" ]]; then
    # shellcheck source=/dev/null
    source "${CIPI_LIB}/common.sh"
fi

# Engine state + databases.json nesting
if [[ -f "${CIPI_LIB}/db.sh" ]]; then
    # shellcheck source=/dev/null
    source "${CIPI_LIB}/db.sh"
    if type db_ensure_engine_state &>/dev/null; then
        db_ensure_engine_state
        echo "  db engine state initialized (default: mariadb)"
    fi
fi

# Backfill engine=mariadb on existing Laravel apps
if type vault_read &>/dev/null && [[ -f "${CIPI_CONFIG}/apps.json" ]]; then
    local_tmp=$(mktemp)
    if vault_read apps.json | jq '
        with_entries(
            if (.value.custom // false) then .
            else .value.engine = (.value.engine // "mariadb")
            end
        )
    ' > "$local_tmp" 2>/dev/null; then
        vault_write apps.json < "$local_tmp"
        echo "  apps.json: engine=mariadb backfilled where missing"
    fi
    rm -f "$local_tmp"
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

# Pin postgresql packages if unattended-upgrades blacklist exists
if [[ -f /etc/apt/apt.conf.d/50cipi-unattended-upgrades ]]; then
    if ! grep -q '"postgresql' /etc/apt/apt.conf.d/50cipi-unattended-upgrades; then
        sed -i '/"mariadb-common";/a\    "postgresql";\n    "postgresql-.*";\n    "postgresql-common";\n    "postgresql-client.*";' \
            /etc/apt/apt.conf.d/50cipi-unattended-upgrades 2>/dev/null || true
        echo "  unattended-upgrades: postgresql packages blacklisted"
    fi
fi

echo "Migration 4.8.0 complete"
