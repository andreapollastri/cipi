#!/bin/bash
#############################################
# Cipi Migration 5.0.1 — Regenerate deploy.php (first-deploy horizon guard)
#
# horizon:terminate / octane:reload used {{current_path}}, which runs readlink
# and aborts on first deploy when /home/<app>/current is missing. Templates now
# guard with [ -L {{deploy_path}}/current ]; regenerate each app's deploy.php.
#############################################

set -e

CIPI_CONFIG="${CIPI_CONFIG:-/etc/cipi}"
CIPI_LIB="${CIPI_LIB:-/opt/cipi/lib}"

echo "Migration 5.0.1 — Regenerate Laravel/Octane deploy.php..."

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

echo "Migration 5.0.1 complete"
