#!/usr/bin/env bash
# Local regression checks for 5.0.1 (first-deploy horizon/octane current guard).
# Run from repo root: bash tests/verify-5.0.1.sh
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
LIB="${ROOT}/lib"
PASS=0
FAIL=0

pass() { echo "  OK: $1"; PASS=$((PASS + 1)); }
fail() { echo "  FAIL: $1"; FAIL=$((FAIL + 1)); }

echo "=== Cipi 5.0.1 regression checks ==="

[[ "$(tr -d '[:space:]' < "${ROOT}/version.md")" == "5.0.1" ]] && pass "version.md is 5.0.1" || fail "version.md"
[[ -f "${LIB}/migrations/5.0.1.sh" ]] && pass "migration 5.0.1 present" || fail "migration 5.0.1"
grep -q '_create_deployer_config_for_app' "${LIB}/migrations/5.0.1.sh" && pass "migration regenerates deploy.php" || fail "migration regenerate"
grep -q 'deploy_path}}/current' "${LIB}/deployer/laravel.php" && pass "laravel horizon skips missing current" || fail "laravel current guard"
grep -q 'deploy_path}}/current' "${LIB}/deployer/laravel-octane.php" && pass "octane horizon skips missing current" || fail "octane current guard"
if grep -E "run\(.*\{\{current_path\}\}" "${LIB}/deployer/laravel.php" >/dev/null; then fail "laravel still uses current_path"; else pass "laravel no current_path"; fi
if grep -E "run\(.*\{\{current_path\}\}" "${LIB}/deployer/laravel-octane.php" >/dev/null; then fail "octane still uses current_path"; else pass "octane no current_path"; fi
grep -q '## \[5.0.1\]' "${ROOT}/CHANGELOG.md" && pass "CHANGELOG has 5.0.1" || fail "CHANGELOG 5.0.1"

echo ""
echo "=== Results: ${PASS} passed, ${FAIL} failed ==="
[[ "$FAIL" -eq 0 ]]
