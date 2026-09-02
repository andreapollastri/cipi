#!/usr/bin/env bash
# Local regression checks for 5.1.0.
# Run from repo root: bash tests/verify-5.1.0.sh
set -uo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
LIB="${ROOT}/lib"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT
PASS=0
FAIL=0

pass() { echo "  OK: $1"; PASS=$((PASS + 1)); }
fail() { echo "  FAIL: $1"; FAIL=$((FAIL + 1)); }
check() { if eval "$2" >/dev/null 2>&1; then pass "$1"; else fail "$1"; fi; }

echo "=== Cipi 5.1.0 regression checks ==="

# ── Release plumbing
[[ "$(tr -d '[:space:]' < "${ROOT}/version.md")" == "5.1.0" ]] && pass "version.md is 5.1.0" || fail "version.md"
[[ -f "${LIB}/migrations/5.1.0.sh" ]] && pass "migration 5.1.0 present" || fail "migration 5.1.0"
[[ -f "${LIB}/migrations/5.0.18.sh" ]] && pass "migration 5.0.18 still present" || fail "5.0.18 missing"
[[ ! -f "${LIB}/migrations/5.1.1.sh" ]] && pass "no stray 5.1.1 migration" || fail "stray 5.1.1"

echo "-- syntax"
for f in "${ROOT}/cipi" "${ROOT}/setup.sh" "${LIB}"/*.sh "${LIB}/migrations/5.1.0.sh"; do
    bash -n "$f" 2>/dev/null || fail "syntax: $f"
done
pass "all shell files parse"

# ── 1. Deploy failure notification (the bug: unreachable under set -e)
echo "-- deploy"
grep -q 'set +e' "${LIB}/deploy.sh" \
    && pass "_deploy_run disables set -e around the Deployer run" \
    || fail "deploy.sh still lets set -e abort before the failure branch"
grep -q 'rc=${PIPESTATUS\[0\]}' "${LIB}/deploy.sh" \
    && pass "deploy reads the real exit status from PIPESTATUS" \
    || fail "deploy exit status not captured"
grep -A4 'deploy_log_close "$app" "FAILED"' "${LIB}/deploy.sh" | grep -q 'error "Deploy failed' \
    && pass "failure branch is reachable" || fail "failure branch"
grep -q 'deploy_fail' "${LIB}/deploy.sh" && pass "deploy_fail notification present" || fail "deploy_fail"
grep -q 'deploy_log_tee' "${LIB}/deploy.sh" && pass "deploy output is timestamped" || fail "no timestamping"
grep -q 'deploy start' "${LIB}/deploy.sh" && pass "deploy log has a start banner" || fail "no start banner"
grep -q 'release=%s' "${LIB}/deploy.sh" && pass "deploy log records the release" || fail "no release in log"

# ── 2. Automatic (webhook) deploy path
echo "-- automatic deploy"
[[ -f "${LIB}/cipi-app-deploy.sh" ]] && pass "cipi-app-deploy.sh exists" || fail "cipi-app-deploy.sh missing"
grep -q 'cipi-app-deploy' "${LIB}/app.sh"  && pass "app.sh crontab uses the wrapper"  || fail "app.sh crontab"
grep -q 'cipi-app-deploy' "${LIB}/sync.sh" && pass "sync.sh crontab uses the wrapper" || fail "sync.sh crontab"
grep -q 'cipi-app-deploy' "${ROOT}/setup.sh" && pass "setup.sh installs the wrapper" || fail "setup.sh install"
grep -q 'cipi-app-deploy' "${LIB}/self-update.sh" && pass "self-update installs the wrapper" || fail "self-update install"
grep -q 'deploy-ok' "${LIB}/cipi-app-notify.sh" && pass "success notification supported" || fail "no deploy-ok"
grep -q 'deploy-ok' "${LIB}/cipi-app-deploy.sh" && pass "wrapper reports success too" || fail "wrapper success"

# ── 3. Horizon
echo "-- horizon"
grep -q 'reload_supervisor || reload_rc=$?' "${LIB}/worker.sh" \
    && pass "horizon enable survives a supervisor hiccup" || fail "horizon enable still fragile"
grep -A6 'reload_supervisor || reload_rc' "${LIB}/worker.sh" | grep -q 'app_set "$app" horizon "true"' \
    && pass "horizon state is written after the reload" || fail "horizon state write"
grep -q '_horizon_supervisor_state' "${LIB}/worker.sh" && pass "horizon status reads supervisor" || fail "horizon status"

# ── 4. Backup
echo "-- backup"
for fn in _bk_profile_add _bk_run_profile _bk_select_databases _bk_app_paths \
          _bk_prune_profile _bk_verify _bk_check_stale _bk_fetch _bk_encrypt_file; do
    grep -q "^${fn}()" "${LIB}/backup.sh" && pass "backup: ${fn}" || fail "backup: ${fn} missing"
done
grep -q 'htdocs' "${LIB}/backup.sh" && pass "custom apps (htdocs/) are archived" || fail "custom apps still skipped"
grep -q 'db_list_databases' "${LIB}/backup.sh" && pass "databases discovered from the engine" || fail "registry-only discovery"
grep -q 'databases/' "${LIB}/backup.sh" && pass "databases stored in their own directory" || fail "db/app not separated"
grep -q 'CIPI BACKUP START' "${LIB}/backup.sh" && pass "crontab block is marked" || fail "no crontab markers"
grep -q '_bk_prune_legacy' "${LIB}/backup.sh" && pass "legacy --weeks prune still works" || fail "legacy prune dropped"
grep -q 'db_dump_database_ex' "${LIB}/db.sh" && pass "dump supports table exclusions" || fail "no table exclusions"
grep -q 'db_list_databases' "${LIB}/db.sh" && pass "db.sh can enumerate live databases" || fail "db.sh discovery"

echo "-- backup helper behaviour"
cat > "${TMP}/bk.sh" <<'BK'
set -uo pipefail
RED=''; GREEN=''; YELLOW=''; CYAN=''; DIM=''; NC=''; BOLD=''
CIPI_CONFIG=/tmp/cipi-t; CIPI_LOG=/tmp/cipi-t; CIPI_LIB=LIBDIR
info(){ :; }; warn(){ :; }; error(){ :; }; success(){ :; }; step(){ :; }
vault_read(){ echo '{}'; }; vault_write(){ cat >/dev/null; }
source LIBDIR/backup.sh
_bk_valid_cron "0 2 * * *"        || exit 10
_bk_valid_cron "0 2 * * * ; evil" && exit 11
_bk_valid_cron "0 2 * *"          && exit 12
[[ "$(_bk_every_to_cron 30m)" == "1800	*/30 * * * *" ]] || exit 13
[[ "$(_bk_every_to_cron 6h)"  == "21600	0 */6 * * *" ]]  || exit 14
_bk_every_to_cron 7m  && exit 15
_bk_every_to_cron bad && exit 16
[[ "$(_bk_cron_interval_seconds '*/15 * * * *')" == "900" ]] || exit 17
_bk_glob_match tenant_7 "$(printf 'tenant_*')" || exit 18
_bk_glob_match main     "$(printf 'tenant_*')" && exit 19
_bk_glob_match x        ""                     && exit 20
_bk_valid_profile_name hourly-db || exit 21
_bk_valid_profile_name 'x;rm'    && exit 22
[[ "$(_bk_csv_to_json 'a, b ,c' | jq -c .)" == '["a","b","c"]' ]] || exit 23
exit 0
BK
sed -i.bak "s#LIBDIR#${LIB}#g" "${TMP}/bk.sh" && rm -f "${TMP}/bk.sh.bak"
if bash "${TMP}/bk.sh"; then pass "backup helpers behave (cron, intervals, globs, names)"
else fail "backup helpers (exit $?)"; fi

