#!/bin/bash
#############################################
# Cipi — Backup (S3 / S3-compatible)
#############################################

backup_command() {
    local sub="${1:-}"; shift||true
    case "$sub" in
        configure) _bk_configure ;;
        run)       _bk_run "$@" ;;
        list)      _bk_list "$@" ;;
        prune)     _bk_prune "$@" ;;
        *) error "Use: configure run list prune"; exit 1 ;;
    esac
}

# Root crontab: daily backup + prune for all apps (no per-app argument).
# Skips if any `cipi backup run` line already exists (e.g. manual or migration).
_ensure_backup_cron_root() {
    [[ $EUID -ne 0 ]] && return 0
    # Do not append if either job already exists (avoids duplicate prune when only prune was scheduled).
    if crontab -l 2>/dev/null | grep -qF 'cipi backup run'; then
        return 0
    fi
    if crontab -l 2>/dev/null | grep -qF 'cipi backup prune'; then
        return 0
    fi
    (
        crontab -l 2>/dev/null || true
        cat <<'BKCRON'
# === CIPI BACKUP (all apps) ===
0 2 * * * /usr/local/bin/cipi backup run >> /var/log/cipi/backup.log 2>&1
0 3 * * * /usr/local/bin/cipi backup prune --weeks=4 >> /var/log/cipi/backup-prune.log 2>&1
BKCRON
    ) | crontab -
}

_ensure_awscli() {
    if ! command -v aws &>/dev/null; then
        step "Installing AWS CLI v2..."
        local tmp; tmp=$(mktemp -d)
        curl -fsSL "https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" -o "${tmp}/awscliv2.zip"
        unzip -q "${tmp}/awscliv2.zip" -d "${tmp}"
        "${tmp}/aws/install" --update -i /usr/local/aws-cli -b /usr/local/bin &>/dev/null
        rm -rf "${tmp}"
        command -v aws &>/dev/null || { error "AWS CLI install failed"; exit 1; }
        success "AWS CLI $(aws --version 2>&1 | awk '{print $1}')"
    fi
}

# Disk-backed temp dir for archives (default /var/tmp). /tmp is often tmpfs and too small for large apps.
# Override: CIPI_BACKUP_TMPDIR env, or backup.json "tmpdir".
_bk_tmp_base() {
    local dir=""
    if [[ -n "${CIPI_BACKUP_TMPDIR:-}" ]]; then
        dir="$CIPI_BACKUP_TMPDIR"
    elif [[ -f "${CIPI_CONFIG}/backup.json" ]]; then
        dir=$(vault_read backup.json | jq -r '.tmpdir // ""')
    fi
    dir="${dir:-/var/tmp}"
    if [[ ! -d "$dir" ]] || [[ ! -w "$dir" ]]; then
        error "Backup temp directory not writable: ${dir}"
        exit 1
    fi
    echo "$dir"
}

# Wrapper: adds --endpoint-url and --region when a custom endpoint is configured.
# S3-compatible APIs can fail with "NoneType is not iterable" if region is empty.
_aws_s3() {
    local ep="" region=""
    if [[ -f "${CIPI_CONFIG}/backup.json" ]]; then
        local _bkj; _bkj=$(vault_read backup.json)
        ep=$(echo "$_bkj" | jq -r '.endpoint_url // ""')
        region=$(echo "$_bkj" | jq -r '.region // "eu-central-1"')
    fi
    if [[ -n "$ep" ]]; then
        aws s3 --endpoint-url "$ep" --region "${region:-eu-central-1}" "$@"
    else
        aws s3 "$@"
    fi
}

