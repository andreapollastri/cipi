#!/bin/bash
#############################################
# Cipi — Vault (AES-256-CBC config encryption at rest)
#############################################

[[ -z "${VAULT_KEY:-}" ]]    && readonly VAULT_KEY="${CIPI_CONFIG}/.vault_key"
[[ -z "${VAULT_CIPHER:-}" ]] && readonly VAULT_CIPHER="aes-256-cbc"

# True when /etc/cipi accepts writes (false on remount-ro even if mode bits look writable).
_cipi_config_writable() {
    local probe="${CIPI_CONFIG}/.cipi-writable-$$"
    touch "$probe" 2>/dev/null || return 1
    rm -f "$probe" 2>/dev/null || true
    return 0
}

# For *mutating* ops only: if /etc/cipi is not writable, try remounting / rw
# (same recovery as migration 4.7.18). Read paths must keep using
# _cipi_config_writable alone — never call this from vault_init / source init.
_cipi_ensure_config_writable() {
    _cipi_config_writable && return 0
    mount -o remount,rw / 2>/dev/null || true
    _cipi_config_writable
}

# Best-effort chmod: no-op when /etc/cipi is read-only (never abort the caller).
_cipi_safe_chmod() {
    _cipi_config_writable || return 0
    chmod "$@" 2>/dev/null || true
}

vault_init() {
    [[ -f "$VAULT_KEY" ]] && return 0
    _cipi_config_writable || return 0
    openssl rand -base64 32 > "$VAULT_KEY" 2>/dev/null || return 0
    chmod 400 "$VAULT_KEY" 2>/dev/null || true
}

# Decrypt a config file and write JSON to stdout.
# Transparently handles both plaintext (legacy) and encrypted files.
vault_read() {
    local file="${CIPI_CONFIG}/$1"
    [[ ! -f "$file" ]] && { echo "{}"; return 0; }

    if jq empty "$file" 2>/dev/null; then
        cat "$file"
    else
        openssl enc -d -"${VAULT_CIPHER}" -pbkdf2 -pass "file:${VAULT_KEY}" -in "$file" 2>/dev/null \
            || { echo "vault: failed to decrypt $1" >&2; return 1; }
    fi
}

# Read JSON from stdin, encrypt, and write to a config file.
# Optional second arg overrides chmod (default 600).
vault_write() {
    local file="${CIPI_CONFIG}/$1"
    local perms="${2:-600}"
    if ! _cipi_ensure_config_writable; then
        echo "vault: cannot write $1 (read-only ${CIPI_CONFIG})" >&2
        return 1
    fi
    local tmp; tmp=$(mktemp)
    openssl enc -"${VAULT_CIPHER}" -salt -pbkdf2 -pass "file:${VAULT_KEY}" -out "$tmp"
    mv "$tmp" "$file"
    _cipi_safe_chmod "$perms" "$file"
}

# Encrypt an existing plaintext config file in-place.
# No-op if the file is already encrypted or missing.
vault_seal() {
    local file="${CIPI_CONFIG}/$1"
    [[ ! -f "$file" ]] && return 0
    jq empty "$file" 2>/dev/null || return 0

    local tmp; tmp=$(mktemp)
    openssl enc -"${VAULT_CIPHER}" -salt -pbkdf2 -pass "file:${VAULT_KEY}" -in "$file" -out "$tmp"
    mv "$tmp" "$file"
    _cipi_safe_chmod 600 "$file"
}
