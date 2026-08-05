#!/usr/bin/env bash
# Local regression checks for 5.0.7 (SMTP remount-ro before /etc/msmtprc write).
# Run from repo root: bash tests/verify-5.0.7.sh
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
LIB="${ROOT}/lib"
PASS=0
FAIL=0

pass() { echo "  OK: $1"; PASS=$((PASS + 1)); }
fail() { echo "  FAIL: $1"; FAIL=$((FAIL + 1)); }

echo "=== Cipi 5.0.7 regression checks ==="

[[ "$(tr -d '[:space:]' < "${ROOT}/version.md")" == "5.0.7" ]] && pass "version.md is 5.0.7" || fail "version.md"
[[ -f "${LIB}/migrations/5.0.7.sh" ]] && pass "migration 5.0.7 present" || fail "migration 5.0.7"
[[ -f "${LIB}/migrations/5.0.6.sh" ]] && pass "migration 5.0.6 still present" || fail "migration 5.0.6 missing"
[[ ! -f "${LIB}/migrations/5.0.8.sh" ]] && pass "no stray 5.0.8 migration" || fail "5.0.8 migration present"

bash -n "${LIB}/smtp.sh" && pass "smtp.sh syntax" || fail "smtp.sh syntax"
bash -n "${LIB}/migrations/5.0.7.sh" && pass "migration syntax" || fail "migration syntax"

# _smtp_write_rc must remount before writing /etc/msmtprc.
if grep -A40 '^_smtp_write_rc()' "${LIB}/smtp.sh" | grep -q '_cipi_ensure_config_writable'; then
    pass "_smtp_write_rc calls _cipi_ensure_config_writable"
else
    fail "_smtp_write_rc missing _cipi_ensure_config_writable"
fi

if grep -A40 '^_smtp_write_rc()' "${LIB}/smtp.sh" | grep -q '_cipi_path_writable /etc'; then
    pass "_smtp_write_rc probes /etc writability"
else
    fail "_smtp_write_rc missing /etc probe"
fi

if grep -A40 '^_smtp_write_rc()' "${LIB}/smtp.sh" | grep -q 'read-only filesystem'; then
    pass "_smtp_write_rc has clear RO error"
else
    fail "_smtp_write_rc missing RO error message"
fi

if grep -A25 '^_smtp_delete()' "${LIB}/smtp.sh" | grep -q '_cipi_ensure_config_writable'; then
    pass "_smtp_delete remounts before rm"
else
    fail "_smtp_delete missing remount guard"
fi

grep -q 'mount -n -o remount,rw' "${LIB}/migrations/5.0.7.sh" \
    && pass "migration 5.0.7 uses mount -n" \
    || fail "migration missing mount -n"

grep -q '## \[5.0.7\]' "${ROOT}/CHANGELOG.md" && pass "CHANGELOG has 5.0.7" || fail "CHANGELOG"

echo ""
echo "=== Results: ${PASS} passed, ${FAIL} failed ==="
[[ "$FAIL" -eq 0 ]]
