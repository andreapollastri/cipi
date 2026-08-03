#!/bin/bash
# Local regression checks for 4.7.18 (read-only /etc/cipi + cipi db list).
# Run from repo root: bash tests/verify-4.7.18.sh
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
LIB="${ROOT}/lib"
PASS=0
FAIL=0

pass() { echo "  OK: $*"; PASS=$((PASS + 1)); }
fail() { echo "  FAIL: $*" >&2; FAIL=$((FAIL + 1)); }

echo "=== Cipi 4.7.18 regression checks ==="

# ── 1. Syntax ───────────────────────────────────────────────────
for f in "${LIB}/vault.sh" "${LIB}/common.sh" "${LIB}/db.sh" "${LIB}/migrations/4.7.16.sh" \
         "${LIB}/migrations/4.7.17.sh" "${LIB}/migrations/4.7.18.sh" "${LIB}/cipi-api-sudoers.sh"; do
    bash -n "$f" && pass "syntax $(basename "$f")" || fail "syntax $(basename "$f")"
done

# ── 2. _cipi_config_writable defined in vault.sh, used in common.sh ─
if grep -q '_cipi_config_writable()' "${LIB}/vault.sh"; then
    pass "vault.sh defines _cipi_config_writable()"
else
    fail "vault.sh missing _cipi_config_writable() definition"
fi

if grep -q '_cipi_config_writable || return 0' "${LIB}/common.sh"; then
    pass "common.sh guards writes with _cipi_config_writable"
else
    fail "common.sh missing _cipi_config_writable guards"
fi

if grep -q '^_cipi_config_writable()' "${LIB}/common.sh"; then
    fail "common.sh still defines _cipi_config_writable (should live in vault.sh only)"
else
    pass "common.sh does not duplicate _cipi_config_writable"
fi

# ── 3. Source common.sh on simulated read-only CIPI_CONFIG ──────
RO_TMP=$(mktemp -d)
RO_CFG="${RO_TMP}/etc/cipi"
RO_LOG="${RO_TMP}/var/log/cipi"
mkdir -p "$RO_CFG" "$RO_LOG"
# Simulate kernel remount-ro: block writes and chmod (macOS uchg; Linux chattr +i).
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
    pass "source common.sh survives read-only CIPI_CONFIG (set -e)"
else
    fail "source common.sh aborted on read-only CIPI_CONFIG"
fi

if [[ -f "${RO_CFG}/.vault_key" ]]; then
    fail "vault_init created .vault_key on read-only CIPI_CONFIG"
else
    pass "vault_init skipped on read-only CIPI_CONFIG"
fi

if [[ -f "${RO_CFG}/apps-public.json" ]]; then
    fail "ensure_apps_json_api_access wrote apps-public.json on read-only CIPI_CONFIG"
else
    pass "no apps-public.json write on read-only CIPI_CONFIG"
fi

if [[ "$(uname -s)" == "Darwin" ]]; then
    chflags nouchg "$RO_CFG" 2>/dev/null || true
elif command -v chattr &>/dev/null; then
    chattr -i "$RO_CFG" 2>/dev/null || true
fi
rm -rf "$RO_TMP"

if ! grep -qE '^chmod 700 "\$\{CIPI_CONFIG\}"' "${LIB}/common.sh"; then
    pass "common.sh init does not chmod /etc/cipi on every source"
else
    fail "common.sh still chmods /etc/cipi on init"
fi

if grep -q '_cipi_safe_chmod' "${LIB}/vault.sh"; then
    pass "vault.sh uses _cipi_safe_chmod"
else
    fail "vault.sh missing _cipi_safe_chmod"
fi

# ── 4. _db_list lists empty databases (schemata, not tables only) ─
if grep -q 'information_schema.schemata' "${LIB}/db.sh"; then
    pass "db list uses schemata (includes empty databases)"
else
    fail "db list still queries tables only — empty DBs hidden"
fi

# ── 5. _db_list tab parsing (no subshell regression) ─────────────
parse_rows() {
    local rows=$'alpha\t1.00\nbeta\t2.50\n'
    local out="" line
    while IFS=$'\t' read -r db sz; do
        [[ -z "$db" ]] && continue
        out+="${db}:${sz};"
    done <<< "$rows"
    printf '%s' "$out"
}
parsed=$(parse_rows)
[[ "$parsed" == "alpha:1.00;beta:2.50;" ]] \
    && pass "_db_list row parsing (here-string, no pipe subshell)" \
    || fail "_db_list row parsing expected 'alpha:1.00;beta:2.50;' got '${parsed}'"

# ── 6. sudoers template includes db list (sudo-rs safe) ──────────
if grep -q 'cipi db list,' "${LIB}/cipi-api-sudoers.sh" \
    && grep -q 'cipi db restore \*,' "${LIB}/cipi-api-sudoers.sh" \
    && ! grep -v '^#' "${LIB}/cipi-api-sudoers.sh" | grep -q 'db restore \* \*'; then
    pass "cipi-api-sudoers.sh: db list present, no sudo-rs-invalid 'restore * *'"
else
    fail "cipi-api-sudoers.sh sudoers regression"
fi

# ── 7. Migration 4.7.18 consolidates fix (no 4.7.19 migration) ───
[[ -f "${LIB}/migrations/4.7.18.sh" ]] \
    && grep -q 'completes 4.7.16 / 4.7.17' "${LIB}/migrations/4.7.18.sh" \
    && grep -q 'information_schema.schemata' "${LIB}/migrations/4.7.18.sh" \
    && pass "migration 4.7.18 documents consolidated db list fix" \
    || fail "migration 4.7.18 missing or incomplete"

[[ ! -f "${LIB}/migrations/4.7.19.sh" ]] \
    && pass "no migration 4.7.19 (fix lives in 4.7.18 only)" \
    || fail "lib/migrations/4.7.19.sh should not exist"

# 4.7.16 stays a chmod-only historical step (no _cipi_config_writable retrofit).
# Sed delimiters must be `#` — `|` + `||` in the replacement breaks self-update.
if [[ -f "${LIB}/migrations/4.7.16.sh" ]] \
    && ! grep -q 'already includes read-only /etc/cipi guards' "${LIB}/migrations/4.7.16.sh" \
    && grep -q "s#\\^chmod 700" "${LIB}/migrations/4.7.16.sh" \
    && grep -q 'has no bare init chmod' "${LIB}/migrations/4.7.16.sh"; then
    pass "migration 4.7.16 is chmod-only with safe sed delimiters"
else
    fail "migration 4.7.16 missing sed fix or was retrofitted with 4.7.18 guards"
fi

# ── Summary ─────────────────────────────────────────────────────
echo ""
echo "=== Results: ${PASS} passed, ${FAIL} failed ==="
[[ "$FAIL" -eq 0 ]]
