#!/bin/bash
#############################################
# Cipi — Deploy Management (Deployer)
#############################################

# Refuse to invoke `dep` for an app pinned to PHP < 8.3 when Deployer 8+ is
# installed: `dep` runs under the app's PHP, and the v8 phar can't even start
# on PHP < 8.3, so this fails fast with a clear message instead of a cryptic
# parse/version error. No-op under Deployer 7 (which supports older PHP).
_deploy_assert_php_compat() {
    local app="$1" php_ver="$2"
    [[ -z "$php_ver" ]] && return 0
    local dep_major; dep_major=$(deployer_major_version 2>/dev/null || echo "")
    [[ -z "$dep_major" ]] && return 0
    if (( dep_major >= 8 )) && ! validate_php_version "$php_ver"; then
        error "App '${app}' uses PHP ${php_ver}, but Deployer ${dep_major} requires PHP >= 8.3."
        warn  "Upgrade the app first:  cipi app edit ${app}   (set PHP to 8.3, 8.4 or 8.5)"
        warn  "Install the version if needed:  cipi php install 8.3"
        log_action "DEPLOY BLOCKED: $app php=${php_ver} dep=${dep_major}"
        exit 1
    fi
}

deploy_command() {
    local app="${1:-}"; shift||true
    [[ -z "$app" ]] && { error "Usage: cipi deploy <app> [--rollback|--releases|--key|--webhook|--snapshot|--snapshot-required]"; exit 1; }
    app_exists "$app" || { error "App '$app' not found"; exit 1; }
    parse_args "$@"

    if   [[ "${ARG_rollback:-}" == "true" ]];        then _deploy_rollback "$app"
    elif [[ "${ARG_releases:-}" == "true" ]];        then _deploy_releases "$app"
    elif [[ "${ARG_key:-}" == "true" ]];             then _deploy_key "$app"
    elif [[ "${ARG_webhook:-}" == "true" ]];         then _deploy_webhook "$app"
    elif [[ "${ARG_unlock:-}" == "true" ]];          then _deploy_unlock "$app"
    elif [[ -n "${ARG_trust_host:-}" ]];             then _deploy_trust_host "$app" "${ARG_trust_host}"
    else _deploy_run "$app"
    fi
}

# Pre-deploy DB snapshot (root/vault only). Opt-in via apps.json or --snapshot.
_deploy_predeploy_snapshot() {
    local app="$1"
    local want="${ARG_snapshot:-}"
    local required="${ARG_snapshot_required:-}"
    [[ "$(app_get "$app" predeploy_snapshot)" == "true" ]] && want="true"
    [[ "$want" != "true" && "$required" != "true" ]] && return 0
    [[ "$(app_get "$app" custom)" == "true" ]] && return 0

    # shellcheck source=/dev/null
    source "${CIPI_LIB}/db.sh"
    local eng
    eng=$(app_get "$app" engine); [[ -z "$eng" ]] && eng="mariadb"
    eng=$(db_normalize_engine "$eng" 2>/dev/null || echo "mariadb")
    if ! db_engine_is_installed "$eng"; then
        warn "Pre-deploy snapshot skipped — engine ${eng} not installed"
        [[ "$required" == "true" ]] && { error "Snapshot required but engine missing"; return 1; }
        return 0
    fi

    mkdir -p "${CIPI_LOG}/backups"
    local dump="${CIPI_LOG}/backups/${eng}_${app}_predeploy_$(date +%Y%m%d_%H%M%S).sql.gz"
    step "Pre-deploy DB snapshot → ${dump}..."
    if db_dump_database "$eng" "$app" "$dump"; then
        success "Snapshot saved: ${dump}"
        echo "$dump" > "/tmp/cipi-predeploy-${app}.path" 2>/dev/null || true
        return 0
    fi

    cipi_notify \
        "Cipi pre-deploy snapshot failed: ${app} on $(hostname)" \
        "Database dump before deploy failed.\n\nServer: $(hostname)\nApp: ${app}\nEngine: ${eng}\nTime: $(date '+%Y-%m-%d %H:%M:%S %Z')" \
        deploy_snapshot_fail
    if [[ "$required" == "true" ]]; then
        error "Pre-deploy snapshot failed (required)"
        return 1
    fi
    warn "Pre-deploy snapshot failed — continuing deploy"
    return 0
}

