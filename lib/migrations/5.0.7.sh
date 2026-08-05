#!/bin/bash
#############################################
# Cipi Migration 5.0.7 — API sudoers for SMTP
#
# Whitelist: smtp status|configure|enable|disable|test|delete
# (health already whitelisted)
#############################################

set -e

CIPI_LIB="${CIPI_LIB:-/opt/cipi/lib}"

echo "Migration 5.0.7 — Regenerate cipi-api sudoers (smtp)..."

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

echo "Migration 5.0.7 complete"
