#!/bin/bash
#############################################
# Cipi — SMTP / Email Notifications
# Optional: notify on errors (backup, deploy, cron, workers)
#############################################

[[ -z "${SMTP_CFG:-}" ]] && readonly SMTP_CFG="${CIPI_CONFIG}/smtp.json"
[[ -z "${SMTP_RC:-}" ]]  && readonly SMTP_RC="${CIPI_CONFIG}/.msmtprc"
[[ -z "${SMTP_PASS_EVAL:-}" ]] && readonly SMTP_PASS_EVAL="${CIPI_LIB:-/opt/cipi/lib}/cipi-smtp-pass.sh"

_smtp_is_enabled() {
    [[ -f "$SMTP_CFG" ]] && [[ "$(vault_read smtp.json | jq -r '.enabled // false')" == "true" ]]
}

_smtp_ensure_msmtp() {
    if ! command -v msmtp &>/dev/null; then
        step "Installing msmtp..."
        apt-get update -qq && apt-get install -y -qq msmtp msmtp-mta
        success "msmtp installed"
    fi
}

_smtp_trust_file() {
    local f
    for f in /etc/ssl/certs/ca-certificates.crt /etc/pki/tls/certs/ca-bundle.crt; do
        [[ -f "$f" && -r "$f" ]] && { printf '%s' "$f"; return 0; }
    done
    return 1
}

# Generate .msmtprc from smtp.json (called after configure / before send)
_smtp_write_rc() {
    [[ ! -f "$SMTP_CFG" ]] && return 1
    local _sj host port user from tls starttls trust_file log_file
    _sj=$(vault_read smtp.json) || return 1
    host=$(echo "$_sj" | jq -r '.host // ""')
    port=$(echo "$_sj" | jq -r '.port // 587')
    user=$(echo "$_sj" | jq -r '.user // ""')
    from=$(echo "$_sj" | jq -r '.from // ""')
    local tls_raw; tls_raw=$(echo "$_sj" | jq -r '.tls // true')
    [[ "$tls_raw" == "true" ]] && tls="on" || tls="off"

    # Implicit SSL on 465 (no STARTTLS). STARTTLS only when TLS is on and port != 465.
    if [[ "$tls" != "on" ]]; then
        starttls="off"
    elif [[ "$port" == "465" ]]; then
        starttls="off"
    else
        starttls="on"
    fi

    [[ -z "$host" || -z "$user" ]] && return 1
    [[ -x "$SMTP_PASS_EVAL" || -f "$SMTP_PASS_EVAL" ]] || return 1
    # Password must be present in vault (passwordeval reads it at send time)
    [[ -n "$(echo "$_sj" | jq -r '.password // empty')" ]] || return 1

    trust_file=""; trust_file=$(_smtp_trust_file) || trust_file=""
    log_file=""
    if mkdir -p "${CIPI_LOG:-/var/log/cipi}" 2>/dev/null; then
        log_file="${CIPI_LOG:-/var/log/cipi}/msmtp.log"
    fi

    # Build with printf/echo — never use an unquoted heredoc (passwords may contain $ ` \).
    # Use passwordeval so .msmtprc has no secrets (avoids msmtp permission refusals).
    umask 077
    {
        echo "defaults"
        echo "auth           on"
        echo "tls            ${tls}"
        [[ -n "$trust_file" ]] && echo "tls_trust_file ${trust_file}"
        [[ -n "$log_file" ]] && echo "logfile        ${log_file}"
        echo ""
        echo "account        cipi"
        echo "host           ${host}"
        echo "port           ${port}"
        echo "from           ${from}"
        echo "user           ${user}"
        echo "passwordeval   ${SMTP_PASS_EVAL}"
        echo "tls_starttls   ${starttls}"
        echo ""
        echo "account default : cipi"
    } > "$SMTP_RC"

    chown root:root "$SMTP_RC" 2>/dev/null || true
    chmod 600 "$SMTP_RC" || return 1
    chmod 700 "$SMTP_PASS_EVAL" 2>/dev/null || true
}

