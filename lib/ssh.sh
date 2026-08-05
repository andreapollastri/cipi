#!/bin/bash
#############################################
# Cipi — SSH Key Management
#############################################

AUTHORIZED_KEYS="/home/cipi/.ssh/authorized_keys"

ssh_command() {
    local sub="${1:-}"; shift||true
    case "$sub" in
        list)   _ssh_list "$@" ;;
        add)    _ssh_add "$@" ;;
        remove) _ssh_remove "$@" ;;
        rename) _ssh_rename "$@" ;;
        *)      error "Use: list add remove rename"; exit 1 ;;
    esac
}

# ── HELPERS ──────────────────────────────────────────────────

# Get the fingerprint of the SSH key used for the current session.
# Requires ExposeAuthInfo=yes in sshd_config and SSH_USER_AUTH env preserved via sudoers.
# SSH_USER_AUTH format: publickey <key_type> <raw_key_data> — field 3 is raw key, not fingerprint
_get_session_fingerprint() {
    local auth_file="${SSH_USER_AUTH:-}"
    [[ -z "$auth_file" || ! -f "$auth_file" ]] && return

    local key_type key_data fp
    key_type=$(awk '/^publickey / {print $2; exit}' "$auth_file" 2>/dev/null)
    key_data=$(awk '/^publickey / {print $3; exit}' "$auth_file" 2>/dev/null)
    if [[ -n "$key_type" && -n "$key_data" ]]; then
        fp=$(echo "$key_type $key_data" | ssh-keygen -lf - 2>/dev/null | awk '{print $2}')
        [[ -n "$fp" ]] && echo "$fp"
    fi
}

# ── LIST ─────────────────────────────────────────────────────

