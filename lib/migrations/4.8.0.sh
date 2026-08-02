#!/bin/bash
#############################################
# Cipi Migration 4.8.0 — www redirects + ssl force
#
# Expose www_redirect / force_https on apps-public.json and refresh the
# panel API sudoers whitelist (www + ssl force commands).
#############################################

set -e

CIPI_CONFIG="${CIPI_CONFIG:-/etc/cipi}"
CIPI_LIB="${CIPI_LIB:-/opt/cipi/lib}"

echo "Migration 4.8.0 — www redirects + ssl force..."

if [[ -f "${CIPI_LIB}/common.sh" ]]; then
    # shellcheck source=/dev/null
    source "${CIPI_LIB}/common.sh"
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

echo "Migration 4.8.0 complete"
