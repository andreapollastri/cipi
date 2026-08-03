#!/bin/bash
#############################################
# Cipi — HTTP healthchecks + alerts
#############################################

readonly HEALTH_STATE_DIR="${CIPI_LOG}/health"
readonly HEALTH_CRON="/etc/cron.d/cipi-health"
readonly HEALTH_HELPER="/usr/local/bin/cipi-health-check"

health_command() {
    local sub="${1:-}"; shift || true
    case "$sub" in
        set)   _health_set "$@" ;;
        unset) _health_unset "$@" ;;
        check) _health_check_one "$@" ;;
        list)  _health_list ;;
        *) error "Usage: cipi health set|unset|check|list <app> [--url=] [--expect=200]"; exit 1 ;;
    esac
}

_health_ensure_cron() {
    mkdir -p "$HEALTH_STATE_DIR"
    if [[ ! -x "$HEALTH_HELPER" && -f "${CIPI_LIB}/cipi-health-check.sh" ]]; then
        cp "${CIPI_LIB}/cipi-health-check.sh" "$HEALTH_HELPER"
        chmod 755 "$HEALTH_HELPER"
    fi
    if [[ ! -f "$HEALTH_CRON" ]]; then
        cat > "$HEALTH_CRON" <<EOF
# Cipi app HTTP healthchecks (every 5 minutes)
*/5 * * * * root ${HEALTH_HELPER} >/dev/null 2>&1
EOF
        chmod 644 "$HEALTH_CRON"
    fi
}

_health_set() {
    local app="${1:-}"; shift || true
    [[ -z "$app" ]] && { error "Usage: cipi health set <app> [--url=https://domain/up] [--expect=200]"; exit 1; }
    app_exists "$app" || { error "App '$app' not found"; exit 1; }
    parse_args "$@"
    local url="${ARG_url:-}"
    local expect="${ARG_expect:-200}"
    if [[ -z "$url" ]]; then
        local domain; domain=$(app_get "$app" domain)
        url="https://${domain}/up"
    fi
    [[ "$expect" =~ ^[0-9]+$ ]] || { error "--expect must be an HTTP status code"; exit 1; }
    [[ "$url" =~ ^https?:// ]] || { error "--url must start with http:// or https://"; exit 1; }

    app_set "$app" health_url "$url"
    app_set "$app" health_expect "$expect"
    _health_ensure_cron
    log_action "HEALTH SET: $app url=$url expect=$expect"
    success "Healthcheck set for '${app}': ${url} (expect ${expect})"
}

_health_unset() {
    local app="${1:-}"
    [[ -z "$app" ]] && { error "Usage: cipi health unset <app>"; exit 1; }
    app_exists "$app" || { error "App '$app' not found"; exit 1; }
    app_unset "$app" health_url
    app_unset "$app" health_expect
    rm -f "${HEALTH_STATE_DIR}/${app}.state" "${HEALTH_STATE_DIR}/${app}.failcount"
    log_action "HEALTH UNSET: $app"
    success "Healthcheck removed for '${app}'"
}

_health_check_one() {
    local app="${1:-}"
    [[ -z "$app" ]] && { error "Usage: cipi health check <app>"; exit 1; }
    app_exists "$app" || { error "App '$app' not found"; exit 1; }
    local url expect
    url=$(app_get "$app" health_url)
    expect=$(app_get "$app" health_expect)
    [[ -z "$url" ]] && { error "No healthcheck configured. Run: cipi health set ${app}"; exit 1; }
    [[ -z "$expect" ]] && expect=200

    local code
    code=$(curl -sS -o /dev/null -w '%{http_code}' --max-time 15 -L "$url" 2>/dev/null || echo "000")
    if [[ "$code" == "$expect" ]]; then
        success "OK ${code} ← ${url}"
        return 0
    fi
    error "FAIL got ${code}, expected ${expect} ← ${url}"
    return 1
}

_health_list() {
    echo -e "\n${BOLD}Healthchecks${NC}"
    local apps json
    json=$(vault_read apps.json)
    echo "$json" | jq -r 'to_entries[] | select(.value.health_url != null and .value.health_url != "") | "\(.key)\t\(.value.health_url)\t\(.value.health_expect // "200")"' \
        | while IFS=$'\t' read -r app url expect; do
            printf "  %-16s %-50s expect=%s\n" "$app" "$url" "$expect"
        done
    echo ""
}
