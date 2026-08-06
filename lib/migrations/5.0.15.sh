#!/bin/bash
#############################################
# Cipi Migration 5.0.15 — drop stale cipi/api VCS repo
#
# Soft updates from 5.0.8+ wrote repositories.cipi-api as type=vcs
# (GitHub). That forces Composer to scan/clone the git repo and can hang
# forever, while Packagist already publishes current cipi/api dist zips
# (same path `cipi api upgrade` uses successfully).
#############################################

set -euo pipefail

echo "Migration 5.0.15 — Prefer Packagist for panel cipi/api soft updates..."

API_ROOT="${CIPI_API_ROOT:-/opt/cipi/api}"
if [[ -f "${API_ROOT}/composer.json" ]]; then
    if grep -q '"cipi-api"' "${API_ROOT}/composer.json" 2>/dev/null; then
        (cd "${API_ROOT}" && composer config --unset repositories.cipi-api 2>/dev/null) || true
        echo "  Unset repositories.cipi-api in ${API_ROOT}/composer.json"
    else
        echo "  No repositories.cipi-api entry — ok"
    fi
else
    echo "  Panel API not installed — skip"
fi

echo "Migration 5.0.15 complete"
