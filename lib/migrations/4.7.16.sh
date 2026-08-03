#!/bin/bash
#############################################
# Cipi Migration 4.7.16 — common.sh init chmod on read-only /etc
#
# Every `sudo cipi …` sources common.sh, which ran `chmod 700 /etc/cipi` without
# error suppression. On a read-only root (kernel remount-ro, dual-boot NTFS,
# etc.) that aborts even read-only API commands (db list, deploy status, …)
# with: chmod: Read-only file system (os error 30)
#
# Note: early builds of this migration used `|` as the sed delimiter while the
# replacement contained `||` (bash OR). sed treated the first `|` of `||` as
# the end of the replacement and aborted with:
#   sed: -e expression #1, char 72: unknown option to `s'
# That blocked every self-update from < 4.7.16. Delimiters are `#` below;
# when common.sh no longer has the bare chmod line (already shipped fixed),
# this step is a no-op.
#############################################

set -e

CIPI_LIB="${CIPI_LIB:-/opt/cipi/lib}"
COMMON="${CIPI_LIB}/common.sh"

echo "Migration 4.7.16 — Harden common.sh chmod on /etc/cipi..."

[[ -f "$COMMON" ]] || { echo "  common.sh not found — skip"; exit 0; }

if grep -q 'chmod 700 "${CIPI_CONFIG}" 2>/dev/null || true' "$COMMON"; then
    echo "  common.sh already patched"
    exit 0
fi

# self-update copies the new lib before migrations run; modern common.sh has
# neither the bare chmod nor the || true patch (uses _cipi_config_writable).
if ! grep -qE '^chmod 700 "\$\{CIPI_CONFIG\}"$' "$COMMON"; then
    echo "  common.sh has no bare init chmod — skip"
    exit 0
fi

cp -a "$COMMON" "${COMMON}.bak.$(date +%s)"

# /etc/cipi init chmod (runs on every cipi source)
# Use # as sed delimiter — replacement contains || which breaks | delimiters.
sed -i 's#^chmod 700 "${CIPI_CONFIG}"$#chmod 700 "${CIPI_CONFIG}" 2>/dev/null || true#' "$COMMON"
sed -i 's#^mkdir -p "${CIPI_CONFIG}" "${CIPI_LOG}"$#mkdir -p "${CIPI_CONFIG}" "${CIPI_LOG}" 2>/dev/null || true#' "$COMMON"

# apps-public.json projection
sed -i 's#^    chmod 640 "${CIPI_CONFIG}/apps-public.json"$#    chmod 640 "${CIPI_CONFIG}/apps-public.json" 2>/dev/null || true#' "$COMMON"

echo "  Patched ${COMMON}"
echo "Migration 4.7.16 complete"
