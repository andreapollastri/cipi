#!/bin/bash
#############################################
# Cipi — print SMTP password for msmtp passwordeval
# Reads from encrypted /etc/cipi/smtp.json (no plaintext in .msmtprc).
#############################################
set -euo pipefail

CIPI_CONFIG="${CIPI_CONFIG:-/etc/cipi}"
CIPI_LIB="${CIPI_LIB:-/opt/cipi/lib}"

# shellcheck source=/dev/null
source "${CIPI_LIB}/vault.sh"

vault_read smtp.json | jq -r '.password // empty'
