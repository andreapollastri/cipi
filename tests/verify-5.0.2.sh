#!/usr/bin/env bash
# Local regression checks for 5.0.2 (skip horizon/octane artisan when package missing).
# Run from repo root: bash tests/verify-5.0.2.sh
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
LIB="${ROOT}/lib"
PASS=0
FAIL=0

pass() { echo "  OK: $1"; PASS=$((PASS + 1)); }
fail() { echo "  FAIL: $1"; FAIL=$((FAIL + 1)); }

echo "=== Cipi 5.0.2 regression checks ==="

[[ "$(tr -d '[:space:]' < "${ROOT}/version.md")" == "5.0.2" ]] && pass "version.md is 5.0.2" || fail "version.md"
[[ -f "${LIB}/migrations/5.0.2.sh" ]] && pass "migration 5.0.2 present" || fail "migration 5.0.2"
grep -q '_create_deployer_config_for_app' "${LIB}/migrations/5.0.2.sh" && pass "migration regenerates deploy.php" || fail "migration regenerate"

for tpl in laravel.php laravel-octane.php; do
    grep -q 'vendor/laravel/horizon/composer.json' "${LIB}/deployer/${tpl}" \
        && pass "${tpl} guards horizon package" || fail "${tpl} horizon package guard"
    grep -q 'deploy_path}}/current' "${LIB}/deployer/${tpl}" \
        && pass "${tpl} current symlink guard" || fail "${tpl} current guard"
    if grep -E "run\(.*\{\{current_path\}\}" "${LIB}/deployer/${tpl}" >/dev/null; then
        fail "${tpl} still uses current_path"
    else
        pass "${tpl} no current_path"
    fi
done

grep -q 'vendor/laravel/octane/composer.json' "${LIB}/deployer/laravel-octane.php" \
    && pass "octane reload guards package" || fail "octane package guard"

# Ensure artisan horizon:terminate is still present (not removed entirely)
grep -q 'artisan horizon:terminate' "${LIB}/deployer/laravel.php" \
    && pass "laravel still terminates horizon when present" || fail "laravel horizon terminate missing"

grep -q '## \[5.0.2\]' "${ROOT}/CHANGELOG.md" && pass "CHANGELOG has 5.0.2" || fail "CHANGELOG 5.0.2"

echo ""
echo "=== Results: ${PASS} passed, ${FAIL} failed ==="
[[ "$FAIL" -eq 0 ]]
