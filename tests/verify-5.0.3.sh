#!/usr/bin/env bash
# Local regression checks for 5.0.3 (env/auth/run/deploy-config + sudoers).
# Run from repo root: bash tests/verify-5.0.3.sh
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
LIB="${ROOT}/lib"
PASS=0
FAIL=0

pass() { echo "  OK: $1"; PASS=$((PASS + 1)); }
fail() { echo "  FAIL: $1"; FAIL=$((FAIL + 1)); }

echo "=== Cipi 5.0.3 regression checks ==="

[[ "$(tr -d '[:space:]' < "${ROOT}/version.md")" == "5.0.3" ]] && pass "version.md is 5.0.3" || fail "version.md"
[[ ! -f "${LIB}/migrations/5.0.4.sh" ]] && pass "no stray 5.0.4 migration" || fail "5.0.4 migration still present"
[[ -f "${LIB}/migrations/5.0.3.sh" ]] && pass "migration 5.0.3 present" || fail "migration 5.0.3"
grep -q 'write_cipi_api_sudoers' "${LIB}/migrations/5.0.3.sh" && pass "migration rewrites sudoers" || fail "migration sudoers"
grep -q '_create_deployer_config_for_app' "${LIB}/migrations/5.0.3.sh" && pass "migration regenerates deploy.php" || fail "migration deploy.php"

bash -n "${LIB}/app.sh" && pass "app.sh syntax" || fail "app.sh syntax"
bash -n "${LIB}/cipi-api-sudoers.sh" && pass "sudoers helper syntax" || fail "sudoers syntax"
bash -n "${LIB}/migrations/5.0.3.sh" && pass "migration syntax" || fail "migration syntax"

grep -q '_env_to_json' "${LIB}/app.sh" && pass "app_env helpers" || fail "app_env helpers"
grep -q 'app_run()' "${LIB}/app.sh" && pass "app_run present" || fail "app_run"
grep -q 'app_deploy_config()' "${LIB}/app.sh" && pass "app_deploy_config present" || fail "deploy-config"
grep -q '__CIPI_KEEP_RELEASES__' "${LIB}/deployer/laravel.php" && pass "laravel keep_releases placeholder" || fail "laravel placeholder"
grep -q '__CIPI_HOOK_MIGRATE__' "${LIB}/deployer/laravel-octane.php" && pass "octane migrate placeholder" || fail "octane placeholder"
grep -q 'deploy-config) app_deploy_config' "${LIB}/app.sh" && pass "router wires deploy-config" || fail "router deploy-config"

for cmd in 'app env \*' 'app artisan \*' 'app run \*' 'app deploy-config \*' 'auth create \*' 'auth edit \*' 'auth show \*' 'auth delete \*'; do
    grep -q "/usr/local/bin/cipi ${cmd}" "${LIB}/cipi-api-sudoers.sh" \
        && pass "sudoers has cipi ${cmd}" || fail "sudoers missing ${cmd}"
done

for bad in nano vim less bash tinker ssh; do
    if awk '/_APP_RUN_COMMANDS=\(/,/^\)/' "${LIB}/app.sh" | grep -qw "$bad"; then
        fail "whitelist contains ${bad}"
    else
        pass "whitelist excludes ${bad}"
    fi
done

grep -q '## \[5.0.3\]' "${ROOT}/CHANGELOG.md" && pass "CHANGELOG has 5.0.3" || fail "CHANGELOG"
grep -q '5\.0\.4' "${ROOT}/CHANGELOG.md" && fail "CHANGELOG still mentions 5.0.4" || pass "CHANGELOG has no 5.0.4 section"
grep -q 'deploy-config' "${ROOT}/cipi" && pass "help mentions deploy-config" || fail "help deploy-config"

echo ""
echo "=== Results: ${PASS} passed, ${FAIL} failed ==="
[[ "$FAIL" -eq 0 ]]
