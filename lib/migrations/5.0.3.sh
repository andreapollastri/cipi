#!/bin/bash
#############################################
# Cipi Migration 5.0.3 — API sudoers + deploy.php recipe options
#
# 1) Whitelist for panel API: app env, app artisan, app run, auth *
# 2) Regenerate Laravel/Octane deploy.php so keep_releases / hook
#    placeholders and deploy-config knobs apply.
#############################################

set -e

CIPI_CONFIG="${CIPI_CONFIG:-/etc/cipi}"
CIPI_LIB="${CIPI_LIB:-/opt/cipi/lib}"

echo "Migration 5.0.3 — Regenerate cipi-api sudoers (env, auth, artisan, run)..."

if [[ -f "${CIPI_LIB}/cipi-api-sudoers.sh" ]]; then
    # shellcheck source=/dev/null
    source "${CIPI_LIB}/cipi-api-sudoers.sh"
    if type write_cipi_api_sudoers &>/dev/null; then
        write_cipi_api_sudoers
        echo "  Wrote /etc/sudoers.d/cipi-api"
    else
        echo "  WARN: write_cipi_api_sudoers not found"
    fi
else
    echo "  WARN: ${CIPI_LIB}/cipi-api-sudoers.sh not found"
fi

echo "Migration 5.0.3 — Regenerate deploy.php (deploy-config placeholders)..."

if [[ -f "${CIPI_LIB}/common.sh" ]]; then
    # shellcheck source=/dev/null
    source "${CIPI_LIB}/common.sh"
fi
if [[ -f "${CIPI_LIB}/app.sh" ]]; then
    # shellcheck source=/dev/null
    source "${CIPI_LIB}/app.sh"
fi

if type _create_deployer_config_for_app &>/dev/null && [[ -f "${CIPI_CONFIG}/apps.json" ]]; then
    while IFS= read -r app; do
        [[ -z "$app" ]] && continue
        [[ "$(app_get "$app" custom)" == "true" ]] && continue
        [[ -f "/home/${app}/.deployer/deploy.php" ]] || continue
        _create_deployer_config_for_app "$app"
        echo "  Regenerated deploy.php for ${app}"
    done < <(vault_read apps.json 2>/dev/null | jq -r 'keys[]' 2>/dev/null || true)
fi

echo "Migration 5.0.3 complete"