_smtp_can_reach() {
    local host="$1" port="$2"
    [[ -n "$host" && -n "$port" ]] || return 1
    if command -v timeout &>/dev/null; then
        timeout 5 bash -c "echo >/dev/tcp/${host}/${port}" 2>/dev/null && return 0
    else
        bash -c "echo >/dev/tcp/${host}/${port}" 2>/dev/null && return 0
    fi
    # Fallback: nc
    if command -v nc &>/dev/null; then
        nc -z -w 5 "$host" "$port" 2>/dev/null && return 0
    fi
    return 1
}

_smtp_configure() {
    _smtp_ensure_msmtp
    local host="" port="587" user="" pass="" from="" to="" tls="on" enabled="on"

    if [[ -f "$SMTP_CFG" ]]; then
        local _sj; _sj=$(vault_read smtp.json)
        host=$(echo "$_sj" | jq -r '.host // ""')
        port=$(echo "$_sj" | jq -r '.port // "587"')
        user=$(echo "$_sj" | jq -r '.user // ""')
        pass=$(echo "$_sj" | jq -r '.password // ""')
        from=$(echo "$_sj" | jq -r '.from // ""')
        to=$(echo "$_sj" | jq -r '.to // ""')
        [[ "$(echo "$_sj" | jq -r '.tls // true')" == "true" ]] && tls="on" || tls="off"
        [[ "$(echo "$_sj" | jq -r '.enabled // true')" == "true" ]] && enabled="on" || enabled="off"
    fi

    echo -e "\n${BOLD}SMTP — Email notifications${NC}"
    echo -e "${DIM}Configure SMTP, then use ${CYAN}cipi notifications${NC}${DIM} to choose which events send email.${NC}\n"

    read_input "SMTP host (e.g. smtp.gmail.com)" "$host" host
    read_input "SMTP port" "${port}" port
    read_input "SMTP user" "$user" user
    read_input "SMTP password" "$pass" pass
    read_input "From address (e.g. noreply@yourdomain.com)" "$from" from
    read_input "Notification recipient (To)" "$to" to
    echo -e "  ${DIM}Use TLS? (recommended for port 587)${NC}"
    read_input "TLS (on/off)" "$tls" tls
    echo -e "  ${DIM}Enable notifications?${NC}"
    read_input "Enabled (on/off)" "$enabled" enabled

    jq -n \
        --arg h "$host" --arg p "$port" --arg u "$user" --arg s "$pass" \
        --arg f "$from" --arg t "$to" --argjson tl "$([[ "$tls" == "on" ]] && echo true || echo false)" \
        --argjson e "$([[ "$enabled" == "on" ]] && echo true || echo false)" \
        '{host:$h,port:$p,user:$u,password:$s,from:$f,to:$t,tls:$tl,enabled:$e}' \
        | vault_write smtp.json

    _smtp_write_rc || { error "Failed to write msmtp config"; exit 1; }

    step "Testing SMTP..."
    if ! _smtp_can_reach "$host" "$port"; then
        warn "SMTP saved but ${host}:${port} is unreachable from this server."
        warn "Check outbound firewall / cloud security group (TCP ${port})."
        return 0
    fi

    local test_err="" test_rc=0
    set +e
    test_err=$(_smtp_send "Cipi SMTP test" "This is a test email from Cipi. Notifications are configured correctly." 2>&1)
    test_rc=$?
    set -e
    if [[ $test_rc -eq 0 ]]; then
        success "SMTP configured and test email sent to ${to}"
    else
        warn "SMTP saved but test send failed."
        [[ -n "$test_err" ]] && echo -e "${DIM}${test_err}${NC}"
        warn "Check credentials, From address, and TLS settings. Log: ${CIPI_LOG:-/var/log/cipi}/msmtp.log"
    fi
}

_smtp_send() {
    local subject="$1" body="$2"
    [[ ! -f "$SMTP_CFG" ]] && return 1
    _smtp_is_enabled || return 1
    local _sj; _sj=$(vault_read smtp.json) || return 1
    local to; to=$(echo "$_sj" | jq -r '.to // empty')
    [[ -z "$to" ]] && return 1
    _smtp_write_rc || return 1

    local from; from=$(echo "$_sj" | jq -r '.from // "noreply@localhost"')
    # Keep stderr visible to callers (configure/test); notification helpers may redirect.
    printf "From: %s\nTo: %s\nSubject: %s\nMIME-Version: 1.0\nContent-Type: text/plain; charset=UTF-8\n\n%b\n" \
        "$from" "$to" "$subject" "$body" | \
        msmtp -C "$SMTP_RC" -- "$to"
}

