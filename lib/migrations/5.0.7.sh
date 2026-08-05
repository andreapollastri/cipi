#!/bin/bash
#############################################
# Cipi Migration 5.0.7 — SMTP remount-ro recovery
#
# cipi smtp test/configure rewrite /etc/msmtprc on every send. When the
# kernel remounts / read-only, that write failed with:
#   /opt/cipi/lib/smtp.sh: … /etc/msmtprc: Read-only file system
# lib/smtp.sh now calls _cipi_ensure_config_writable + /etc probe (same
# pattern as basicauth). Remount here so SMTP works right after self-update.
#############################################

set -e

CIPI_CONFIG="${CIPI_CONFIG:-/etc/cipi}"
CIPI_LIB="${CIPI_LIB:-/opt/cipi/lib}"

echo "Migration 5.0.7 — SMTP /etc/msmtprc remount-ro recovery..."

_probe() {
    local dir="$1" probe
    [[ -d "$dir" ]] || mkdir -p "$dir" 2>/dev/null || return 1
    probe="${dir}/.cipi-migrate-writable-$$"
    touch "$probe" 2>/dev/null || return 1
    rm -f "$probe" 2>/dev/null || true
    return 0
}

_remount() {
    local path="$1" target="/" source=""
    if command -v findmnt >/dev/null 2>&1; then
        target=$(findmnt -n -o TARGET --target "$path" 2>/dev/null || echo "/")
        source=$(findmnt -n -o SOURCE --target "$path" 2>/dev/null || true)
    fi
    [[ -z "$target" ]] && target="/"
    mount -n -o remount,rw "$target" 2>/dev/null && return 0
    [[ -n "$source" ]] && mount -n -o remount,rw "$source" "$target" 2>/dev/null && return 0
    mount -n -o remount,rw / 2>/dev/null && return 0
    mount -o remount,rw / 2>/dev/null && return 0
    return 1
}

if _probe "$CIPI_CONFIG" && _probe /etc; then
    echo "  /etc and /etc/cipi already writable"
else
    echo "  filesystem read-only — attempting mount -n -o remount,rw ..."
    if _remount "$CIPI_CONFIG" || _remount /etc || _remount /; then
        mkdir -p "$CIPI_CONFIG" 2>/dev/null || true
        if _probe "$CIPI_CONFIG" && _probe /etc; then
            echo "  remount,rw succeeded"
        else
            echo "  WARN: remount reported OK but /etc still not writable"
            echo "        Check: findmnt /; dmesg | tail -20"
        fi
    else
        echo "  WARN: remount,rw failed — cipi smtp test will keep failing until fixed"
        echo "        Try: mount -n -o remount,rw / && cipi smtp test"
        echo "        Check: dmesg | tail -20"
    fi
fi

SMTP="${CIPI_LIB}/smtp.sh"
if [[ -f "$SMTP" ]] && grep -q '_cipi_ensure_config_writable' "$SMTP" && grep -q '_cipi_path_writable /etc' "$SMTP"; then
    echo "  lib/smtp.sh: remount-ro guards present"
else
    echo "  WARN: lib/smtp.sh missing remount guards — re-run cipi self-update"
fi

# Refresh msmtprc if SMTP is already configured (best-effort).
if [[ -f "${CIPI_CONFIG}/smtp.json" ]]; then
    # shellcheck source=/dev/null
    source "${CIPI_LIB}/common.sh"
    if declare -f _smtp_write_rc &>/dev/null && _smtp_write_rc; then
        echo "  Regenerated /etc/msmtprc from vault"
        ls -l /etc/msmtprc 2>/dev/null || true
    else
        echo "  WARN: could not regenerate /etc/msmtprc — run: cipi smtp configure"
    fi
else
    echo "  SMTP not configured — skip msmtprc rewrite"
fi

echo "Migration 5.0.7 complete"
