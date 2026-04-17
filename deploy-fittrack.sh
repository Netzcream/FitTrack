#!/bin/bash

set -euo pipefail

REPO_DIR="/home/netz8452/repositories/FitTrack"
TARGET_DIR="/home/netz8452/fittrack.com.ar"
BRANCH="main"

PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"

APP_WAS_PUT_DOWN=0

log() {
    echo "== $1 =="
}

require_command() {
    if ! command -v "$1" >/dev/null 2>&1; then
        echo "ERROR: required command not found: $1"
        exit 1
    fi
}

artisan() {
    "$PHP_BIN" artisan "$@"
}

bring_app_up() {
    if [ "$APP_WAS_PUT_DOWN" -eq 1 ] && [ -f "$TARGET_DIR/artisan" ]; then
        cd "$TARGET_DIR" || return
        artisan up || true
    fi
}

trap bring_app_up EXIT

require_command git
require_command rsync
require_command "$PHP_BIN"
require_command "$COMPOSER_BIN"

log "Starting deployment"
date

log "Updating repository"
cd "$REPO_DIR"
git fetch origin "$BRANCH"
git checkout "$BRANCH"
git pull --ff-only origin "$BRANCH"

mkdir -p "$TARGET_DIR"
cd "$TARGET_DIR"

if [ -f artisan ]; then
    log "Putting app in maintenance mode"
    artisan down --retry=60 --render="errors::503" || true
    APP_WAS_PUT_DOWN=1
fi

if [ -f "$TARGET_DIR/artisan" ]; then
    log "Running tenant backup if available"
    artisan app:backup-tenants || echo "WARNING: tenant backup failed, continuing deploy"
fi

log "Syncing files"
rsync -az --delete \
  --exclude ".git/" \
  --exclude ".github/" \
  --exclude ".cpanel.yml" \
  --exclude ".env" \
  --exclude "storage/" \
  --exclude "vendor/" \
  --exclude "node_modules/" \
  --exclude ".idea/" \
  --exclude ".vscode/" \
  --exclude "documents/" \
  --exclude "*.log" \
  "$REPO_DIR/" "$TARGET_DIR/"

cd "$TARGET_DIR"

if [ ! -f .env ]; then
    if [ -f .env.deploy ]; then
        log "Creating .env from .env.deploy"
        cp .env.deploy .env
    elif [ -f .env.example ]; then
        log "Creating .env from .env.example"
        cp .env.example .env
    else
        echo "ERROR: .env is missing and no fallback file was found"
        exit 1
    fi
fi

log "Installing PHP dependencies"
"$COMPOSER_BIN" install \
  --no-interaction \
  --prefer-dist \
  --no-dev \
  --optimize-autoloader

log "Ensuring writable directories"
mkdir -p \
  storage/app/public \
  storage/app/descargas \
  storage/framework/cache \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs \
  bootstrap/cache

log "Ensuring storage symlink"
if [ -L public/storage ]; then
    echo "public/storage symlink already exists"
else
    if [ -e public/storage ]; then
        rm -rf public/storage
    fi
    artisan storage:link || true
fi

log "Running database updates"
artisan migrate --force
artisan tenants:migrate --force
artisan tenants:seed --class=TenantUpdateSeeder --force

log "Refreshing caches"
artisan optimize:clear
artisan config:cache
artisan route:cache
artisan view:cache

log "Fixing permissions for cPanel"
find "$TARGET_DIR" -type d -exec chmod 755 {} \;
find "$TARGET_DIR" -type f -exec chmod 644 {} \;
chmod -R 775 "$TARGET_DIR/storage" "$TARGET_DIR/bootstrap/cache"

if [ -d "$TARGET_DIR/public" ]; then
    chmod 755 "$TARGET_DIR/public"
fi

if artisan list | grep -q "queue:restart"; then
    log "Restarting queue workers"
    artisan queue:restart || true
fi

log "Bringing app up"
artisan up
APP_WAS_PUT_DOWN=0

log "Deployment finished successfully"
date
