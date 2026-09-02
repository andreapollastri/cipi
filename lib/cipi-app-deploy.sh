#!/bin/bash
#############################################
# Cipi — Automatic deploy wrapper (app user)
#
# Runs from the app's own crontab when a webhook drops ~/.deploy-trigger.
# Wraps `dep deploy` so the automatic path produces the same readable log as
# `cipi deploy`: a start/end banner naming the trigger, the branch, the
# release and the duration, and a wall-clock timestamp on every line.
# Raw Deployer output alone had no dates and no release markers, so a deploy
# that failed overnight could not be reconstructed afterwards.
#
# Usage: cipi-app-deploy <app> <php_version> [trigger]
#############################################
set -uo pipefail

APP="${1:-}"; PHP_VER="${2:-}"; TRIGGER="${3:-webhook}"
[[ -z "$APP" || -z "$PHP_VER" ]] && { echo "Usage: cipi-app-deploy <app> <php_version> [trigger]" >&2; exit 2; }

# Refuse to act on anything but the invoking user's own app: this runs from a
# user crontab, so it must never be a lever to touch another app's home.
[[ "$APP" =~ ^[a-z][a-z0-9]{2,31}$ ]]   || { echo "cipi-app-deploy: invalid app name" >&2; exit 2; }
[[ "$PHP_VER" =~ ^8\.[0-9]$ ]]          || { echo "cipi-app-deploy: invalid php version" >&2; exit 2; }
[[ "$TRIGGER" =~ ^[a-z-]{1,20}$ ]]      || TRIGGER="webhook"
if [[ "$(id -un)" != "$APP" && "$(id -u)" -ne 0 ]]; then
    echo "cipi-app-deploy: must run as '${APP}' (or root)" >&2
    exit 2
fi

HOME_DIR="/home/${APP}"
DEPLOY_FILE="${HOME_DIR}/.deployer/deploy.php"
LOG="${HOME_DIR}/logs/deploy.log"

[[ -f "$DEPLOY_FILE" ]] || { echo "cipi-app-deploy: ${DEPLOY_FILE} not found" >&2; exit 2; }
mkdir -p "${HOME_DIR}/logs" 2>/dev/null || true
touch "$LOG" 2>/dev/null || true

_release() {
    local target
    [[ -L "${HOME_DIR}/current" ]] || { echo ""; return 0; }
    target=$(readlink "${HOME_DIR}/current" 2>/dev/null || true)
    [[ -z "$target" ]] && { echo ""; return 0; }
    basename "$target"
}

BRANCH=""
if [[ -d "${HOME_DIR}/current/.git" ]]; then
    BRANCH=$(git -C "${HOME_DIR}/current" rev-parse --abbrev-ref HEAD 2>/dev/null || true)
fi

REL_BEFORE=$(_release)
T0=$SECONDS

{
    printf '[%(%Y-%m-%d %H:%M:%S)T] ===== deploy start  app=%s trigger=%s branch=%s from-release=%s =====\n' \
        -1 "$APP" "$TRIGGER" "${BRANCH:-?}" "${REL_BEFORE:-none}"
} >> "$LOG" 2>/dev/null || true

RC=0
cd "$HOME_DIR" || exit 2
"/usr/bin/php${PHP_VER}" /usr/local/bin/dep deploy -f "$DEPLOY_FILE" 2>&1 \
    | while IFS= read -r line; do
          printf '[%(%Y-%m-%d %H:%M:%S)T] %s\n' -1 "$line"
      done >> "$LOG"
RC=${PIPESTATUS[0]}

REL_AFTER=$(_release)
SECS=$(( SECONDS - T0 ))
RESULT="OK"; [[ $RC -eq 0 ]] || RESULT="FAILED"

{
    printf '[%(%Y-%m-%d %H:%M:%S)T] ===== deploy %s  app=%s release=%s duration=%ss exit=%s =====\n\n' \
        -1 "$RESULT" "$APP" "${REL_AFTER:-?}" "$SECS" "$RC"
} >> "$LOG" 2>/dev/null || true

DETAIL="Trigger: ${TRIGGER}
Branch: ${BRANCH:-?}
Release: ${REL_BEFORE:-none} -> ${REL_AFTER:-?}
Duration: ${SECS}s
Log: ${LOG}"

if [[ $RC -ne 0 ]]; then
    sudo /usr/local/bin/cipi-app-notify "$APP" deploy "$RC" "$LOG" "$DETAIL" 2>/dev/null || true
    exit "$RC"
fi

sudo /usr/local/bin/cipi-app-notify "$APP" deploy-ok 0 "$LOG" "$DETAIL" 2>/dev/null || true

# Reconcile the server with the cipi.yml shipped in this release, but only when
# root has explicitly opted this app in (`cipi yml auto <app> on`), which is
# also what creates the single narrowly scoped sudoers rule below. Without that
# file the app user cannot run this at all.
if [[ -f "/etc/sudoers.d/cipi-${APP}-yml" ]]; then
    {
        printf '[%(%Y-%m-%d %H:%M:%S)T] ===== cipi.yml apply =====\n' -1
    } >> "$LOG" 2>/dev/null || true
    sudo /usr/local/bin/cipi yml apply "$APP" --yes --auto >> "$LOG" 2>&1 || {
        printf '[%(%Y-%m-%d %H:%M:%S)T] cipi.yml apply failed — see above\n' -1 >> "$LOG" 2>/dev/null || true
    }
fi

exit 0
