#!/bin/bash
#############################################
# Cipi Migration 4.7.21 — Fix msmtp setgid Permission denied
#
# Ubuntu/Debian msmtp is setgid msmtp; after setgid it cannot open
# /etc/cipi/.msmtprc (parent dir is 750 root:cipi-api). Strip setgid so
# root can send mail with Cipi's vault-backed config.
#############################################

set -e

CIPI_LIB="${CIPI_LIB:-/opt/cipi/lib}"
CIPI_CONFIG="${CIPI_CONFIG:-/etc/cipi}"

echo "Migration 4.7.21 — Fix msmtp setgid (SMTP Permission denied)..."

if command -v msmtp &>/dev/null; then
    bin=$(command -v msmtp)
    if [[ -g "$bin" ]]; then
        chmod g-s "$bin"
        echo "  Removed setgid from ${bin}"
    else
        echo "  msmtp already non-setgid"
    fi
else
    echo "  msmtp not installed — skip"
fi

chmod 700 "${CIPI_LIB}/cipi-smtp-pass.sh" 2>/dev/null || true

if [[ -f "${CIPI_CONFIG}/smtp.json" ]]; then
    # shellcheck source=/dev/null
    source "${CIPI_LIB}/common.sh"
    if declare -f _smtp_write_rc &>/dev/null && _smtp_write_rc; then
        echo "  Regenerated ${CIPI_CONFIG}/.msmtprc"
    else
        echo "  WARN: could not regenerate .msmtprc — run: cipi smtp configure"
    fi
fi

echo "Migration 4.7.21 complete."