# ── 5. php.ini
echo "-- ini"
[[ -f "${LIB}/ini.sh" ]] && pass "ini.sh exists" || fail "ini.sh missing"
grep -q 'ini_command' "${ROOT}/cipi" && pass "cipi ini is wired up" || fail "cipi ini not dispatched"
grep -q 'cli/conf.d' "${LIB}/ini.sh" && pass "server-wide set also writes the CLI SAPI" || fail "CLI SAPI not written"
grep -q 'auto_prepend_file' "${LIB}/ini.sh" && pass "dangerous keys are explicitly refused" || fail "no deny list"
grep -q '_ini_cascade' "${LIB}/ini.sh" && pass "companion limits are raised with the change" || fail "no cascade"
grep -q 'client_max_body_size' "${LIB}/ini.sh" && pass "nginx body cap is surfaced" || fail "no nginx warning"
grep -q 'php_admin_value\[upload_max_filesize\]' "${LIB}/app.sh" \
    && fail "FPM pool still hardcodes upload_max_filesize (shadows the global file)" \
    || pass "FPM pool no longer shadows the server-wide php.ini"
grep -q '.ini // {}) | to_entries' "${LIB}/app.sh" && pass "FPM pool renders per-app overrides" || fail "pool overrides"
grep -q 'cli/conf.d/99-cipi.ini' "${LIB}/migrations/5.1.0.sh" && pass "migration adds the CLI ini" || fail "migration CLI ini"