_ssh_list() {
    parse_args "$@"
    local session_fp
    session_fp=$(_get_session_fingerprint)

    if [[ "${ARG_json:-}" == "true" ]]; then
        local items="[]"
        local i=0
        if [[ -f "$AUTHORIZED_KEYS" ]] && [[ -s "$AUTHORIZED_KEYS" ]]; then
            while IFS= read -r line; do
                [[ -z "$line" || "$line" == \#* ]] && continue
                (( i++ )) || true
                local key_type comment fingerprint current=false
                key_type=$(echo "$line" | awk '{print $1}')
                comment=$(echo "$line" | awk '{$1=$2=""; print}' | xargs)
                [[ -z "$comment" ]] && comment="(no comment)"
                fingerprint=$(echo "$line" | ssh-keygen -lf - 2>/dev/null | awk '{print $2}') || fingerprint="?"
                [[ -n "$session_fp" && "$fingerprint" == "$session_fp" ]] && current=true
                items=$(echo "$items" | jq -c --argjson id "$i" --arg t "$key_type" --arg c "$comment" --arg f "$fingerprint" --argjson cur "$current" \
                    '. + [{id:$id, type:$t, comment:$c, fingerprint:$f, current_session:$cur}]')
            done < "$AUTHORIZED_KEYS"
        fi
        jq -n --argjson keys "$items" '{keys: $keys}'
        return 0
    fi

    if [[ ! -f "$AUTHORIZED_KEYS" ]] || [[ ! -s "$AUTHORIZED_KEYS" ]]; then
        warn "No SSH keys configured for cipi user"
        exit 0
    fi

    echo ""
    echo -e "  ${BOLD}SSH Keys (cipi user)${NC}"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""

    local i=0
    while IFS= read -r line; do
        [[ -z "$line" || "$line" == \#* ]] && continue
        (( i++ )) || true

        # Extract key type, fingerprint and comment
        local key_type comment fingerprint
        key_type=$(echo "$line" | awk '{print $1}')
        comment=$(echo "$line" | awk '{$1=$2=""; print}' | xargs)
        [[ -z "$comment" ]] && comment="(no comment)"

        fingerprint=$(echo "$line" | ssh-keygen -lf - 2>/dev/null | awk '{print $2}') || fingerprint="?"

        local active_marker=""
        if [[ -n "$session_fp" && "$fingerprint" == "$session_fp" ]]; then
            active_marker=" ${GREEN}<< current session${NC}"
        fi

        echo -e "  ${CYAN}${i}${NC}  ${BOLD}${comment}${NC}${active_marker}"
        echo -e "     ${DIM}${key_type} · ${fingerprint}${NC}"
        echo ""
    done < "$AUTHORIZED_KEYS"

    if [[ $i -eq 0 ]]; then
        warn "No SSH keys configured for cipi user"
    else
        echo -e "  ${DIM}Total: ${i} key(s)${NC}"
    fi
    echo ""
}

# ── ADD ──────────────────────────────────────────────────────

_ssh_add() {
    local key="${*}"

    if [[ -z "$key" ]]; then
        echo ""
        echo -e "  ${BOLD}Add SSH Key${NC}"
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
        echo ""
        echo -e "  ${DIM}Generate a key on your machine:${NC}"
        echo -e "  ${CYAN}ssh-keygen -t ed25519 -C \"your@email.com\"${NC}"
        echo -e "  ${CYAN}cat ~/.ssh/id_ed25519.pub${NC}"
        echo ""
        echo -en "  ${BOLD}Paste the public key:${NC} "
        read -r key
    fi

    if [[ -z "$key" ]]; then
        error "No key provided"
        exit 1
    fi

    # Validate format
    if ! echo "$key" | grep -qE '^(ssh-(rsa|ed25519)|ecdsa-sha2-\S+) '; then
        error "Invalid key format. Must start with ssh-rsa, ssh-ed25519, or ecdsa-sha2-*"
        exit 1
    fi

    # Check for duplicates
    if grep -qF "$key" "$AUTHORIZED_KEYS" 2>/dev/null; then
        warn "Key already exists"
        exit 0
    fi

    # Append
    echo "$key" >> "$AUTHORIZED_KEYS"
    chown cipi:cipi "$AUTHORIZED_KEYS"
    chmod 600 "$AUTHORIZED_KEYS"

    local comment
    comment=$(echo "$key" | awk '{$1=$2=""; print}' | xargs)
    [[ -z "$comment" ]] && comment="(no comment)"

    local fingerprint
    fingerprint=$(echo "$key" | ssh-keygen -lf - 2>/dev/null | awk '{print $2}') || fingerprint="?"

    success "Key added: ${comment}"
    log_action "SSH KEY ADD: ${comment}"

    # Email notification
    local server_ip; server_ip=$(curl -s --max-time 3 https://checkip.amazonaws.com 2>/dev/null || hostname)
    cipi_notify \
        "Cipi SSH key added on $(hostname)" \
        "An SSH key was added to the cipi user.\n\nServer: $(hostname) (${server_ip})\nComment: ${comment}\nFingerprint: ${fingerprint}\nTime: $(date '+%Y-%m-%d %H:%M:%S %Z')" \
        ssh_key_add
}

# ── RENAME ──────────────────────────────────────────────────

_ssh_rename() {
    local target="${1:-}"
    local new_name="${2:-}"

    if [[ ! -f "$AUTHORIZED_KEYS" ]] || [[ ! -s "$AUTHORIZED_KEYS" ]]; then
        warn "No SSH keys to rename"
        exit 0
    fi

    local -a keys=()
    while IFS= read -r line; do
        [[ -z "$line" || "$line" == \#* ]] && continue
        keys+=("$line")
    done < "$AUTHORIZED_KEYS"

    if [[ ${#keys[@]} -eq 0 ]]; then
        warn "No SSH keys to rename"
        exit 0
    fi

    if [[ -z "$target" ]]; then
        echo ""
        echo -e "  ${BOLD}Rename SSH Key${NC}"
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
        echo ""

        local i=0
        for k in "${keys[@]}"; do
            (( i++ )) || true
            local comment
            comment=$(echo "$k" | awk '{$1=$2=""; print}' | xargs)
            [[ -z "$comment" ]] && comment="(no comment)"
            local fingerprint
            fingerprint=$(echo "$k" | ssh-keygen -lf - 2>/dev/null | awk '{print $2}') || fingerprint="?"
            echo -e "  ${CYAN}${i}${NC}  ${BOLD}${comment}${NC}"
            echo -e "     ${DIM}${fingerprint}${NC}"
            echo ""
        done

        echo -en "  ${BOLD}Key number to rename (or 'q' to cancel):${NC} "
        read -r target
    fi

    [[ "$target" == "q" || -z "$target" ]] && { echo "  Cancelled"; exit 0; }

    if ! [[ "$target" =~ ^[0-9]+$ ]] || [[ "$target" -lt 1 ]] || [[ "$target" -gt ${#keys[@]} ]]; then
        error "Invalid selection: ${target} (must be 1-${#keys[@]})"
        exit 1
    fi

    if [[ -z "$new_name" ]]; then
        echo -en "  ${BOLD}New name:${NC} "
        read -r new_name
    fi

    if [[ -z "$new_name" ]]; then
        error "Name cannot be empty"
        exit 1
    fi

    local selected_key="${keys[$((target-1))]}"
    local key_type key_data
    key_type=$(echo "$selected_key" | awk '{print $1}')
    key_data=$(echo "$selected_key" | awk '{print $2}')
    local updated_key="${key_type} ${key_data} ${new_name}"

    local tmp
    tmp=$(mktemp)
    local idx=0
    while IFS= read -r line; do
        if [[ -z "$line" || "$line" == \#* ]]; then
            echo "$line" >> "$tmp"
            continue
        fi
        (( idx++ )) || true
        if [[ $idx -eq $target ]]; then
            echo "$updated_key" >> "$tmp"
        else
            echo "$line" >> "$tmp"
        fi
    done < "$AUTHORIZED_KEYS"

    mv "$tmp" "$AUTHORIZED_KEYS"
    chown cipi:cipi "$AUTHORIZED_KEYS"
    chmod 600 "$AUTHORIZED_KEYS"

    local old_comment
    old_comment=$(echo "$selected_key" | awk '{$1=$2=""; print}' | xargs)
    [[ -z "$old_comment" ]] && old_comment="(no comment)"

    local fingerprint
    fingerprint=$(echo "$selected_key" | ssh-keygen -lf - 2>/dev/null | awk '{print $2}') || fingerprint="?"

    success "Key ${target} renamed to: ${new_name}"
    log_action "SSH KEY RENAME: ${old_comment} -> ${new_name}"

    # Email notification
    local server_ip; server_ip=$(curl -s --max-time 3 https://checkip.amazonaws.com 2>/dev/null || hostname)
    cipi_notify \
        "Cipi SSH key renamed on $(hostname)" \
        "An SSH key was renamed on the cipi user.\n\nServer: $(hostname) (${server_ip})\nOld name: ${old_comment}\nNew name: ${new_name}\nFingerprint: ${fingerprint}\nTime: $(date '+%Y-%m-%d %H:%M:%S %Z')" \
        ssh_key_rename
}

# ── REMOVE ───────────────────────────────────────────────────

_ssh_remove() {
    local target="${1:-}"

    if [[ ! -f "$AUTHORIZED_KEYS" ]] || [[ ! -s "$AUTHORIZED_KEYS" ]]; then
        warn "No SSH keys to remove"
        exit 0
    fi

    # Detect current session key fingerprint
    local session_fp
    session_fp=$(_get_session_fingerprint)

    # Build indexed list of keys (skip empty lines and comments)
    local -a keys=()
    while IFS= read -r line; do
        [[ -z "$line" || "$line" == \#* ]] && continue
        keys+=("$line")
    done < "$AUTHORIZED_KEYS"

    if [[ ${#keys[@]} -eq 0 ]]; then
        warn "No SSH keys to remove"
        exit 0
    fi

    # If no argument, show list and ask
    if [[ -z "$target" ]]; then
        echo ""
        echo -e "  ${BOLD}Remove SSH Key${NC}"
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
        echo ""

        local i=0
        for k in "${keys[@]}"; do
            (( i++ )) || true
            local comment
            comment=$(echo "$k" | awk '{$1=$2=""; print}' | xargs)
            [[ -z "$comment" ]] && comment="(no comment)"
            local fingerprint
            fingerprint=$(echo "$k" | ssh-keygen -lf - 2>/dev/null | awk '{print $2}') || fingerprint="?"

            local active_marker=""
            if [[ -n "$session_fp" && "$fingerprint" == "$session_fp" ]]; then
                active_marker="  ${GREEN}<< current session${NC}"
            fi

            echo -e "  ${CYAN}${i}${NC}  ${comment}  ${DIM}${fingerprint}${NC}${active_marker}"
        done

        echo ""
        echo -en "  ${BOLD}Key number to remove (or 'q' to cancel):${NC} "
        read -r target
    fi

    [[ "$target" == "q" || -z "$target" ]] && { echo "  Cancelled"; exit 0; }

    # Validate number
    if ! [[ "$target" =~ ^[0-9]+$ ]] || [[ "$target" -lt 1 ]] || [[ "$target" -gt ${#keys[@]} ]]; then
        error "Invalid selection: ${target} (must be 1-${#keys[@]})"
        exit 1
    fi

    # Safety: prevent removing the last key
    if [[ ${#keys[@]} -eq 1 ]]; then
        error "Cannot remove the last SSH key — you would be locked out"
        echo -e "  ${DIM}Add another key first: cipi ssh add${NC}"
        exit 1
    fi

    local removed_key="${keys[$((target-1))]}"

    # Safety: prevent removing the key used for the current SSH session
    if [[ -n "$session_fp" ]]; then
        local removed_fp
        removed_fp=$(echo "$removed_key" | ssh-keygen -lf - 2>/dev/null | awk '{print $2}') || removed_fp=""
        if [[ -n "$removed_fp" && "$removed_fp" == "$session_fp" ]]; then
            error "Cannot remove the key you are currently logged in with"
            echo -e "  ${DIM}Log in with a different key first, then remove this one${NC}"
            exit 1
        fi
    fi

    local removed_comment
    removed_comment=$(echo "$removed_key" | awk '{$1=$2=""; print}' | xargs)
    [[ -z "$removed_comment" ]] && removed_comment="(no comment)"

    # Remove the key
    local tmp
    tmp=$(mktemp)
    local idx=0
    while IFS= read -r line; do
        if [[ -z "$line" || "$line" == \#* ]]; then
            echo "$line" >> "$tmp"
            continue
        fi
        (( idx++ )) || true
        [[ $idx -ne $target ]] && echo "$line" >> "$tmp"
    done < "$AUTHORIZED_KEYS"

    mv "$tmp" "$AUTHORIZED_KEYS"
    chown cipi:cipi "$AUTHORIZED_KEYS"
    chmod 600 "$AUTHORIZED_KEYS"

    local removed_fp
    removed_fp=$(echo "$removed_key" | ssh-keygen -lf - 2>/dev/null | awk '{print $2}') || removed_fp="?"

    success "Key removed: ${removed_comment}"
    log_action "SSH KEY REMOVE: ${removed_comment}"

    # Email notification
    local server_ip; server_ip=$(curl -s --max-time 3 https://checkip.amazonaws.com 2>/dev/null || hostname)
    cipi_notify \
        "Cipi SSH key removed on $(hostname)" \
        "An SSH key was removed from the cipi user.\n\nServer: $(hostname) (${server_ip})\nComment: ${removed_comment}\nFingerprint: ${removed_fp}\nTime: $(date '+%Y-%m-%d %H:%M:%S %Z')\nRemaining keys: $((${#keys[@]} - 1))" \
        ssh_key_remove
}
