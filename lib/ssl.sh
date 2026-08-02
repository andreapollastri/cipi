#!/bin/bash
#############################################
# Cipi — SSL (Let's Encrypt)
#############################################

ssl_command() {
    local sub="${1:-}"; shift||true
    case "$sub" in
        install) _ssl_install "$@" ;;
        force)   _ssl_force "$@" ;;
        renew)   _ssl_renew ;;
        status)  _ssl_status ;;
        *) error "Use: install force renew status"; exit 1 ;;
    esac
}

_ssl_install() {
    local app="${1:-}"; [[ -z "$app" ]] && { error "Usage: cipi ssl install <app>"; exit 1; }
    app_exists "$app" || { error "App '$app' not found"; exit 1; }
    local d; d=$(app_get "$app" domain)
    [[ -z "$d" ]] && { error "No domain for app '$app'"; exit 1; }

    # Pre-flight: nginx vhost must exist
    if [[ ! -f "/etc/nginx/sites-available/${app}" ]]; then
        error "Nginx vhost for '${app}' not found. Create the app first."
        exit 1
    fi

    # Pre-flight: nginx must be serving the domain on port 80
    if ! nginx -t 2>/dev/null; then
        error "Nginx config test failed. Fix nginx errors before installing SSL."
        exit 1
    fi

    local domains="-d ${d}"
    local aliases
    # Exclude primary domain from aliases to avoid duplicates
    aliases=$(vault_read apps.json | jq -r --arg a "$app" --arg d "$d" '.[$a].aliases // [] | map(select(. != $d)) | .[]' 2>/dev/null || true)
    while read -r a; do
        [[ -n "$a" ]] && domains+=" -d ${a}"
    done <<< "${aliases:-}"

    echo ""
    step "Installing SSL for ${d}$([ -n "${aliases}" ] && echo " + aliases")..."
    echo ""

    if certbot --nginx $domains \
        --cert-name "${d}" \
        --expand \
        --non-interactive \
        --agree-tos \
        --register-unsafely-without-email \
        --redirect 2>&1; then

        # Force nginx test + reload after certbot modifies the vhost
        if nginx -t 2>&1; then
            systemctl reload nginx 2>/dev/null || true
        else
            error "Nginx config test failed after certbot modification. Check: nginx -t"
            exit 1
        fi

        sed -i "s|^APP_URL=http://|APP_URL=https://|" "/home/${app}/shared/.env" 2>/dev/null || true
        app_set "$app" force_https "true"
        log_action "SSL INSTALLED: $app"
        cipi_notify \
            "Cipi SSL installed: ${d} (${app}) on $(hostname)" \
            "An SSL certificate was installed.\n\nServer: $(hostname)\nApp: ${app}\nDomain: ${d}\nTime: $(date '+%Y-%m-%d %H:%M:%S %Z')" \
            ssl_install
        echo ""
        success "SSL installed for ${d}"
    else
        echo ""
        error "SSL failed. Check: DNS points to this server, port 80 is open, domain is correct."
        exit 1
    fi
}

# Re-apply HTTP → HTTPS redirect for an app that already has a certificate.
# Useful after vhost regeneration (alias/www/basicauth) when the Certbot
# redirect block was overwritten. No new ACME issuance — no rate-limit risk.
_ssl_force() {
    local app="${1:-}"; [[ -z "$app" ]] && { error "Usage: cipi ssl force <app>"; exit 1; }
    app_exists "$app" || { error "App '$app' not found"; exit 1; }
    local d; d=$(app_get "$app" domain)
    [[ -z "$d" ]] && { error "No domain for app '$app'"; exit 1; }

    if [[ ! -d "/etc/letsencrypt/live/${d}" ]]; then
        error "No SSL certificate for '${d}'. Run: cipi ssl install ${app}"
        exit 1
    fi
    if [[ ! -f "/etc/nginx/sites-available/${app}" ]]; then
        error "Nginx vhost for '${app}' not found."
        exit 1
    fi
    if ! command -v certbot &>/dev/null; then
        error "certbot not found"; exit 1
    fi

    step "Forcing HTTP → HTTPS redirect for ${d}..."
    if ! certbot install --nginx --cert-name "${d}" --non-interactive --redirect 2>&1; then
        error "Failed to apply HTTPS redirect. Check: nginx -t"
        exit 1
    fi
    if nginx -t 2>&1; then
        systemctl reload nginx 2>/dev/null || true
    else
        error "Nginx config test failed after redirect. Check: nginx -t"
        exit 1
    fi

    app_set "$app" force_https "true"
    log_action "SSL FORCE HTTPS: $app"
    cipi_notify \
        "Cipi SSL force HTTPS: ${d} (${app}) on $(hostname)" \
        "HTTP → HTTPS redirect was forced.\n\nServer: $(hostname)\nApp: ${app}\nDomain: ${d}\nTime: $(date '+%Y-%m-%d %H:%M:%S %Z')" \
        ssl_force
    success "HTTP → HTTPS redirect enabled for ${d}"
}

_ssl_renew() {
    step "Renewing certificates..."
    certbot renew --nginx --non-interactive 2>&1
    systemctl reload nginx 2>/dev/null
    log_action "SSL RENEWED"
    cipi_notify \
        "Cipi SSL renewed on $(hostname)" \
        "SSL certificates were renewed.\n\nServer: $(hostname)\nTime: $(date '+%Y-%m-%d %H:%M:%S %Z')" \
        ssl_renew
    success "Renewal complete"
}

_ssl_status() {
    echo -e "\n${BOLD}SSL Certificates${NC}"
    certbot certificates 2>/dev/null | while IFS= read -r line; do
        case "$line" in
            *"Certificate Name:"*) echo -e "\n  ${BOLD}${line##*: }${NC}" ;;
            *"Domains:"*)          echo -e "    Domains: ${CYAN}${line##*: }${NC}" ;;
            *"Expiry Date:"*)
                local exp; exp=$(echo "${line##*: }"|awk '{print $1}')
                local days; days=$(( ($(date -d "$exp" +%s) - $(date +%s)) / 86400 ))
                local c="${GREEN}"; [[ $days -lt 14 ]] && c="${RED}"; [[ $days -lt 30 && $days -ge 14 ]] && c="${YELLOW}"
                echo -e "    Expiry:  ${c}${exp} (${days}d)${NC}" ;;
        esac
    done; echo ""
}
