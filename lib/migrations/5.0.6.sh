#!/bin/bash
#############################################
# Cipi Migration 5.0.6 — API sudoers for server mgmt + webhook recreate
#
# Whitelist for panel API:
#   php list|install|remove, ssh list|add|remove,
#   service list|restart, status, db install|default,
#   app webhook recreate
#############################################

set -e

CIPI_LIB="${CIPI_LIB:-/opt/cipi/lib}"

echo "Migration 5.0.6 — Regenerate cipi-api sudoers (php/ssh/service/webhook/db engines)..."

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

echo "Migration 5.0.6 complete"
