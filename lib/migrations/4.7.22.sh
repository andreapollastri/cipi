#!/bin/bash
#############################################
# Cipi Migration 4.7.22 — SMTP via /etc/msmtprc (AppArmor-safe)
#
# msmtp on Debian/Ubuntu often cannot use /etc/cipi/.msmtprc:
#   - setgid drops access to 750 /etc/cipi
#   - AppArmor denies reading /etc/cipi/* and executing cat from passwordeval
#   - AppArmor may deny custom logfile paths under /var/log
# Fix: system /etc/msmtprc with embedded password; no -C, no passwordeval, no logfile.
#############################################

set -e

CIPI_LIB="${CIPI_LIB:-/opt/cipi/lib}"
CIPI_CONFIG="${CIPI_CONFIG:-/etc/cipi}"

echo "Migration 4.7.22 — SMTP /etc/msmtprc (AppArmor-safe)..."

rm -f "${CIPI_CONFIG}/.msmtprc" 2>/dev/null || true
echo "  Removed legacy ${CIPI_CONFIG}/.msmtprc (if any)"

if command -v msmtp &>/dev/null; then
    bin=$(command -v msmtp)
    if [[ -g "$bin" ]]; then
        chmod g-s "$bin" 2>/dev/null || true
        echo "  Cleared setgid on ${bin} (best-effort)"
    fi
fi

if [[ -f "${CIPI_CONFIG}/smtp.json" ]]; then
    # shellcheck source=/dev/null
    source "${CIPI_LIB}/common.sh"
    if declare -f _smtp_write_rc &>/dev/null && _smtp_write_rc; then
        echo "  Wrote /etc/msmtprc"
        ls -l /etc/msmtprc 2>/dev/null || true
    else
        echo "  WARN: could not write /etc/msmtprc — run: cipi smtp configure"
    fi
else
    echo "  SMTP not configured — skip"
fi

echo "Migration 4.7.22 complete."