_deploy_run() {
    local app="$1" home="/home/${app}"
    local df="${home}/.deployer/deploy.php"
    local php_ver; php_ver=$(app_get "$app" php)

    if [[ ! -f "$df" ]]; then
        step "Creating deployer config..."
        source "${CIPI_LIB}/app.sh"
        local repo branch is_custom
        repo=$(app_get "$app" repository)
        branch=$(app_get "$app" branch)
        is_custom=$(app_get "$app" custom)
        [[ -z "$php_ver" ]] && { error "App config incomplete (php). Run: cipi app edit $app"; exit 1; }
        if [[ "$is_custom" != "true" && -z "$repo" ]]; then
            error "App config incomplete (repository). Run: cipi app edit $app"
            exit 1
        fi
        _create_deployer_config_for_app "$app"
        success "Deployer config created"
    fi

    _deploy_assert_php_compat "$app" "$php_ver"

    # Legacy per-file ACLs on laravel-*.log break deploy:writable chmod — strip before Deployer runs.
    ensure_app_logs_permissions "$app" || true

    _deploy_predeploy_snapshot "$app" || exit 1
    local snapshot_path=""
    [[ -f "/tmp/cipi-predeploy-${app}.path" ]] && snapshot_path=$(cat "/tmp/cipi-predeploy-${app}.path" 2>/dev/null || true)

    info "Deploying '${app}'..."
    echo ""
    sudo -u "$app" bash -c "cd ${home} && /usr/bin/php${php_ver} /usr/local/bin/dep deploy -f ${df} 2>&1"
    local rc=$?
    echo ""
    if [[ $rc -eq 0 ]]; then
        success "Deploy completed"
        log_action "DEPLOY OK: $app"
        cipi_notify \
            "Cipi deploy succeeded: ${app} on $(hostname)" \
            "Deploy completed successfully.\n\nServer: $(hostname)\nApp: ${app}\nTime: $(date '+%Y-%m-%d %H:%M:%S %Z')" \
            deploy_success
    else
        error "Deploy failed (exit $rc)"
        warn "Rollback code: cipi deploy ${app} --rollback"
        if [[ -n "$snapshot_path" && -f "$snapshot_path" ]]; then
            warn "DB snapshot (not restored automatically): ${snapshot_path}"
            warn "Restore: cipi db restore ${app} ${snapshot_path}"
        fi
        log_action "DEPLOY FAIL: $app exit=$rc"
        cipi_notify \
            "Cipi deploy failed: ${app}" \
            "Deploy exited with code ${rc}. Rollback: cipi deploy ${app} --rollback" \
            deploy_fail
    fi
}

_deploy_unlock() {
    local app="$1" home="/home/${app}"
    local lockfile="${home}/.dep/deploy.lock"
    if [[ ! -f "$lockfile" ]]; then
        info "No deploy lock found for '${app}'"
        return 0
    fi
    rm -f "$lockfile"
    success "Deploy unlocked — run: cipi deploy ${app}"
    log_action "DEPLOY UNLOCK: $app"
}

_deploy_rollback() {
    local app="$1" home="/home/${app}"
    local php_ver; php_ver=$(app_get "$app" php)
    _deploy_assert_php_compat "$app" "$php_ver"
    # Skip confirmation for non-interactive callers (API/UI job runner): a
    # blocking `read` with no TTY would hang the job. --force is parsed upstream
    # in deploy_command.
    if [[ "${ARG_force:-}" != "true" ]] && [[ -t 0 ]]; then
        confirm "Rollback '${app}'?" || { info "Cancelled"; return; }
    fi
    info "Rolling back..."
    sudo -u "$app" bash -c "cd ${home} && /usr/bin/php${php_ver} /usr/local/bin/dep rollback -f ${home}/.deployer/deploy.php 2>&1"
    local rc=$?
    if [[ $rc -eq 0 ]]; then
        success "Rollback done"
        cipi_notify \
            "Cipi deploy rollback: ${app} on $(hostname)" \
            "Deploy rollback completed.\n\nServer: $(hostname)\nApp: ${app}\nTime: $(date '+%Y-%m-%d %H:%M:%S %Z')" \
            deploy_rollback
    else
        error "Rollback failed"
    fi
    log_action "ROLLBACK: $app"
}

_deploy_releases() {
    local app="$1" home="/home/${app}"
    [[ ! -d "${home}/releases" ]] && { info "No releases yet"; return; }
    local current=""
    [[ -L "${home}/current" ]] && current=$(readlink -f "${home}/current" | xargs basename)
    echo -e "\n${BOLD}Releases${NC}"
    ls -1t "${home}/releases" | while read -r r; do
        local mark=""; [[ "$r" == "$current" ]] && mark=" ${GREEN}← current${NC}"
        printf "  ${CYAN}%-20s${NC} %s%b\n" "$r" "$(stat -c '%y' "${home}/releases/${r}" 2>/dev/null|cut -d. -f1)" "$mark"
    done; echo ""
}

_deploy_key() {
    local app="$1" kf="/home/${app}/.ssh/id_ed25519.pub"
    [[ ! -f "$kf" ]] && { error "Key not found"; exit 1; }
    echo -e "\n${BOLD}Deploy Key for '${app}'${NC}"
    echo -e "${CYAN}$(cat "$kf")${NC}\n"

    local git_prov; git_prov=$(app_get "$app" git_provider)
    local git_dkid; git_dkid=$(app_get "$app" git_deploy_key_id)
    if [[ -n "$git_prov" && -n "$git_dkid" ]]; then
        echo -e "  ${GREEN}✓ Auto-configured on ${git_prov} (ID: ${git_dkid})${NC}"
    else
        echo "Add as Deploy Key in your Git provider:"
        echo "  GitHub:  Repo → Settings → Deploy keys → Add deploy key"
        echo "  GitLab:  Repo → Settings → Repository → Deploy keys"
        echo "  Gitea:   Repo → Settings → Deploy keys → Add key"
        echo "  Forgejo: Repo → Settings → Deploy keys → Add key"
        echo "  Custom:  append to ~/.ssh/authorized_keys on the git server"
    fi
    echo ""
    echo "  Trust the host fingerprint with:"
    echo "  ${CYAN}cipi deploy ${app} --trust-host=<host[:port]>${NC}"
    echo ""
}

