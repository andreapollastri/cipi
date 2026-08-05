#!/usr/bin/env bash
# Local regression checks for 5.0.5 (hardened remount-ro for basicauth/vault).
# Run from repo root: bash tests/verify-5.0.5.sh
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
LIB="${ROOT}/lib"
PASS=0
FAIL=0

pass() { echo "  OK: $1"; PASS=$((PASS + 1)); }
fail() { echo "  FAIL: $1"; FAIL=$((FAIL + 1)); }

echo "=== Cipi 5.0.5 regression checks ==="

[[ "$(tr -d '[:space:]' < "${ROOT}/version.md")" == "5.0.5" ]] && pass "version.md is 5.0.5" || fail "version.md"
[[ -f "${LIB}/migrations/5.0.5.sh" ]] && pass "migration 5.0.5 present" || fail "migration 5.0.5"
[[ -f "${LIB}/migrations/5.0.4.sh" ]] && pass "migration 5.0.4 still present" || fail "migration 5.0.4 missing"
[[ ! -f "${LIB}/migrations/5.0.6.sh" ]] && pass "no stray 5.0.6 migration" || fail "5.0.6 migration present"

bash -n "${LIB}/vault.sh" && pass "vault.sh syntax" || fail "vault.sh syntax"
bash -n "${LIB}/app.sh" && pass "app.sh syntax" || fail "app.sh syntax"
bash -n "${LIB}/migrations/5.0.5.sh" && pass "migration syntax" || fail "migration syntax"

grep -q '_cipi_remount_rw()' "${LIB}/vault.sh" \
    && pass "vault.sh defines _cipi_remount_rw" \
    || fail "vault.sh missing _cipi_remount_rw"

grep -q 'mount -n -o remount,rw' "${LIB}/vault.sh" \
    && pass "remount uses mount -n" \
    || fail "remount missing mount -n"

grep -q 'findmnt' "${LIB}/vault.sh" \
    && pass "remount uses findmnt" \
    || fail "remount missing findmnt"

grep -q '_cipi_path_writable()' "${LIB}/vault.sh" \
    && pass "vault.sh defines _cipi_path_writable" \
    || fail "vault.sh missing _cipi_path_writable"

grep -A8 '^_cipi_ensure_config_writable()' "${LIB}/vault.sh" | grep -q '_cipi_remount_rw' \
    && pass "ensure calls _cipi_remount_rw" \
    || fail "ensure does not call _cipi_remount_rw"

# Read paths must NOT remount.
if grep -A8 '^vault_init()' "${LIB}/vault.sh" | grep -q '_cipi_ensure_config_writable'; then
    fail "vault_init must not call ensure"
else
    pass "vault_init still probe-only"
fi

if grep -E '_cipi_ensure_config_writable|_cipi_remount_rw' "${LIB}/common.sh" >/dev/null 2>&1; then
    fail "common.sh must not call remount helpers"
else
    pass "common.sh has no remount helpers (read paths safe)"
fi

grep -q '_cipi_path_writable "\$BASICAUTH_DIR"' "${LIB}/app.sh" \
    && pass "basicauth probes nginx credentials dir" \
    || fail "basicauth missing nginx path probe"

grep -q 'Remount failed:' "${LIB}/app.sh" \
    && pass "basicauth surfaces remount error" \
    || fail "basicauth missing remount error detail"

grep -q 'mount -n -o remount,rw' "${LIB}/migrations/5.0.5.sh" \
    && pass "migration 5.0.5 uses mount -n" \
    || fail "migration missing mount -n"

grep -q '## \[5.0.5\]' "${ROOT}/CHANGELOG.md" && pass "CHANGELOG has 5.0.5" || fail "CHANGELOG"

# Simulated RO CIPI_CONFIG: source survives; ensure cannot magically fix a non-root RO dir.
RO_TMP=$(mktemp -d)
RO_CFG="${RO_TMP}/etc/cipi"
RO_LOG="${RO_TMP}/var/log/cipi"
mkdir -p "$RO_CFG" "$RO_LOG"
echo "testkey" > "${RO_CFG}/.vault_key"
chmod 400 "${RO_CFG}/.vault_key"
if [[ "$(uname -s)" == "Darwin" ]]; then
    chflags uchg "$RO_CFG"
elif command -v chattr &>/dev/null; then
    chattr +i "$RO_CFG"
else
    chmod 555 "$RO_CFG"
fi

export CIPI_LIB="$LIB"
export CIPI_CONFIG="$RO_CFG"
export CIPI_LOG="$RO_LOG"
RED=$'\033[0;31m'; GREEN=$'\033[0;32m'; YELLOW=$'\033[1;33m'
CYAN=$'\033[0;36m'; DIM=$'\033[2m'; NC=$'\033[0m'; BOLD=$'\033[1m'

if ( set -euo pipefail; source "${LIB}/common.sh"; true ); then
    pass "source common.sh survives read-only CIPI_CONFIG"
else
    fail "source common.sh aborted on read-only CIPI_CONFIG"
fi

if ( set +e; source "${LIB}/vault.sh"; echo '{}' | vault_write apps.json 2>/dev/null ); then
    fail "vault_write unexpectedly succeeded on read-only CIPI_CONFIG"
else
    pass "vault_write fails closed when remount cannot help"
fi

if [[ "$(uname -s)" == "Darwin" ]]; then
    chflags nouchg "$RO_CFG" 2>/dev/null || true
elif command -v chattr &>/dev/null; then
    chattr -i "$RO_CFG" 2>/dev/null || true
fi
rm -rf "$RO_TMP"

echo ""
echo "=== Results: ${PASS} passed, ${FAIL} failed ==="
[[ "$FAIL" -eq 0 ]]
