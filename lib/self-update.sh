#!/bin/bash
#############################################
# Cipi — Self-Update
#############################################

readonly _CIPI_REPO="cipi-sh/cipi"
readonly _CIPI_SELFUPDATE_BACKUP_KEEP=7

_selfupdate_prune_backups() {
    local keep="${_CIPI_SELFUPDATE_BACKUP_KEEP}" dir count=0 removed=0
    while IFS= read -r dir; do
        [[ -d "$dir" ]] || continue
        ((count++)) || true
        if (( count > keep )); then
            rm -rf "$dir" && ((removed++)) || true
        fi
    done < <(ls -1dt /opt/cipi.bak.* 2>/dev/null)
    if (( removed > 0 )); then
        info "Pruned ${removed} old self-update backup(s) (keeping ${keep} newest)"
        log_action "SELF-UPDATE: pruned ${removed} /opt/cipi.bak.* (keep=${keep})"
    fi
}

selfupdate_command() {
    parse_args "$@"
    local branch="${ARG_branch:-latest}"
    if [[ "${ARG_check:-}" == "true" ]]; then
        local rv; rv=$(curl -fsSL "https://raw.githubusercontent.com/${_CIPI_REPO}/refs/heads/${branch}/version.md" 2>/dev/null | tr -d '[:space:]')
        [[ -z "$rv" ]] && { error "Cannot check updates"; exit 1; }
        [[ "$rv" == "$CIPI_VERSION" ]] && success "Up to date (v${CIPI_VERSION})" || info "Update: v${CIPI_VERSION} → v${rv}"
        return
    fi

    step "Downloading from '${branch}'..."
    local tmp="/tmp/cipi-update-$$"; rm -rf "$tmp"
    GIT_TERMINAL_PROMPT=0 git clone -b "$branch" --depth 1 "https://github.com/${_CIPI_REPO}.git" "$tmp" 2>/dev/null \
        || { error "Download failed"; exit 1; }
    local nv; nv=$(tr -d '[:space:]' < "${tmp}/version.md" 2>/dev/null)
    [[ -z "$nv" ]] && { error "Invalid package (version.md missing)"; rm -rf "$tmp"; exit 1; }
    info "Updating v${CIPI_VERSION} → v${nv}"
    cp -r /opt/cipi "/opt/cipi.bak.$(date +%Y%m%d%H%M%S)" 2>/dev/null||true
    cp "${tmp}/cipi" /usr/local/bin/cipi; chmod 700 /usr/local/bin/cipi
    cp "${tmp}"/lib/*.sh /opt/cipi/lib/; chmod 700 /opt/cipi/lib/*.sh
    cp "${tmp}/lib/gui-reset-admin.php" /opt/cipi/lib/ 2>/dev/null || true
    chmod 644 /opt/cipi/lib/gui-reset-admin.php 2>/dev/null || true
    [[ -d "${tmp}/lib/deployer" ]] && cp -r "${tmp}/lib/deployer" /opt/cipi/lib/
    [[ -f "${tmp}/lib/cipi-worker.sh" ]] && cp "${tmp}/lib/cipi-worker.sh" /usr/local/bin/cipi-worker && chmod 700 /usr/local/bin/cipi-worker
    [[ -f "${tmp}/lib/cipi-cron-notify.sh" ]] && cp "${tmp}/lib/cipi-cron-notify.sh" /usr/local/bin/cipi-cron-notify && chmod 700 /usr/local/bin/cipi-cron-notify
    [[ -f "${tmp}/lib/cipi-auth-notify.sh" ]] && cp "${tmp}/lib/cipi-auth-notify.sh" /usr/local/bin/cipi-auth-notify && chmod 700 /usr/local/bin/cipi-auth-notify
    [[ -f "${tmp}/lib/cipi-app-notify.sh" ]] && cp "${tmp}/lib/cipi-app-notify.sh" /usr/local/bin/cipi-app-notify && chmod 700 /usr/local/bin/cipi-app-notify
    [[ -f "${tmp}/lib/cipi-health-check.sh" ]] && cp "${tmp}/lib/cipi-health-check.sh" /usr/local/bin/cipi-health-check && chmod 700 /usr/local/bin/cipi-health-check
    [[ -f "${tmp}/lib/cipi-read-app-logs.sh" ]] && cp "${tmp}/lib/cipi-read-app-logs.sh" /usr/local/bin/cipi-read-app-logs && chmod 755 /usr/local/bin/cipi-read-app-logs
    [[ -d "${tmp}/cipi-api" ]] && rm -rf /opt/cipi/cipi-api && cp -a "${tmp}/cipi-api" /opt/cipi/cipi-api
    chown -R root:root /usr/local/bin/cipi /opt/cipi

    # This process started with pre-update common.sh; reload lib helpers copied above
    # so migrations and composer steps see functions added in this release.
    # shellcheck source=/dev/null
    source /opt/cipi/lib/common.sh

    # Run migrations — child bash processes need CIPI_* in the environment.
    # The main cipi binary sets CIPI_LIB/CONFIG/LOG readonly; export by name only (no reassignment).
    export CIPI_LIB CIPI_CONFIG CIPI_LOG
    [[ -z "${CIPI_API_ROOT:-}" ]] && export CIPI_API_ROOT="/opt/cipi/api"
    [[ -z "${CIPI_GUI_ROOT:-}" ]] && export CIPI_GUI_ROOT="/opt/cipi/gui"
    export CIPI_UPDATE_TMP="$tmp"

    # The blanket `chown -R root:root /opt/cipi` above also re-roots the panel
    # Laravel app under /opt/cipi/api: storage/, database/ and bootstrap/cache/
    # become root:root, so PHP-FPM (www-data) can no longer open
    # storage/logs/laravel.log or write the SQLite DB → the panel returns HTTP
    # 500 after EVERY self-update (including the nightly cron). The cipi-api
    # block below only reclaims them when /opt/cipi/cipi-api exists, so on
    # package-from-packagist installs it was skipped and the panel stayed
    # broken. Reclaim the writable paths unconditionally, right here.
    ensure_cipi_api_permissions
    if [[ -f /opt/cipi/lib/gui.sh ]]; then
        # shellcheck source=/dev/null
        source /opt/cipi/lib/gui.sh
        ensure_cipi_gui_permissions
    fi

    if [[ -d "${tmp}/lib/migrations" ]]; then
        for m in $(ls "${tmp}/lib/migrations/"*.sh 2>/dev/null|sort -V); do
            local mv; mv=$(basename "$m" .sh)
            if [[ "$(printf '%s\n' "$CIPI_VERSION" "$mv"|sort -V|head -1)" == "$CIPI_VERSION" && "$mv" != "$CIPI_VERSION" ]]; then
                step "Migration ${mv}..."
                if ! ( set -euo pipefail; bash "$m" ); then
                    error "Migration ${mv} failed — version not updated, installation unchanged at v${CIPI_VERSION}"
                    rm -rf "$tmp"
                    exit 1
                fi
            fi
        done
    fi

    # Record the new version before panel package updates. Composer/migrate for
    # cipi/api can take minutes or hang; core files and migrations are already done.
    if declare -f _cipi_ensure_config_writable >/dev/null 2>&1; then
        _cipi_ensure_config_writable || true
    fi
    echo "$nv" > "${CIPI_CONFIG}/version"
    step "Core updated to v${nv}"

    # Auto-update cipi-api package in installed API app
    if [[ -f "${CIPI_API_ROOT:-/opt/cipi/api}/artisan" ]]; then
        step "Updating cipi-api in Laravel app..."
        local api_root="${CIPI_API_ROOT:-/opt/cipi/api}"
        if [[ -f /opt/cipi/lib/api.sh ]]; then
            # shellcheck source=/dev/null
            source /opt/cipi/lib/api.sh
        fi
        # Same as `cipi api update`: stop the queue before composer/migrate so the
        # panel SQLite DB is not locked (migrate can hang forever otherwise).
        systemctl stop cipi-queue 2>/dev/null || true
        if [[ -d /opt/cipi/cipi-api ]]; then
            (cd "$api_root" && composer config repositories.cipi-api path /opt/cipi/cipi-api 2>/dev/null) || true
        else
            _api_composer_vcs_repo "$api_root"
            (cd "$api_root" && composer config minimum-stability dev 2>/dev/null) || true
            (cd "$api_root" && composer config prefer-stable true 2>/dev/null) || true
        fi
        _api_composer_prepare_github "$api_root" || true
        step "Composer update cipi/api (GitHub VCS — may take a few minutes)..."
        export GIT_TERMINAL_PROMPT=0
        if ! (cd "$api_root" && composer update cipi/api --no-interaction --prefer-dist); then
            warn "Composer update cipi/api failed — panel API package unchanged (run: cipi api update)"
        fi
        # Composer runs as root; reclaim ownership before migrate (SQLite/logs must be www-data-writable).
        step "Reclaiming API permissions & running migrations..."
        chown -R www-data:www-data "$api_root"
        (cd "$api_root" && sudo -u www-data php artisan vendor:publish --tag=cipi-assets --force 2>/dev/null) || true
        (cd "$api_root" && sudo -u www-data php artisan migrate --force 2>/dev/null) || true
        systemctl restart cipi-queue 2>/dev/null || true
        success "cipi-api package updated in Laravel app"
    fi

    # Auto-update cipi/gui package in installed GUI app (from GitHub VCS).
    # Never let a composer helper miss abort the update after chown root:root —
    # that left .env root-owned and the panel on HTTP 500 (v5.0.2 → 5.0.12).
    if [[ -f "${CIPI_GUI_ROOT:-/opt/cipi/gui}/artisan" ]] && [[ -f /opt/cipi/lib/gui.sh ]]; then
        step "Updating cipi/gui in Laravel app..."
        # shellcheck source=/dev/null
        source /opt/cipi/lib/gui.sh
        if ! _gui_update_package; then
            warn "cipi/gui package update failed — continuing (run: cipi gui update)"
        fi
        ensure_cipi_gui_permissions || true
        _gui_create_fpm_pool || true
        reload_php_fpm "8.5" 2>/dev/null || true
        success "cipi/gui package step finished"
    fi

    rm -rf "$tmp"
    _selfupdate_prune_backups

    # Re-source so helpers added in this update (e.g. purge_orphan_app_users) are
    # available even when no new migration ran (same-version refresh / already on 4.7.19).
    # shellcheck source=/dev/null
    source "${CIPI_LIB}/common.sh"
    # Final reclaim: blanket chown root:root above must never leave the panel unreadable.
    ensure_cipi_api_permissions || true
    if declare -f ensure_cipi_gui_permissions >/dev/null 2>&1; then
        ensure_cipi_gui_permissions || true
    elif [[ -f /opt/cipi/lib/gui.sh ]]; then
        # shellcheck source=/dev/null
        source /opt/cipi/lib/gui.sh
        ensure_cipi_gui_permissions || true
    fi
    if declare -f purge_orphan_app_users >/dev/null; then
        step "Purging orphan app users..."
        purge_orphan_app_users || true
    fi

    log_action "SELF-UPDATE: v${CIPI_VERSION} → v${nv}"
    success "Updated to v${nv}"
}
