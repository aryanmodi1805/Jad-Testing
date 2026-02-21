#!/bin/bash

# Git Post-Receive Hook for Virtualmin Laravel Deployment
# Place this file in: /home/username/app.git/hooks/post-receive
# Make it executable: chmod +x /home/username/app.git/hooks/post-receive
#
# Usage: 
# 1. Create bare repo: git clone --bare https://github.com/user/repo.git /home/username/app.git
# 2. Copy this script to /home/username/app.git/hooks/post-receive
# 3. Update the variables below
# 4. Make executable: chmod +x /home/username/app.git/hooks/post-receive
# 5. Add remote: git remote add production username@server:/home/username/app.git

# ============================================
# CONFIGURATION - UPDATE THESE VALUES
# ============================================

# Path to your deployed application
TARGET="/home/username/domains/yourdomain.com/app"

# Path to the bare git repository
GIT_DIR="/home/username/app.git"

# Branch to deploy
BRANCH="main"

# Virtualmin username (for permissions)
VIRTUALMIN_USER="username"

# PHP version
PHP_VERSION="8.2"

# ============================================
# DEPLOYMENT SCRIPT
# ============================================

# Log file for deployments
LOG_FILE="/home/$VIRTUALMIN_USER/deploy.log"

# Function to log messages
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

log "=========================================="
log "Starting deployment..."

# Read git ref information
while read oldrev newrev ref
do
    # Only deploy if pushing to the specified branch
    if [[ $ref = refs/heads/$BRANCH ]];
    then
        log "Deploying $BRANCH branch to $TARGET..."
        
        # Check if target directory exists
        if [ ! -d "$TARGET/server" ]; then
            log "ERROR: Target directory $TARGET/server does not exist!"
            exit 1
        fi
        
        cd "$TARGET/server" || exit 1
        
        # Fetch and checkout latest code
        log "Fetching latest code..."
        git --git-dir="$GIT_DIR" --work-tree="$TARGET" fetch origin "$BRANCH"
        git --git-dir="$GIT_DIR" --work-tree="$TARGET" reset --hard "origin/$BRANCH"
        
        # Install/update Composer dependencies
        log "Installing Composer dependencies..."
        composer install --no-dev --optimize-autoloader --no-interaction 2>&1 | tee -a "$LOG_FILE"
        
        # Build assets
        log "Building assets..."
        if [ -f "package.json" ]; then
            npm ci --production=false 2>&1 | tee -a "$LOG_FILE"
            npm run build 2>&1 | tee -a "$LOG_FILE"
        fi
        
        # Run Laravel commands
        log "Running Laravel optimizations..."
        php artisan migrate --force 2>&1 | tee -a "$LOG_FILE" || log "WARNING: Migration failed"
        php artisan config:cache 2>&1 | tee -a "$LOG_FILE"
        php artisan route:cache 2>&1 | tee -a "$LOG_FILE"
        php artisan view:cache 2>&1 | tee -a "$LOG_FILE"
        
        # Restart queue workers
        log "Restarting queue workers..."
        php artisan queue:restart 2>&1 | tee -a "$LOG_FILE" || log "WARNING: Queue restart failed"
        
        # Set permissions
        log "Setting permissions..."
        chown -R "$VIRTUALMIN_USER:$VIRTUALMIN_USER" "$TARGET"
        find "$TARGET/server" -type d -exec chmod 755 {} \;
        find "$TARGET/server" -type f -exec chmod 644 {} \;
        chmod -R 775 "$TARGET/server/storage"
        chmod -R 775 "$TARGET/server/bootstrap/cache"
        
        # Restart PHP-FPM (optional, may require sudo)
        log "Restarting PHP-FPM..."
        sudo systemctl restart "php${PHP_VERSION}-fpm" 2>&1 | tee -a "$LOG_FILE" || log "WARNING: Could not restart PHP-FPM"
        
        # Reload Apache (optional, may require sudo)
        log "Reloading Apache..."
        sudo systemctl reload apache2 2>&1 | tee -a "$LOG_FILE" || log "WARNING: Could not reload Apache"
        
        log "✅ Deployment completed successfully!"
        log "=========================================="
    else
        log "Skipping deployment for ref: $ref (not $BRANCH branch)"
    fi
done






