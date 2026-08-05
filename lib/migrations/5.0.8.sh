#!/bin/bash
#############################################
# Cipi Migration 5.0.8 — Panel API from GitHub VCS
#
# cipi/api 1.16+ fixes open_basedir crashes on POST /api/php/install (GUI
# "Server Error") and improves server-management endpoints. Packagist can lag
# behind GitHub; wire composer to cipi-sh/api like cipi/gui and update.
#############################################

set -e

CIPI_LIB="${CIPI_LIB:-/opt/cipi/lib}"
CIPI_API_ROOT="${CIPI_API_ROOT:-/opt/cipi/api}"

echo "Migration 5.0.8 — Panel API VCS (cipi/api from GitHub)..."

if [[ ! -f "${CIPI_API_ROOT}/artisan" ]]; then
    echo "  No panel API at ${CIPI_API_ROOT} — skip"
    echo "Migration 5.0.8 complete"
    exit 0
fi

if [[ ! -f "${CIPI_LIB}/api.sh" ]]; then
    echo "  WARN: ${CIPI_LIB}/api.sh not found — skip"
    echo "Migration 5.0.8 complete"
    exit 0
fi

# Migrations run in a fresh bash subprocess — source helpers explicitly.
if [[ -f "${CIPI_LIB}/common.sh" ]]; then
    # shellcheck source=/dev/null
    source "${CIPI_LIB}/common.sh"
fi
# shellcheck source=/dev/null
source "${CIPI_LIB}/api.sh"

_mig_508_api_perms() {
    if type ensure_cipi_api_permissions &>/dev/null; then
        ensure_cipi_api_permissions
    else
        mkdir -p "${CIPI_API_ROOT}/storage/logs" "${CIPI_API_ROOT}/database" "${CIPI_API_ROOT}/bootstrap/cache" 2>/dev/null || true
        chown -R www-data:www-data "${CIPI_API_ROOT}/storage" "${CIPI_API_ROOT}/database" "${CIPI_API_ROOT}/bootstrap/cache" 2>/dev/null || true
        if [[ -f "${CIPI_API_ROOT}/.env" ]]; then
            chown www-data:www-data "${CIPI_API_ROOT}/.env" 2>/dev/null || true
            chmod 640 "${CIPI_API_ROOT}/.env" 2>/dev/null || true
        fi
    fi
}

_mig_508_api_perms

if [[ -d /opt/cipi/cipi-api ]]; then
    (cd "${CIPI_API_ROOT}" && composer config repositories.cipi-api path /opt/cipi/cipi-api 2>/dev/null) || true
    echo "  Using bundled /opt/cipi/cipi-api path repository"
else
    _api_composer_vcs_repo "${CIPI_API_ROOT}"
    (cd "${CIPI_API_ROOT}" && composer config minimum-stability dev 2>/dev/null) || true
    (cd "${CIPI_API_ROOT}" && composer config prefer-stable true 2>/dev/null) || true
    echo "  Configured composer VCS repo: ${CIPI_API_REPO:-https://github.com/cipi-sh/api}"
fi

echo "  Updating cipi/api package..."
systemctl stop cipi-queue 2>/dev/null || true
(cd "${CIPI_API_ROOT}" && composer update cipi/api --no-interaction 2>/dev/null) || {
    echo "  WARN: composer update cipi/api failed — try: cipi api update"
}
_mig_508_api_perms
(cd "${CIPI_API_ROOT}" && sudo -u www-data php artisan vendor:publish --tag=cipi-assets --force 2>/dev/null) || true
(cd "${CIPI_API_ROOT}" && sudo -u www-data php artisan migrate --force 2>/dev/null) || true
systemctl restart cipi-queue 2>/dev/null || true
systemctl reload "php$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null)-fpm" 2>/dev/null || true

echo "Migration 5.0.8 complete"