_smtp_disable() {
    if [[ -f "$SMTP_CFG" ]]; then
        vault_read smtp.json | jq '.enabled = false' | vault_write smtp.json
        success "Email notifications disabled"
    else
        warn "SMTP not configured. Run: cipi smtp configure"
    fi
}

_smtp_enable() {
    if [[ -f "$SMTP_CFG" ]]; then
        vault_read smtp.json | jq '.enabled = true' | vault_write smtp.json
        success "Email notifications enabled"
    else
        error "SMTP not configured. Run: cipi smtp configure"
        exit 1
    fi
}

_smtp_delete() {
    if confirm "Remove SMTP config and disable notifications?"; then
        rm -f "$SMTP_CFG" "$SMTP_RC"
        success "SMTP config removed"
    fi
}

_smtp_status() {
    echo -e "\n${BOLD}SMTP / Email Notifications${NC}"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    if [[ ! -f "$SMTP_CFG" ]]; then
        echo -e "  ${DIM}Not configured${NC}"
        echo -e "  Run ${CYAN}cipi smtp configure${NC} to enable email notifications"
        echo ""
        return
    fi

    local _sj; _sj=$(vault_read smtp.json)
    local enabled to host port tls
    enabled=$(echo "$_sj" | jq -r '.enabled // false')
    to=$(echo "$_sj" | jq -r '.to // ""')
    host=$(echo "$_sj" | jq -r '.host // ""')
    port=$(echo "$_sj" | jq -r '.port // ""')
    tls=$(echo "$_sj" | jq -r '.tls // true')

    if [[ "$enabled" == "true" ]]; then
        echo -e "  Status:    ${GREEN}enabled${NC}"
    else
        echo -e "  Status:    ${YELLOW}disabled${NC}"
    fi
    echo -e "  Recipient: ${CYAN}${to}${NC}"
    echo -e "  Host:      ${CYAN}${host}${NC}:${CYAN}${port}${NC}"
    echo -e "  TLS:       ${CYAN}${tls}${NC}"
    echo ""
}

_smtp_test() {
    if ! _smtp_is_enabled; then
        error "SMTP not configured or disabled. Run: cipi smtp configure"
        exit 1
    fi
    step "Sending test email..."
    local err="" rc=0
    set +e
    err=$(_smtp_send "Cipi test $(date '+%Y-%m-%d %H:%M')" "Test notification from Cipi." 2>&1)
    rc=$?
    set -e
    if [[ $rc -eq 0 ]]; then
        success "Test email sent"
    else
        error "Failed to send."
        [[ -n "$err" ]] && echo -e "${DIM}${err}${NC}"
        error "Check: cipi smtp status  and  ${CIPI_LOG:-/var/log/cipi}/msmtp.log"
        exit 1
    fi
}

# Called from common.sh and other scripts — sends notification if SMTP is configured
# Optional 3rd arg: trigger id (see: cipi notifications list)
cipi_notify() {
    local subject="$1" body="$2" trigger="${3:-}"
    [[ -z "$subject" || -z "$body" ]] && return 0
    local client_ip; client_ip=$(_get_client_ip 2>/dev/null || echo "n/a")
    local key_name; key_name=$(_get_session_key_name 2>/dev/null || echo "n/a")
    log_event "$subject"
    if [[ -n "$trigger" ]] && declare -f _notify_trigger_enabled &>/dev/null && ! _notify_trigger_enabled "$trigger"; then
        return 0
    fi
    body="${body}\n\n---\nPerformed by: ${client_ip}\nSSH Key: ${key_name}"
    _smtp_send "$subject" "$body" 2>/dev/null || true
}

smtp_command() {
    local sub="${1:-}"; shift || true
    case "$sub" in
        configure) _smtp_configure ;;
        disable)   _smtp_disable ;;
        enable)    _smtp_enable ;;
        delete)    _smtp_delete ;;
        status)    _smtp_status ;;
        test)      _smtp_test ;;
        *) error "Use: configure disable enable delete status test"; exit 1 ;;
    esac
}
