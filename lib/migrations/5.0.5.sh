#!/bin/bash
#############################################
# Cipi Migration 5.0.5 — harden remount-ro recovery
#
# 5.0.4 tried `mount -o remount,rw /` but that often fails while the root
# FS is still RO (mtab/fstab UUID lookup). lib/vault.sh now uses
# `mount -n` + findmnt SOURCE/TARGET. This migration applies the same
# recovery immediately so basicauth / vault writes work after self-update.
#############################################

set -e

CIPI_CONFIG="${CIPI_CONFIG:-/etc/cipi}"
CIPI_LIB="${CIPI_LIB:-/opt/cipi/lib}"

echo "Migration 5.0.5 — Harden remount,rw for basicauth / vault writes..."

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

if _probe "$CIPI_CONFIG"; then
    echo "  /etc/cipi already writable"
else
    echo "  /etc/cipi is read-only — attempting mount -n -o remount,rw ..."
    if _remount "$CIPI_CONFIG" || _remount /; then
        mkdir -p "$CIPI_CONFIG" 2>/dev/null || true
        if _probe "$CIPI_CONFIG"; then
            echo "  remount,rw succeeded"
        else
            echo "  WARN: remount reported OK but /etc/cipi still not writable"
            echo "        Check: findmnt /; dmesg | tail -20"
        fi
    else
        echo "  WARN: remount,rw failed — basicauth/vault writes will keep failing"
        echo "        Try: mount -n -o remount,rw / && findmnt /"
        echo "        Check: dmesg | tail -20"
    fi
fi

# Also probe nginx credentials dir (same mount on standard installs).
NGINX_BA="/etc/nginx/cipi-basicauth"
if ! _probe "$(dirname "$NGINX_BA")"; then
    echo "  /etc/nginx not writable — retry remount for nginx path..."
    _remount /etc/nginx || _remount / || true
fi
if _probe "$(dirname "$NGINX_BA")"; then
    mkdir -p "$NGINX_BA" 2>/dev/null || true
    echo "  /etc/nginx writable (basicauth dir ready)"
else
    echo "  WARN: /etc/nginx still read-only"
fi

VAULT="${CIPI_LIB}/vault.sh"
if [[ -f "$VAULT" ]] && grep -q '_cipi_remount_rw()' "$VAULT" && grep -q 'mount -n -o remount,rw' "$VAULT"; then
    echo "  lib/vault.sh: hardened remount helper present"
else
    echo "  WARN: lib/vault.sh missing _cipi_remount_rw — re-run cipi self-update"
fi

echo "Migration 5.0.5 complete"
