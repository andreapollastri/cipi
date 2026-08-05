#!/bin/bash
#############################################
# Cipi Migration 5.0.4 — recover remount-ro for write ops
#
# Kernel / Ubuntu remount-ro left /etc/cipi and /etc/nginx read-only.
# Read commands already survive (4.7.17+); mutating ops (basicauth,
# apps.json) still failed. lib/vault.sh now remounts rw on write via
# _cipi_ensure_config_writable; this migration recovers the live mount
# immediately so the next panel/CLI write works without a reboot.
#############################################

set -e

CIPI_CONFIG="${CIPI_CONFIG:-/etc/cipi}"
CIPI_LIB="${CIPI_LIB:-/opt/cipi/lib}"

echo "Migration 5.0.4 — Ensure /etc/cipi writable for basicauth / vault writes..."

_probe() {
    local probe="${CIPI_CONFIG}/.cipi-migrate-writable-$$"
    touch "$probe" 2>/dev/null || return 1
    rm -f "$probe" 2>/dev/null || true
    return 0
}

if _probe; then
    echo "  /etc/cipi already writable"
else
    echo "  /etc/cipi is read-only — attempting mount -o remount,rw / ..."
    mount -o remount,rw / 2>/dev/null || true
    if _probe; then
        echo "  remount,rw / succeeded"
    else
        echo "  WARN: / still read-only — basicauth/vault writes will keep failing"
        echo "        Check: mount | grep ' on / '; dmesg | tail -20"
    fi
fi

VAULT="${CIPI_LIB}/vault.sh"
if [[ -f "$VAULT" ]] && grep -q '_cipi_ensure_config_writable' "$VAULT"; then
    echo "  lib/vault.sh: write-path remount helper present"
else
    echo "  WARN: lib/vault.sh missing _cipi_ensure_config_writable — re-run cipi self-update"
fi

if [[ -f "${CIPI_LIB}/app.sh" ]] && grep -q '_cipi_ensure_config_writable' "${CIPI_LIB}/app.sh"; then
    echo "  lib/app.sh: basicauth fail-closed on read-only fs"
else
    echo "  WARN: lib/app.sh missing basicauth writable guard — re-run cipi self-update"
fi

echo "Migration 5.0.4 complete"