_deploy_trust_host() {
    local app="$1" hostport="$2"
    local home="/home/${app}" known_hosts

    # Split host and optional port
    local host port
    if [[ "$hostport" == *:* ]]; then
        host="${hostport%%:*}"
        port="${hostport##*:}"
    else
        host="$hostport"
        port="22"
    fi

    [[ -z "$host" ]] && { error "Usage: cipi deploy <app> --trust-host=<host[:port]>"; exit 1; }

    known_hosts="${home}/.ssh/known_hosts"

    step "Scanning SSH fingerprint of ${host}:${port}..."
    local scan_out
    if [[ "$port" == "22" ]]; then
        scan_out=$(ssh-keyscan -T 10 -H "$host" 2>/dev/null)
    else
        scan_out=$(ssh-keyscan -T 10 -p "$port" -H "$host" 2>/dev/null)
    fi

    if [[ -z "$scan_out" ]]; then
        error "Could not reach ${host}:${port} — check the hostname and that port ${port} is open"
        exit 1
    fi

    # Remove any existing entry for this host to avoid duplicates
    if [[ -f "$known_hosts" ]]; then
        local tmp; tmp=$(mktemp)
        ssh-keygen -R "$host" -f "$known_hosts" &>/dev/null || true
        [[ "$port" != "22" ]] && ssh-keygen -R "[${host}]:${port}" -f "$known_hosts" &>/dev/null || true
    fi

    echo "$scan_out" >> "$known_hosts"
    chown "${app}:${app}" "$known_hosts"
    chmod 600 "$known_hosts"

    success "Fingerprint of ${host}:${port} trusted for '${app}'"
    echo ""
    echo -e "${BOLD}Fingerprints added:${NC}"
    echo "$scan_out" | awk '{print "  " $0}' | cut -c1-80
    echo ""

    # Show the deploy key as a reminder
    local kf="${home}/.ssh/id_ed25519.pub"
    if [[ -f "$kf" ]]; then
        echo -e "${BOLD}Deploy Key${NC} (add this to your Git server):"
        echo -e "  ${CYAN}$(cat "$kf")${NC}"
        echo ""
    fi

    # If non-standard port, write/update SSH config entry
    if [[ "$port" != "22" ]]; then
        local ssh_cfg="${home}/.ssh/config"
        # Remove existing Host block for this host if any
        if [[ -f "$ssh_cfg" ]]; then
            local tmp; tmp=$(mktemp)
            awk -v h="$host" '
                /^Host / { in_block = ($2 == h) }
                !in_block { print }
            ' "$ssh_cfg" > "$tmp" && mv "$tmp" "$ssh_cfg"
        fi
        cat >> "$ssh_cfg" <<SSHCFG

Host ${host}
    HostName ${host}
    Port ${port}
    IdentityFile ${home}/.ssh/id_ed25519
    StrictHostKeyChecking accept-new
SSHCFG
        chown "${app}:${app}" "$ssh_cfg"
        chmod 600 "$ssh_cfg"
        success "SSH config updated (port ${port} → ${host})"
    fi

    log_action "TRUST HOST: $app → ${host}:${port}"
}

_deploy_webhook() {
    local app="$1"
    local d; d=$(app_get "$app" domain)
    local t; t=$(app_get "$app" webhook_token)
    echo -e "\n${BOLD}Webhook for '${app}'${NC}"
    echo -e "  URL:   ${CYAN}https://${d}/cipi/webhook${NC}"
    echo -e "  Token: ${CYAN}${t}${NC}"
    echo ""

    local git_prov; git_prov=$(app_get "$app" git_provider)
    local git_whid; git_whid=$(app_get "$app" git_webhook_id)
    if [[ -n "$git_prov" && -n "$git_whid" ]]; then
        echo -e "  ${GREEN}✓ Auto-configured on ${git_prov} (ID: ${git_whid})${NC}"
    else
        echo "  GitHub: Repo → Settings → Webhooks → Add"
        echo "    Payload URL: https://${d}/cipi/webhook"
        echo "    Secret: ${t}"
        echo "    Events: Push only"
        echo ""
        echo "  GitLab: Repo → Settings → Webhooks"
        echo "    URL: https://${d}/cipi/webhook"
        echo "    Secret token: ${t}"
    fi
    echo ""
    echo "  Requires: composer require cipi/agent"
    echo ""
}