_bk_configure() {
    _ensure_awscli
    local cf="${CIPI_CONFIG}/backup.json"
    local ck="" cs="" cb="" cr="" ce="" ct="/var/tmp"
    if [[ -f "$cf" ]]; then
        local _bkj; _bkj=$(vault_read backup.json)
        ck=$(echo "$_bkj" | jq -r '.aws_key    // ""')
        cs=$(echo "$_bkj" | jq -r '.aws_secret // ""')
        cb=$(echo "$_bkj" | jq -r '.bucket     // ""')
        cr=$(echo "$_bkj" | jq -r '.region     // ""')
        ce=$(echo "$_bkj" | jq -r '.endpoint_url // ""')
        ct=$(echo "$_bkj" | jq -r '.tmpdir // "/var/tmp"')
    fi

    read_input "Access Key ID" "$ck" ck
    read_input "Secret Access Key" "$cs" cs
    read_input "Bucket name" "$cb" cb
    read_input "Region" "${cr:-eu-central-1}" cr
    echo -e "  ${DIM}Leave empty for AWS S3. For other providers set the endpoint URL.${NC}"
    echo -e "  ${DIM}Examples:${NC}"
    echo -e "  ${DIM}  Hetzner:    https://<datacenter>.your-objectstorage.com${NC}"
    echo -e "  ${DIM}  DO Spaces:  https://<region>.digitaloceanspaces.com${NC}"
    echo -e "  ${DIM}  Backblaze:  https://s3.<region>.backblazeb2.com${NC}"
    echo -e "  ${DIM}  MinIO:      https://your-minio-host${NC}"
    read_input "Endpoint URL (optional)" "$ce" ce
    echo -e "  ${DIM}Temp directory for backup archives (must have enough disk space; /tmp is often RAM-backed).${NC}"
    read_input "Temp directory" "$ct" ct

    # Region must not be empty — AWS CLI fails with "NoneType is not iterable" when region is blank
    cr="${cr:-eu-central-1}"
    ct="${ct:-/var/tmp}"

    jq -n \
        --arg k "$ck" --arg s "$cs" --arg b "$cb" \
        --arg r "$cr" --arg e "$ce" --arg t "$ct" \
        '{"aws_key":$k,"aws_secret":$s,"bucket":$b,"region":$r,"endpoint_url":$e,"tmpdir":$t}' \
        | vault_write backup.json

    mkdir -p /root/.aws
    cat > /root/.aws/credentials <<AWSCREDS
[default]
aws_access_key_id = ${ck}
aws_secret_access_key = ${cs}
AWSCREDS
    cat > /root/.aws/config <<AWSCFG
[default]
region = ${cr}
output = json
AWSCFG
    chmod 600 /root/.aws/credentials /root/.aws/config

    step "Testing S3 connectivity..."
    local test_err
    if ! test_err=$(_aws_s3 ls "s3://${cb}" 2>&1); then
        error "S3 connection failed:"
        echo "$test_err" | sed 's/^/  /'
        exit 1
    fi
    success "Backup configured (S3 connection OK)"
    _ensure_backup_cron_root
}

_bk_run() {
    _ensure_awscli
    local target="${1:-}" cf="${CIPI_CONFIG}/backup.json"
    [[ ! -f "$cf" ]] && { error "Run: cipi backup configure"; exit 1; }
    local bucket; bucket=$(vault_read backup.json | jq -r '.bucket')
    # shellcheck source=/dev/null
    source "${CIPI_LIB}/db.sh"
    local ts; ts=$(date +%Y-%m-%d_%H%M%S)
    local tmp="$(_bk_tmp_base)/cipi-bk-${ts}"; mkdir -p "$tmp"
    local backup_errors=""

    _do_backup() {
        local app="$1"; local d="${tmp}/${app}"; mkdir -p "$d"
        local ok=true
        step "Backup '${app}'..."

        local eng; eng=$(app_get "$app" engine 2>/dev/null || true)
        [[ -z "$eng" ]] && eng="mariadb"
        eng=$(db_normalize_engine "$eng" 2>/dev/null || echo "mariadb")
        if [[ "$(app_get "$app" custom)" == "true" ]]; then
            : # no database
        elif db_engine_is_installed "$eng"; then
            if ! db_dump_database "$eng" "$app" "${d}/db.sql.gz" 2>"${d}/db.err"; then
                error "  DB dump failed:"; sed 's/^/    /' "${d}/db.err" 2>/dev/null; ok=false
            fi
            rm -f "${d}/db.err"
            echo "$eng" > "${d}/db.engine"
        else
            error "  DB engine '${eng}' not installed"; ok=false
        fi

        local tar_err
        tar_err=$(tar -czf "${d}/shared.tar.gz" -C "/home/${app}" shared/ 2>&1) || {
            error "  Files archive failed: ${tar_err}"; ok=false
        }

        local s3_err
        if ! s3_err=$(_aws_s3 cp "${d}/" "s3://${bucket}/cipi/${app}/${ts}/" --recursive 2>&1); then
            error "  S3 upload failed:"
            echo "$s3_err" | sed 's/^/    /'
            ok=false
        else
            success "  → s3://${bucket}/cipi/${app}/${ts}/"
        fi

        if [[ "$ok" == false ]]; then
            warn "  Backup '${app}' completed with errors"
            backup_errors="${backup_errors}${backup_errors:+, }${app}"
        fi
    }

    if [[ -n "$target" ]]; then
        app_exists "$target" || { error "Not found"; exit 1; }
        _do_backup "$target"
    else
        while IFS= read -r a; do
            [[ -n "$a" ]] && _do_backup "$a"
        done < <(vault_read apps.json | jq -r 'keys[]' 2>/dev/null)
    fi
    rm -rf "$tmp"

    if [[ -n "$backup_errors" ]]; then
        cipi_notify \
            "Cipi backup failed: ${backup_errors}" \
            "Backup completed with errors for: ${backup_errors}. Check logs: /var/log/cipi/cipi.log" \
            backup_fail
    fi
    success "Backup complete"
}

