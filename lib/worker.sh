#!/bin/bash
#############################################
# Cipi — Queue Workers (Supervisor)
#############################################

worker_command() {
    local sub="${1:-}"; shift||true
    case "$sub" in
        add)     _worker_add "$@" ;;
        list|ls) _worker_list "$@" ;;
        remove)  _worker_remove "$@" ;;
        stop)    _worker_stop "$@" ;;
        restart) _worker_restart "$@" ;;
        edit)    _worker_edit "$@" ;;
        horizon) _worker_horizon "$@" ;;
        *) error "Use: add list remove stop restart edit horizon"; exit 1 ;;
    esac
}

_worker_horizon() {
    local action="${1:-}"; shift || true
    case "$action" in
        enable)  _horizon_enable "$@" ;;
        disable) _horizon_disable "$@" ;;
        status)  _horizon_status "$@" ;;
        *) error "Usage: cipi worker horizon enable|disable|status <app>"; exit 1 ;;
    esac
}

_horizon_enable() {
    local app="${1:-}"
    [[ -z "$app" ]] && { error "Usage: cipi worker horizon enable <app>"; exit 1; }
    app_exists "$app" || { error "Not found"; exit 1; }
    [[ "$(app_get "$app" custom)" == "true" ]] && { error "Horizon is only for Laravel apps"; exit 1; }
    if [[ "$(app_get "$app" horizon)" == "true" ]]; then
        info "Horizon already enabled for '${app}'"
        return 0
    fi

    local php_ver conf
    php_ver=$(app_get "$app" php)
    conf="/etc/supervisor/conf.d/${app}.conf"
    [[ -f "$conf" ]] || echo "" > "$conf"

    step "Enabling Horizon for '${app}' (replaces queue:work workers)..."
    # Stop and remove all queue:work programs
    local prog
    for prog in $(grep '^\[program:' "$conf" 2>/dev/null | sed 's/\[program://;s/\]//' | grep -- "-worker-" || true); do
        supervisorctl stop "${prog}:"* 2>/dev/null || true
        _supervisor_remove_program "$app" "$prog"
    done
    _supervisor_remove_program "$app" "${app}-horizon"
    _create_supervisor_horizon "$app" "$php_ver"
    # Keep octane/reverb programs intact (they are separate)
    reload_supervisor
    supervisorctl start "${app}-horizon" 2>/dev/null || true
    app_set "$app" horizon "true"
    log_action "HORIZON ENABLE: $app"
    success "Horizon enabled for '${app}'"
    info "Use QUEUE_CONNECTION=redis (Valkey) in .env. Require laravel/horizon in the app."
}

_horizon_disable() {
    local app="${1:-}"
    [[ -z "$app" ]] && { error "Usage: cipi worker horizon disable <app>"; exit 1; }
    app_exists "$app" || { error "Not found"; exit 1; }
    if [[ "$(app_get "$app" horizon)" != "true" ]]; then
        info "Horizon already disabled for '${app}'"
        return 0
    fi

    local php_ver
    php_ver=$(app_get "$app" php)
    step "Disabling Horizon for '${app}'..."
    supervisorctl stop "${app}-horizon" 2>/dev/null || true
    _supervisor_remove_program "$app" "${app}-horizon"
    # Restore default queue:work worker
    if ! grep -q "\[program:${app}-worker-default\]" "/etc/supervisor/conf.d/${app}.conf" 2>/dev/null; then
        [[ -f "/etc/supervisor/conf.d/${app}.conf" ]] || echo "" > "/etc/supervisor/conf.d/${app}.conf"
        _create_supervisor_worker "$app" "$php_ver" "default"
    fi
    reload_supervisor
    app_unset "$app" horizon
    log_action "HORIZON DISABLE: $app"
    success "Horizon disabled — default queue worker restored"
}

_horizon_status() {
    local app="${1:-}"
    [[ -z "$app" ]] && { error "Usage: cipi worker horizon status <app>"; exit 1; }
    app_exists "$app" || { error "Not found"; exit 1; }
    if [[ "$(app_get "$app" horizon)" == "true" ]]; then
        echo -e "  Horizon: ${GREEN}enabled${NC}"
        supervisorctl status "${app}-horizon" 2>/dev/null || true
    else
        echo -e "  Horizon: ${DIM}disabled${NC}"
    fi
}

_worker_add() {
    local app="${1:-}"; shift||true
    [[ -z "$app" ]] && { error "Usage: cipi worker add <app> [--queue=Q] [--processes=N]"; exit 1; }
    app_exists "$app" || { error "Not found"; exit 1; }
    if [[ "$(app_get "$app" horizon)" == "true" ]]; then
        error "Horizon is enabled for '${app}'. Disable it first: cipi worker horizon disable ${app}"
        exit 1
    fi
    parse_args "$@"
    local queue="${ARG_queue:-}" procs="${ARG_processes:-1}" tries="${ARG_tries:-3}" timeout="${ARG_timeout:-3600}"
    [[ -z "$queue" ]] && read_input "Queue name" "" queue
    [[ -z "$queue" ]] && { error "Queue name required"; exit 1; }
    grep -q "\[program:${app}-worker-${queue}\]" "/etc/supervisor/conf.d/${app}.conf" 2>/dev/null && \
        { error "Worker '${queue}' already exists"; exit 1; }
    local v; v=$(app_get "$app" php)
    _create_supervisor_worker "$app" "$v" "$queue" "$procs" "$tries" "$timeout"
    reload_supervisor
    supervisorctl start "${app}-worker-${queue}:*" 2>/dev/null
    log_action "WORKER ADD: $app queue=$queue procs=$procs"
    cipi_notify \
        "Cipi worker added: ${app} (${queue}) on $(hostname)" \
        "A queue worker was added.\n\nServer: $(hostname)\nApp: ${app}\nQueue: ${queue}\nProcesses: ${procs}\nTime: $(date '+%Y-%m-%d %H:%M:%S %Z')" \
        worker_add
    success "Worker '${queue}' added (${procs} processes)"
}

