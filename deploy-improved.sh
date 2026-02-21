#!/bin/bash
cd /home/forge/jad.services

# Backup current build assets before git operations
if [ -d "public/build" ]; then
    echo "Backing up build assets..."
    cp -r public/build public/build.backup
fi

export GIT_SSH_COMMAND="ssh -i /home/forge/.ssh/id_rsa -o IdentitiesOnly=yes -o StrictHostKeyChecking=accept-new"

# Only stash tracked files, not untracked build assets
git stash push --keep-index || true
git pull --no-rebase origin "$FORGE_SITE_BRANCH"

# Frontend - with error handling
echo "Building frontend assets..."
npm ci || npm install

if ! npm run build; then
    echo "ERROR: npm build failed, restoring backup"
    if [ -d "public/build.backup" ]; then
        rm -rf public/build
        mv public/build.backup public/build
    fi
    exit 1
fi

# Clean up backup on success
rm -rf public/build.backup

# Backend
$FORGE_COMPOSER install \
  --no-dev \
  --no-interaction \
  --prefer-dist \
  --optimize-autoloader

# Cache handling - AFTER build completes
$FORGE_PHP artisan config:clear
$FORGE_PHP artisan view:clear
$FORGE_PHP artisan filament:optimize-clear
$FORGE_PHP artisan route:cache
$FORGE_PHP artisan event:cache

# Database
$FORGE_PHP artisan migrate --force

# Restart services
( flock -w 10 9 || exit 1
  echo 'Restarting FPM...'
  sudo -S service "$FORGE_PHP_FPM" reload
) 9>/tmp/fpmlock

$FORGE_PHP artisan queue:restart
$FORGE_PHP artisan reverb:restart
