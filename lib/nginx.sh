#!/bin/bash
#############################################
# Cipi — Nginx helpers (mainline repo + HTTP/2 bomb mitigation)
#############################################

# Minimum nginx version that ships max_headers (HTTP/2 bomb fix).
readonly _NGINX_MIN_VERSION="1.29.8"

nginx_installed_version() {
    nginx -v 2>&1 | sed -n 's/.*nginx\///p' | awk '{print $1}'
}

nginx_version_at_least() {
    local ver="${1:-}" min="${2:-$_NGINX_MIN_VERSION}"
    [[ -z "$ver" ]] && return 1
    [[ "$(printf '%s\n' "$min" "$ver" | sort -V | head -1)" == "$min" ]]
}

nginx_setup_mainline_repo() {
    apt-get install -y -qq curl gnupg2 ca-certificates lsb-release ubuntu-keyring 2>/dev/null || true

    curl -fsSL https://nginx.org/keys/nginx_signing.key \
        | gpg --dearmor > /usr/share/keyrings/nginx-archive-keyring.gpg

    echo "deb [signed-by=/usr/share/keyrings/nginx-archive-keyring.gpg] \
https://nginx.org/packages/mainline/ubuntu $(lsb_release -cs) nginx" \
        > /etc/apt/sources.list.d/nginx.list

    echo -e "Package: *\nPin: origin nginx.org\nPin: release o=nginx\nPin-Priority: 900\n" \
        > /etc/apt/preferences.d/99nginx

    apt-get update -qq 2>/dev/null || true
}

nginx_ensure_sites_layout() {
    # nginx.org packages ship conf.d/ only; Cipi uses Debian-style sites-* dirs.
    mkdir -p /etc/nginx/sites-available /etc/nginx/sites-enabled /var/www/html
    rm -f /etc/nginx/conf.d/default.conf
}

nginx_write_global_conf() {
    local worker_processes="${1:-$(nproc)}"

    cat > /etc/nginx/nginx.conf <<NGINXEOF
user www-data;
worker_processes ${worker_processes};
pid /run/nginx.pid;

events {
    worker_connections 2048;
    multi_accept on;
}

http {
    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;
    server_names_hash_bucket_size 64;
    server_tokens off;
    max_headers 1000;

    include /etc/nginx/mime.types;
    default_type application/octet-stream;

    access_log /var/log/nginx/access.log;
    error_log /var/log/nginx/error.log;

    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript
               application/json application/javascript application/xml+rss
               application/rss+xml font/truetype font/opentype
               application/vnd.ms-fontobject image/svg+xml;

    client_max_body_size 256M;
    fastcgi_read_timeout 300;

    limit_req_zone \$binary_remote_addr zone=global:10m rate=30r/s;

    map \$http_upgrade \$connection_upgrade {
        default upgrade;
        ''      close;
    }

    include /etc/nginx/conf.d/*.conf;
    include /etc/nginx/sites-enabled/*;
}
NGINXEOF
}

# Upgrade to nginx.org mainline (>= 1.29.8) and apply max_headers.
# Idempotent — safe to re-run.
nginx_upgrade_mainline_for_http2_bomb() {
    local cur_ver workers candidate

    cur_ver=$(nginx_installed_version 2>/dev/null || echo "")
    if [[ -n "$cur_ver" ]] \
       && nginx_version_at_least "$cur_ver" \
       && grep -qE '^\s*max_headers\s' /etc/nginx/nginx.conf 2>/dev/null \
       && ! grep -q 'more_clear_headers' /etc/nginx/nginx.conf 2>/dev/null; then
        echo "  Nginx ${cur_ver} with max_headers already configured — nothing to do"
        return 0
    fi

    workers=$(grep -E '^worker_processes' /etc/nginx/nginx.conf 2>/dev/null \
        | awk '{print $2}' | tr -d ';')
    [[ -z "$workers" || "$workers" == "auto" ]] && workers=$(nproc)

    echo "  Configuring nginx.org mainline repository..."
    nginx_setup_mainline_repo

    candidate=$(apt-cache policy nginx 2>/dev/null | awk '/Candidate:/{print $2}')
    if [[ -z "$candidate" || "$candidate" == "(none)" ]]; then
        echo "  ERROR: nginx mainline package not available from APT."
        echo "  HTTP/2 bomb mitigation not applied — check nginx.org repo / network."
        return 1
    fi
    echo "  nginx candidate: ${candidate}"

    # Ubuntu's headers-more module is tied to distro nginx; drop before switching packages.
    apt-get purge -y -qq libnginx-mod-http-headers-more-filter 2>/dev/null || true

    export DEBIAN_FRONTEND=noninteractive
    if ! apt-get install -y -qq nginx 2>/dev/null; then
        echo "  ERROR: failed to install nginx from nginx.org mainline repository."
        return 1
    fi

    cur_ver=$(nginx_installed_version 2>/dev/null || echo "")
    if [[ -n "$cur_ver" ]] && ! nginx_version_at_least "$cur_ver"; then
        echo "  WARNING: installed nginx ${cur_ver} is below ${_NGINX_MIN_VERSION} (max_headers may be missing)."
    fi

    echo "  Rewriting /etc/nginx/nginx.conf (max_headers, drop headers-more module)..."
    nginx_ensure_sites_layout
    nginx_write_global_conf "$workers"

    if ! nginx -t 2>&1; then
        echo "  ERROR: nginx -t failed after upgrade — please review /etc/nginx/nginx.conf"
        return 1
    fi

    systemctl enable nginx 2>/dev/null || true
    systemctl reload nginx 2>/dev/null || systemctl restart nginx 2>/dev/null || true
    echo "  Nginx upgraded to $(nginx_installed_version 2>/dev/null || echo unknown)"
    return 0
}
