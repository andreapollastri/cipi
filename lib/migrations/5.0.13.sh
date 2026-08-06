#!/bin/bash
#############################################
# Cipi Migration 5.0.13 — recover panel after aborted self-update
#
# v5.0.2 → 5.0.12 could abort on `_cipi_composer_prepare_github: command not
# found` after `chown -R root:root /opt/cipi`, leaving GUI/API `.env` and
# storage root-owned → HTTP 500. Reclaim www-data ownership immediately.
#############################################

set -e

CIPI_LIB="${CIPI_LIB:-/opt/cipi/lib}"
CIPI_API_ROOT="${CIPI_API_ROOT:-/opt/cipi/api}"
CIPI_GUI_ROOT="${CIPI_GUI_ROOT:-/opt/cipi/gui}"

echo "Migration 5.0.13 — Reclaim panel permissions after self-update abort..."

if [[ -f "${CIPI_LIB}/common.sh" ]]; then
    # shellcheck source=/dev/null
    source "${CIPI_LIB}/common.sh"
fi

if declare -f ensure_cipi_api_permissions >/dev/null 2>&1; then
    ensure_cipi_api_permissions
    echo "  API permissions reclaimed"
elif [[ -f "${CIPI_API_ROOT}/artisan" ]]; then
    mkdir -p "${CIPI_API_ROOT}/storage/logs" "${CIPI_API_ROOT}/database" "${CIPI_API_ROOT}/bootstrap/cache" 2>/dev/null || true
    chown -R www-data:www-data "${CIPI_API_ROOT}/storage" "${CIPI_API_ROOT}/database" "${CIPI_API_ROOT}/bootstrap/cache" 2>/dev/null || true
    [[ -f "${CIPI_API_ROOT}/.env" ]] && chown www-data:www-data "${CIPI_API_ROOT}/.env" && chmod 640 "${CIPI_API_ROOT}/.env" || true
    echo "  API permissions reclaimed (fallback)"
fi

if [[ -f "${CIPI_LIB}/gui.sh" ]]; then
    # shellcheck source=/dev/null
    source "${CIPI_LIB}/gui.sh"
fi

if declare -f ensure_cipi_gui_permissions >/dev/null 2>&1; then
    ensure_cipi_gui_permissions
    echo "  GUI permissions reclaimed"
elif [[ -d "${CIPI_GUI_ROOT}" ]]; then
    mkdir -p "${CIPI_GUI_ROOT}/storage/logs" "${CIPI_GUI_ROOT}/database" "${CIPI_GUI_ROOT}/bootstrap/cache" 2>/dev/null || true
    chown -R www-data:www-data "${CIPI_GUI_ROOT}/storage" "${CIPI_GUI_ROOT}/database" "${CIPI_GUI_ROOT}/bootstrap/cache" 2>/dev/null || true
    [[ -f "${CIPI_GUI_ROOT}/.env" ]] && chown www-data:www-data "${CIPI_GUI_ROOT}/.env" && chmod 640 "${CIPI_GUI_ROOT}/.env" || true
    echo "  GUI permissions reclaimed (fallback)"
fi

echo "Migration 5.0.13 complete"