_worker_list() {
    local app="${1:-}"; [[ -z "$app" ]] && { error "Usage: cipi worker list <app>"; exit 1; }
    app_exists "$app" || { error "Not found"; exit 1; }
    local conf="/etc/supervisor/conf.d/${app}.conf"
    [[ ! -f "$conf" ]] && { info "No workers"; return; }
    echo -e "\n${BOLD}Workers for '${app}'${NC}"
    if [[ "$(app_get "$app" horizon)" == "true" ]]; then
        echo -e "  ${CYAN}Mode: Horizon${NC}"
    fi
    printf "  ${BOLD}%-28s %-10s %-6s %s${NC}\n" "PROGRAM" "QUEUE" "PROCS" "STATUS"
    grep "^\[program:" "$conf" | sed 's/\[program://;s/\]//' | while read -r prog; do
        local q np st c
        if [[ "$prog" == "${app}-horizon" ]]; then
            q="horizon"
        elif [[ "$prog" == "${app}-octane" ]]; then
            q="octane"
        elif [[ "$prog" == "${app}-reverb" ]]; then
            q="reverb"
        else
            q=$(echo "$prog"|sed "s/${app}-worker-//")
        fi
        np=$(grep -A20 "\[program:${prog}\]" "$conf"|grep numprocs|head -1|cut -d= -f2)
        st=$(supervisorctl status "${prog}:*" 2>/dev/null|head -1|awk '{print $2}'||echo "?")
        [[ -z "$st" || "$st" == "?" ]] && st=$(supervisorctl status "${prog}" 2>/dev/null|awk '{print $2}'||echo "?")
        c="${GREEN}"; [[ "$st" != "RUNNING" ]] && c="${RED}"
        printf "  %-28s %-10s %-6s ${c}%s${NC}\n" "$prog" "$q" "${np:-1}" "$st"
    done; echo ""
}

_worker_remove() {
    local app="${1:-}"; shift||true
    [[ -z "$app" ]] && { error "Usage: cipi worker remove <app> <queue>"; exit 1; }
    app_exists "$app" || { error "Not found"; exit 1; }
    if [[ "$(app_get "$app" horizon)" == "true" ]]; then
        error "Horizon is enabled. Use: cipi worker horizon disable ${app}"
        exit 1
    fi
    parse_args "$@"
    local queue="${ARG_queue:-${1:-}}"
    [[ -z "$queue" ]] && { error "Queue name required"; exit 1; }
    [[ "$queue" == "default" ]] && confirm "Remove default worker?" || { [[ "$queue" == "default" ]] && return; }
    supervisorctl stop "${app}-worker-${queue}:*" 2>/dev/null||true
    _supervisor_remove_program "$app" "${app}-worker-${queue}"
    reload_supervisor
    log_action "WORKER REMOVE: $app queue=$queue"
    cipi_notify \
        "Cipi worker removed: ${app} (${queue}) on $(hostname)" \
        "A queue worker was removed.\n\nServer: $(hostname)\nApp: ${app}\nQueue: ${queue}\nTime: $(date '+%Y-%m-%d %H:%M:%S %Z')" \
        worker_remove
    success "Worker '${queue}' removed"
}

_worker_stop() {
    local app="${1:-}"; [[ -z "$app" ]] && { error "Usage: cipi worker stop <app>"; exit 1; }
    app_exists "$app" || { error "Not found"; exit 1; }
    if [[ "$(app_get "$app" horizon)" == "true" ]]; then
        supervisorctl stop "${app}-horizon" 2>/dev/null||true
    else
        supervisorctl stop "${app}-worker-*" 2>/dev/null||true
    fi
    success "Workers stopped for '${app}'"
}

_worker_restart() {
    local app="${1:-}"; [[ -z "$app" ]] && { error "Usage: cipi worker restart <app>"; exit 1; }
    app_exists "$app" || { error "Not found"; exit 1; }
    if [[ "$(app_get "$app" horizon)" == "true" ]]; then
        supervisorctl restart "${app}-horizon" 2>/dev/null||true
    else
        supervisorctl restart "${app}-worker-*" 2>/dev/null||true
    fi
    success "Workers restarted for '${app}'"
}

_worker_edit() {
    local app="${1:-}"; shift||true
    [[ -z "$app" ]] && { error "Usage: cipi worker edit <app> --queue=Q --processes=N"; exit 1; }
    app_exists "$app" || { error "Not found"; exit 1; }
    parse_args "$@"
    local queue="${ARG_queue:-}" procs="${ARG_processes:-}"
    [[ -z "$queue" ]] && { error "Need --queue"; exit 1; }
    local conf="/etc/supervisor/conf.d/${app}.conf"
    grep -q "\[program:${app}-worker-${queue}\]" "$conf" 2>/dev/null || { error "Worker '${queue}' not found"; exit 1; }
    if [[ -n "$procs" ]]; then
        local tmp; tmp=$(mktemp)
        awk -v p="[program:${app}-worker-${queue}]" -v n="numprocs=${procs}" \
            '$0==p{b=1}/^\[program:/&&$0!=p{b=0} b&&/^numprocs=/{$0=n}{print}' "$conf">"$tmp"
        mv "$tmp" "$conf"; reload_supervisor
        supervisorctl restart "${app}-worker-${queue}:*" 2>/dev/null
        success "Worker '${queue}' → ${procs} processes"
    fi
}
