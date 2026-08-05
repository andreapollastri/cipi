#!/bin/bash
#############################################
# Cipi Migration 5.0.8 — API IP whitelist
#
# - Default /etc/cipi/api-ip-whitelist (*)
# - Panel API sudoers: cipi api ip-whitelist …
#############################################

set -e

CIPI_LIB="${CIPI_LIB:-/opt/cipi/lib}"
CIPI_CONFIG="${CIPI_CONFIG:-/etc/cipi}"
WL="${CIPI_CONFIG}/api-ip-whitelist"

echo "Migration 5.0.8 — API IP whitelist + sudoers..."

mkdir -p "${CIPI_CONFIG}"
if [[ ! -f "$WL" ]]; then
    printf '%s\n' '*' > "$WL"
    chmod 644 "$WL" 2>/dev/null || true
    echo "  Created ${WL} (allow all)"
else
    echo "  ${WL} already present"
fi

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

echo "Migration 5.0.8 complete"
