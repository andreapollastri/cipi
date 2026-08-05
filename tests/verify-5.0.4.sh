#!/usr/bin/env bash
# Local regression checks for 5.0.4 (basicauth / vault remount-ro recovery).
# Run from repo root: bash tests/verify-5.0.4.sh
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
LIB="${ROOT}/lib"
PASS=0
FAIL=0

pass() { echo "  OK: $1"; PASS=$((PASS + 1)); }
fail() { echo "  FAIL: $1"; FAIL=$((FAIL + 1)); }

echo "=== Cipi 5.0.4 regression checks ==="

[[ "$(tr -d '[:space:]' < "${ROOT}/version.md")" == "5.0.4" ]] && pass "version.md is 5.0.4" || fail "version.md"
[[ -f "${LIB}/migrations/5.0.4.sh" ]] && pass "migration 5.0.4 present" || fail "migration 5.0.4"
[[ ! -f "${LIB}/migrations/5.0.5.sh" ]] && pass "no stray 5.0.5 migration" || fail "5.0.5 migration present"

bash -n "${LIB}/vault.sh" && pass "vault.sh syntax" || fail "vault.sh syntax"
bash -n "${LIB}/app.sh" && pass "app.sh syntax" || fail "app.sh syntax"
bash -n "${LIB}/migrations/5.0.4.sh" && pass "migration syntax" || fail "migration syntax"

grep -q '_cipi_ensure_config_writable()' "${LIB}/vault.sh" \
    && pass "vault.sh defines _cipi_ensure_config_writable" \
    || fail "vault.sh missing _cipi_ensure_config_writable"

grep -q 'mount -o remount,rw /' "${LIB}/vault.sh" \
    && pass "ensure helper remounts / rw" \
    || fail "ensure helper missing remount"

grep -q '_cipi_ensure_config_writable' "${LIB}/vault.sh" \
    && grep -A6 '^vault_write()' "${LIB}/vault.sh" | grep -q '_cipi_ensure_config_writable' \
    && pass "vault_write uses ensure (not bare probe)" \
    || fail "vault_write does not use ensure"

# Read / init paths must NOT remount (keeps 4.7.17+ read-only-safe behaviour).
if grep -A8 '^vault_init()' "${LIB}/vault.sh" | grep -q '_cipi_ensure_config_writable'; then
    fail "vault_init must not call ensure (would remount on source)"
else
    pass "vault_init still uses probe-only writable check"
fi

if grep -A5 '^_cipi_safe_chmod()' "${LIB}/vault.sh" | grep -q '_cipi_ensure_config_writable'; then
    fail "_cipi_safe_chmod must not call ensure"
else
    pass "_cipi_safe_chmod still probe-only"
fi

# common.sh source init: still probe-only (no ensure → no remount on db list).
if grep -E '_cipi_ensure_config_writable' "${LIB}/common.sh" >/dev/null 2>&1; then
    fail "common.sh must not call _cipi_ensure_config_writable (read-path regression)"
else
    pass "common.sh does not call ensure (read paths stay remount-free)"
fi

grep -q '_cipi_ensure_config_writable' "${LIB}/app.sh" \
    && grep -q 'Cannot write credentials: filesystem is read-only' "${LIB}/app.sh" \
    && pass "basicauth fail-closed on read-only fs" \
    || fail "basicauth missing fail-closed guard"

# _basicauth_set_user must not end with bare || true that masks write failure.
if awk '/^_basicauth_set_user\(\)/,/^basicauth_enable\(\)/' "${LIB}/app.sh" \
    | grep -q 'echo "\${user}:\${hash}" >> "\$file" ||'; then
    pass "htpasswd write checks failure"
else
    fail "htpasswd write still unguarded"
fi

grep -q 'mount -o remount,rw /' "${LIB}/migrations/5.0.4.sh" \
    && pass "migration remounts when RO" \
    || fail "migration missing remount"

# app_run: GNU env must not see `--` after NAME=VALUE (it would exec `--`).
if awk '/^app_run\(\)/,/^_alias_ensure\(\)/' "${LIB}/app.sh" | grep -q '/usr/bin/env.*--.*"\${_APP_RUN_ARGV'; then
    fail "app_run still passes -- before command (env would exec --)"
else
    pass "app_run does not pass -- after env assignments"
fi
grep -q '/usr/bin/env -C "\$workdir"' "${LIB}/app.sh" \
    && pass "app_run uses env -C for workdir" \
    || fail "app_run missing env -C"
grep -q "env: '--'" "${ROOT}/CHANGELOG.md" \
    && pass "CHANGELOG documents app run env fix" \
    || fail "CHANGELOG missing app run env fix"

grep -q '## \[5.0.4\]' "${ROOT}/CHANGELOG.md" && pass "CHANGELOG has 5.0.4" || fail "CHANGELOG"

# ── Simulated read-only CIPI_CONFIG: source must survive; vault_write must fail
#    without remounting a real root (ensure tries remount, probe still fails).
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