_bk_prune() {
    local target="" weeks=""

    # Parse arguments: [app] --weeks=N (in any order)
    for arg in "$@"; do
        case "$arg" in
            --weeks=*) weeks="${arg#--weeks=}" ;;
            --*)       error "Unknown flag: ${arg}"; exit 1 ;;
            *)         target="$arg" ;;
        esac
    done

    [[ -z "$weeks" ]]           && { error "--weeks=<N> is required  (e.g. cipi backup prune --weeks=4)"; exit 1; }
    [[ ! "$weeks" =~ ^[0-9]+$ ]] && { error "--weeks must be a positive integer"; exit 1; }
    [[ "$weeks" -eq 0 ]]        && { error "--weeks must be greater than 0"; exit 1; }

    local cf="${CIPI_CONFIG}/backup.json"
    local cutoff; cutoff=$(date -d "${weeks} weeks ago" +%s 2>/dev/null \
                           || date -v "-${weeks}w"    +%s)

    _prune_local() {
        local app="$1"
        local pattern="/var/log/cipi/backups/${app}_*.sql.gz"
        local found=0
        for f in $pattern; do
            [[ -f "$f" ]] || continue
            local fname; fname=$(basename "$f")
            # filename format: <app>_YYYYMMDD_HHMMSS.sql.gz
            local date_str; date_str=$(echo "$fname" | grep -oP '\d{8}' | head -1)
            [[ -z "$date_str" ]] && continue
            local file_ts; file_ts=$(date -d "${date_str}" +%s 2>/dev/null \
                                     || date -j -f "%Y%m%d" "${date_str}" +%s 2>/dev/null) || continue
            if [[ "$file_ts" -lt "$cutoff" ]]; then
                rm -f "$f"
                step "  Deleted local: $(basename "$f")"
                found=1
            fi
        done
        [[ $found -eq 0 ]] && info "  No local backups older than ${weeks} week(s) for '${app}'"
    }

    _prune_s3() {
        local app="$1"
        [[ ! -f "$cf" ]] && { warn "S3 not configured — skipping S3 prune for '${app}'"; return; }
        _ensure_awscli
        local bucket; bucket=$(vault_read backup.json | jq -r '.bucket')
        local prefix="cipi/${app}/"
        local found=0
        while IFS= read -r folder; do
            [[ -z "$folder" ]] && continue
            local date_str; date_str=$(echo "$folder" | grep -oP '^\d{4}-\d{2}-\d{2}')
            [[ -z "$date_str" ]] && continue
            local folder_ts; folder_ts=$(date -d "${date_str}" +%s 2>/dev/null \
                                         || date -j -f "%Y-%m-%d" "${date_str}" +%s 2>/dev/null) || continue
            if [[ "$folder_ts" -lt "$cutoff" ]]; then
                local s3_err
                if s3_err=$(_aws_s3 rm "s3://${bucket}/${prefix}${folder}" --recursive 2>&1); then
                    step "  Deleted S3:   s3://${bucket}/${prefix}${folder}"
                    found=1
                else
                    error "  S3 delete failed for ${folder}:"; echo "$s3_err" | sed 's/^/    /'
                fi
            fi
        done < <(_aws_s3 ls "s3://${bucket}/${prefix}" 2>/dev/null | awk '{print $NF}')
        [[ $found -eq 0 ]] && info "  No S3 backups older than ${weeks} week(s) for '${app}'"
    }

    _prune_app() {
        local app="$1"
        echo -e "\n${BOLD}Pruning '${app}'${NC} ${DIM}(older than ${weeks} week(s))${NC}"
        _prune_local "$app"
        _prune_s3    "$app"
    }

    if [[ -n "$target" ]]; then
        app_exists "$target" || { error "App not found: ${target}"; exit 1; }
        _prune_app "$target"
    else
        local apps
        apps=$(vault_read apps.json | jq -r 'keys[]' 2>/dev/null) || true
        [[ -z "$apps" ]] && { warn "No apps found"; exit 0; }
        while IFS= read -r a; do _prune_app "$a"; done <<< "$apps"
    fi

    echo ""
    success "Prune complete"
    log_action "backup prune${target:+ $target} --weeks=${weeks}"
}

_bk_list() {
    _ensure_awscli
    local target="${1:-}" cf="${CIPI_CONFIG}/backup.json"
    [[ ! -f "$cf" ]] && { error "Run: cipi backup configure"; exit 1; }
    local bucket; bucket=$(vault_read backup.json | jq -r '.bucket')
    echo -e "\n${BOLD}Backups${NC}"
    local ls_err
    if [[ -n "$target" ]]; then
        ls_err=$(_aws_s3 ls "s3://${bucket}/cipi/${target}/" 2>&1) || { error "$ls_err"; exit 1; }
        echo "$ls_err" | sed 's/^/  /'
    else
        ls_err=$(_aws_s3 ls "s3://${bucket}/cipi/" 2>&1) || { error "$ls_err"; exit 1; }
        echo "$ls_err" | sed 's/^/  /'
    fi
    echo ""
}