# ── 6. cipi.yml
echo "-- cipi.yml"
[[ -f "${LIB}/yml.sh" ]] && pass "yml.sh exists" || fail "yml.sh missing"
grep -q 'yml_command' "${ROOT}/cipi" && pass "cipi yml is wired up" || fail "cipi yml not dispatched"

cat > "${TMP}/yml.sh" <<'YS'
set -uo pipefail
RED=''; GREEN=''; YELLOW=''; CYAN=''; DIM=''; NC=''; BOLD=''
CIPI_LIB=LIBDIR; CIPI_CONFIG=/tmp/cipi-t; CIPI_LOG=/tmp/cipi-t
info(){ :; }; warn(){ :; }; error(){ :; }; success(){ :; }; step(){ :; }
source LIBDIR/yml.sh
ok(){ [[ "$(_yml_parse "$1" "$2" | jq -r .ok)" == "true" ]]; }
_yml_example > TMPDIR/example.yml
ok TMPDIR/example.yml example || exit 30

w(){ printf '%s\n' "$2" > TMPDIR/c.yml; if ok TMPDIR/c.yml iceberg; then echo "ACCEPTED: $1"; exit 31; fi; }
w "cross-app database"   'version: 1
databases:
  - name: otherapp'
w "cross-app backup profile" 'version: 1
backup:
  profiles:
    - name: default
      keep: 5'
w "auto_prepend_file"    'version: 1
app:
  ini:
    auto_prepend_file: /tmp/x.php'
w "cron injection"       'version: 1
backup:
  profiles:
    - name: iceberg-x
      cron: "0 2 * * * ; curl x|bash"
      keep: 3'
w "yaml anchor"          'version: 1
a: &x
  b: 1'
w "no retention"         'version: 1
backup:
  profiles:
    - name: iceberg-x
      every: 30m'
w "unknown top-level key" 'version: 1
bogus: 1'
w "missing version"      'app:
  php: "8.5"'
w "unsupported php"      'version: 1
app:
  php: "8.2"'
w "sql metachars in db"  'version: 1
databases:
  - name: "iceberg_x; DROP DATABASE main"'
w "horizon and queues"   'version: 1
workers:
  horizon: true
  queues:
    - default'
exit 0
YS
sed -i.bak "s#LIBDIR#${LIB}#g; s#TMPDIR#${TMP}#g" "${TMP}/yml.sh" && rm -f "${TMP}/yml.sh.bak"
out=$(bash "${TMP}/yml.sh" 2>&1); rc=$?
if [[ $rc -eq 0 ]]; then pass "cipi.yml: template valid, 11 hostile documents rejected"
else fail "cipi.yml validation (${out})"; fi

grep -q 'a project file cannot' "${LIB}/yml.sh" && pass "namespacing is enforced with an explanation" || fail "namespacing"

# generate must produce something this same Cipi accepts, and round-trip exactly
cat > "${TMP}/gen.sh" <<'GS'
set -uo pipefail
RED=''; GREEN=''; YELLOW=''; CYAN=''; DIM=''; NC=''; BOLD=''
CIPI_LIB=LIBDIR; CIPI_CONFIG=/tmp/cipi-t; CIPI_LOG=/tmp/cipi-t
info(){ :; }; warn(){ echo "WARN $*" >&2; }; error(){ echo "ERR $*" >&2; }
success(){ :; }; step(){ :; }
APPS='{"iceberg":{"domain":"icebergpro.it","aliases":["*.icebergpro.it","www.icebergpro.it"],
 "php":"8.5","custom":false,"horizon":false,
 "ini":{"upload_max_filesize":"50M","display_errors":"Off"}}}'
BK='{"bucket":"b","profiles":{
 "default":{"scope":"all","cron":"0 2 * * *","destinations":["s3"],"retention":{"keep":0,"days":28,"weeks":0},"encrypt":false,"enabled":true},
 "iceberg-db":{"scope":"db","databases":["iceberg","tenant_*"],"exclude_tables":["*.jobs"],
   "cron":"*/30 * * * *","destinations":["local"],"retention":{"keep":48,"days":0,"weeks":0},"encrypt":false,"enabled":true}}}'
