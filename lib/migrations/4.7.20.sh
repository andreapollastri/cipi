#!/bin/bash
#############################################
# Cipi Migration 4.7.20 — Regenerate SMTP msmtp config
#
# SMTP send used an unquoted heredoc (password `$` expansion), a hardcoded
# tls_trust_file (fails when missing), and hid msmtp errors. Regenerate
# .msmtprc with passwordeval so existing smtp.json keeps working after update.
#############################################

set -e

CIPI_LIB="${CIPI_LIB:-/opt/cipi/lib}"
CIPI_CONFIG="${CIPI_CONFIG:-/etc/cipi}"

echo "Migration 4.7.20 — Regenerate SMTP msmtp config..."

chmod 700 "${CIPI_LIB}/cipi-smtp-pass.sh" 2>/dev/null || true

if [[ ! -f "${CIPI_CONFIG}/smtp.json" ]]; then
    echo "  SMTP not configured — nothing to regenerate"
    echo "Migration 4.7.20 complete."
    exit 0
fi

# shellcheck source=/dev/null
source "${CIPI_LIB}/common.sh"

if declare -f _smtp_write_rc &>/dev/null; then
    if _smtp_write_rc; then
        echo "  Regenerated ${CIPI_CONFIG}/.msmtprc (passwordeval)"
    else
        echo "  WARN: could not regenerate .msmtprc — run: cipi smtp configure"
    fi
else
    echo "  WARN: _smtp_write_rc missing — run: cipi self-update && cipi smtp configure"
fi

echo "Migration 4.7.20 complete."
