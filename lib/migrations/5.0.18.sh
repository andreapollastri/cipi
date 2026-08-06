#!/bin/bash
#############################################
# Cipi Migration 5.0.18 — GUI path package must be copied, not symlinked
#
# 5.0.16 reintroduced a Composer path repo for cipi/gui without
# options.symlink=false. Composer then symlinks vendor/cipi/gui →
# /opt/cipi/cipi-gui, which PHP-FPM open_basedir (/opt/cipi/gui only)
# blocks → HTTP 500. Repair: path repo with symlink=false, reinstall,
# widen open_basedir, reload FPM.
#############################################

set -euo pipefail

export CIPI_LIB="${CIPI_LIB:-/opt/cipi/lib}"
export CIPI_CONFIG="${CIPI_CONFIG:-/etc/cipi}"
export CIPI_LOG="${CIPI_LOG:-/var/log/cipi}"
[[ -z "${CIPI_GUI_ROOT:-}" ]] && export CIPI_GUI_ROOT="/opt/cipi/gui"
export COMPOSER_ALLOW_SUPERUSER=1

echo "Migration 5.0.18 — GUI vendor copy + open_basedir repair..."

if [[ ! -f /opt/cipi/lib/gui.sh ]]; then
    echo "  lib/gui.sh not found — skip"
    echo "Migration 5.0.18 complete"
    exit 0
fi

# shellcheck source=/dev/null
source /opt/cipi/lib/gui.sh

if [[ -f "${CIPI_GUI_ROOT}/artisan" ]]; then
    if [[ -d /opt/cipi/cipi-gui ]]; then
        _gui_composer_path_repo "${CIPI_GUI_ROOT}" /opt/cipi/cipi-gui || true
        _gui_ensure_vendor_copy "${CIPI_GUI_ROOT}" || true
        echo "  cipi/gui path repo → vendor copy"
    fi
    chown -R www-data:www-data "${CIPI_GUI_ROOT}" 2>/dev/null || true
    ensure_cipi_gui_permissions || true
    _gui_create_fpm_pool || true
    (cd "${CIPI_GUI_ROOT}" && sudo -u www-data php artisan optimize:clear 2>/dev/null) || true
    reload_php_fpm "8.5" 2>/dev/null || true
    echo "  FPM pool + permissions refreshed"
else
    echo "  Panel GUI not installed — skip"
fi

echo "Migration 5.0.18 complete"
