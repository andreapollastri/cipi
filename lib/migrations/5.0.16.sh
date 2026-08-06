#!/bin/bash
#############################################
# Cipi Migration 5.0.16 — drop stale cipi/gui VCS repo
#
# Soft updates used repositories.cipi-gui type=vcs (GitHub), which hangs
# Composer. cipi/gui is not on Packagist; updates now sync a GitHub tar
# into /opt/cipi/cipi-gui and use a path repository.
#############################################

set -euo pipefail

echo "Migration 5.0.16 — Prefer path package for panel cipi/gui soft updates..."

GUI_ROOT="${CIPI_GUI_ROOT:-/opt/cipi/gui}"
if [[ -f "${GUI_ROOT}/composer.json" ]]; then
    if grep -q '"cipi-gui"' "${GUI_ROOT}/composer.json" 2>/dev/null; then
        (cd "${GUI_ROOT}" && composer config --unset repositories.cipi-gui 2>/dev/null) || true
        echo "  Unset repositories.cipi-gui in ${GUI_ROOT}/composer.json"
    else
        echo "  No repositories.cipi-gui entry — ok"
    fi
else
    echo "  Panel GUI not installed — skip"
fi

echo "Migration 5.0.16 complete"
