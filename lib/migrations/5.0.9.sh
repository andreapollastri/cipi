#!/bin/bash
#############################################
# Cipi Migration 5.0.9 — API sudoers: php switch
#
# Panel API PUT /api/php/default wraps `cipi php switch`, but www-data
# sudoers only allowed php list|install|remove — sudo asked for a password
# without a TTY ("a terminal is required to read the password").
#############################################

set -e

CIPI_LIB="${CIPI_LIB:-/opt/cipi/lib}"

echo "Migration 5.0.9 — Regenerate cipi-api sudoers (php switch)..."

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

echo "Migration 5.0.9 complete"
