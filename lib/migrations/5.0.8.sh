#!/bin/bash
#############################################
# Cipi Migration 5.0.8 — noop
#
# Panel API VCS wiring + composer update for existing installs lives in
# lib/self-update.sh (only when /opt/cipi/api/artisan already exists).
# This migration must not install or touch the panel API.
#############################################

set -e

echo "Migration 5.0.8 — nothing to migrate (API updates handled by self-update when present)"
echo "Migration 5.0.8 complete"