vault_read(){ case "$1" in apps.json) echo "$APPS";; backup.json) echo "$BK";; *) echo '{}';; esac; }
vault_write(){ cat >/dev/null; }
app_exists(){ [[ "$1" == iceberg ]]; }
app_get(){ echo "$APPS" | jq -r --arg a "$1" --arg k "$2" '.[$a][$k] // empty'; }
db_engine_is_installed(){ [[ "$1" == mariadb ]]; }
db_list_databases(){ [[ "$1" == mariadb ]] && printf 'iceberg\niceberg_reporting\ntenant_1\nsomeoneelse\n'; }
crontab(){ printf '* * * * * /usr/bin/php8.5 /home/iceberg/current/artisan schedule:run\n'; }
hostname(){ echo vps-test; }
source LIBDIR/backup.sh
source LIBDIR/yml.sh
_yml_source_libs(){ :; }
_bk_configured(){ return 0; }
_yml_read_workers(){ printf 'default\t3\t3\t3600\nemails\t1\t5\t300\n'; }
_yml_generate iceberg > TMPDIR/gen.yml 2>TMPDIR/gen.err
[[ -s TMPDIR/gen.err ]] && { cat TMPDIR/gen.err >&2; exit 40; }
[[ "$(_yml_parse TMPDIR/gen.yml iceberg | jq -r .ok)" == "true" ]] || exit 41
d=$(_yml_parse TMPDIR/gen.yml iceberg | jq -c .data)
[[ "$(echo "$d" | jq -r '.app.php')" == "8.5" ]] || exit 42
[[ "$(echo "$d" | jq -c '.app.aliases')" == '["*.icebergpro.it","www.icebergpro.it"]' ]] || exit 43
[[ "$(echo "$d" | jq -r '.app.ini["display_errors"]')" == "Off" ]] || exit 44
[[ "$(echo "$d" | jq -c '[.databases[].name]')" == '["iceberg_reporting"]' ]] || exit 45
[[ "$(echo "$d" | jq -c '[.workers.queues[] | [.queue,.processes,.tries,.timeout]]')" \
   == '[["default",3,3,3600],["emails",1,5,300]]' ]] || exit 46
[[ "$(echo "$d" | jq -r '.schedule')" == "true" ]] || exit 47
[[ "$(echo "$d" | jq -c '[.backup.profiles[].name]')" == '["iceberg-db"]' ]] || exit 48
[[ "$(echo "$d" | jq -r '.backup.profiles[0].every')" == "30m" ]] || exit 49
[[ "$(echo "$d" | jq -r '.backup.profiles[0].retention.keep')" == "48" ]] || exit 50
exit 0
GS
sed -i.bak "s#LIBDIR#${LIB}#g; s#TMPDIR#${TMP}#g" "${TMP}/gen.sh" && rm -f "${TMP}/gen.sh.bak"
gout=$(bash "${TMP}/gen.sh" 2>&1); grc=$?
if [[ $grc -eq 0 ]]; then pass "cipi yml generate round-trips through the validator unchanged"
else fail "cipi yml generate (exit ${grc}: ${gout})"; fi
grep -q "'default' stay out of the repository" "${LIB}/yml.sh" \
    && pass "generate leaves server-wide backup profiles out" || fail "generate namespacing"
grep -q '_yml_cron_to_every' "${LIB}/yml.sh" && pass "generate renders cron back as every:" || fail "no every: rendering"
grep -q 'sudoers.d/cipi-\${app}-yml' "${LIB}/yml.sh" && pass "auto-apply uses a scoped sudoers rule" || fail "auto-apply sudoers"
grep -q 'yml_auto' "${LIB}/yml.sh" && pass "auto-apply is opt-in per app" || fail "auto-apply gate"

# both deploy paths must honour the same opt-in
grep -q '_deploy_apply_yml' "${LIB}/deploy.sh" \
    && pass "cipi deploy applies cipi.yml when opted in" || fail "CLI deploy ignores cipi.yml"
grep -q 'cipi yml apply' "${LIB}/cipi-app-deploy.sh" \
    && pass "webhook deploy applies cipi.yml when opted in" || fail "webhook deploy ignores cipi.yml"
grep -A3 '_deploy_apply_yml() {' "${LIB}/deploy.sh" | grep -q 'yml_auto.*== "true".*|| return 0' \
    && pass "deploy hook is gated on the opt-in" || fail "deploy hook not gated"

cat > "${TMP}/auto.sh" <<'AS'
set -uo pipefail
RED=''; GREEN=''; YELLOW=''; CYAN=''; DIM=''; NC=''; BOLD=''
CIPI_LIB=LIBDIR; CIPI_CONFIG=/tmp/cipi-t; CIPI_LOG=/tmp/cipi-t
info(){ echo "INFO $*"; }; warn(){ :; }; error(){ echo "ERR $*"; }
success(){ :; }; step(){ :; }
parse_args() {
    for arg in "$@"; do
        case "$arg" in
            --*=*) local k="${arg%%=*}" v="${arg#*=}"; k="${k#--}"; printf -v "ARG_${k//-/_}" '%s' "$v" ;;
            --*)   local k="${arg#--}"; printf -v "ARG_${k//-/_}" '%s' "true" ;;
        esac
    done
}
YMLAUTO="$1"
app_exists(){ [[ "$1" == iceberg ]]; }
app_get(){ [[ "$2" == yml_auto ]] && echo "$YMLAUTO"; }
source LIBDIR/yml.sh
_yml_find_file(){ [[ -f TMPDIR/cipi.yml ]] && { echo TMPDIR/cipi.yml; return 0; }; return 1; }
_yml_source_libs(){ :; }
_yml_build_plan(){ _YML_ACTIONS=(); _YML_BLOCKERS=(); }
_yml_apply_cmd iceberg --yes --auto
AS
sed -i.bak "s#LIBDIR#${LIB}#g; s#TMPDIR#${TMP}#g" "${TMP}/auto.sh" && rm -f "${TMP}/auto.sh.bak"

rm -f "${TMP}/cipi.yml"
o=$(bash "${TMP}/auto.sh" true 2>&1); r=$?
[[ $r -eq 0 && "$o" == *"nothing to reconcile"* ]] \
    && pass "auto: a release with no cipi.yml is a quiet no-op" \
    || fail "auto: missing file should not error (exit ${r}: ${o})"

o=$(bash "${TMP}/auto.sh" false 2>&1); r=$?
[[ $r -ne 0 && "$o" == *"not enabled"* ]] \
    && pass "auto: --auto is refused when the app has not opted in" \
    || fail "auto: --auto not gated (exit ${r}: ${o})"

printf 'version: 1\napp:\n  php: "8.5"\n' > "${TMP}/cipi.yml"
o=$(bash "${TMP}/auto.sh" true 2>&1); r=$?
[[ $r -eq 0 ]] && pass "auto: a valid file applies cleanly" || fail "auto: valid file (exit ${r}: ${o})"

printf 'version: 1\ndatabases:\n  - name: otherapp\n' > "${TMP}/cipi.yml"
o=$(bash "${TMP}/auto.sh" true 2>&1); r=$?
[[ $r -ne 0 && "$o" == *"namespace"* ]] \
    && pass "auto: an out-of-namespace file is refused, not applied" \
    || fail "auto: hostile file (exit ${r}: ${o})"
rm -f "${TMP}/cipi.yml"

# ── 7. nginx default server
echo "-- nginx"
grep -q 'nginx_default_server_owner' "${LIB}/nginx.sh" && pass "default_server detected per port" || fail "per-port detection"
grep -q 'ssl_reject_handshake' "${LIB}/nginx.sh" && pass "HTTPS catch-all rejects the handshake" || fail "no ssl catch-all"
grep -q '000-cipi-default' "${ROOT}/setup.sh" && pass "fresh installs get the HTTPS catch-all" || fail "setup.sh catch-all"

# ── 8. Notifications
echo "-- notifications"
for t in backup_stale ini_set yml_apply yml_fail self_update; do
    grep -q "^${t}|" "${LIB}/notifications.sh" && pass "trigger ${t}" || fail "trigger ${t} missing"
done
grep -q 'self_update' "${LIB}/self-update.sh" && pass "self-update sends a notification" || fail "self-update notify"

# ── 9. Re-sourcing safety (cipi yml apply loads several libs)
echo "-- re-sourcing"
cat > "${TMP}/src.sh" <<'SS'
set -euo pipefail
CIPI_LIB=LIBDIR; CIPI_CONFIG=/tmp/cipi-t; CIPI_LOG=/tmp/cipi-t
RED=''; GREEN=''; YELLOW=''; CYAN=''; DIM=''; NC=''; BOLD=''
vault_read(){ echo '{}'; }; vault_write(){ cat >/dev/null; }
error(){ :; }; warn(){ :; }; info(){ :; }; success(){ :; }; step(){ :; }
source LIBDIR/app.sh;    source LIBDIR/app.sh
source LIBDIR/nginx.sh;  source LIBDIR/nginx.sh
source LIBDIR/backup.sh; source LIBDIR/backup.sh
SS
sed -i.bak "s#LIBDIR#${LIB}#g" "${TMP}/src.sh" && rm -f "${TMP}/src.sh.bak"
bash "${TMP}/src.sh" 2>/dev/null && pass "libs can be sourced twice in one process" || fail "double-source aborts"

echo ""
echo "=== ${PASS} passed, ${FAIL} failed ==="
[[ $FAIL -eq 0 ]]
