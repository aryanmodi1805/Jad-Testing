#!/bin/bash
set -euo pipefail

# ==============================
# LOGGING
# ==============================
LOG_FILE="/home/forge/jad.services/storage/logs/backup.log"
# Ensure the log directory exists
mkdir -p "$(dirname "$LOG_FILE")"

exec > >(tee -a "${LOG_FILE}") 2>&1


# ==============================

# CONFIG
# ==============================
DB_NAME="evanto24"

REMOTE_USER="u518683-sub1"
REMOTE_HOST="u518683-sub1.your-storagebox.de"
REMOTE_PORT="23"
# IMPORTANT: Use quotes because the folder name has a space
REMOTE_DIR="backup"
REMOTE_PASSWORD="sfQkTj#34@Hh$0%2^97"

LOCAL_TMP="/home/forge/tmp"
LOCAL_NEW="$LOCAL_TMP/new_backup.sql.gz"

# ==============================
# CHECK DEPENDENCIES
# ==============================
if ! command -v sshpass &> /dev/null; then
    echo "Error: sshpass is not installed."
    echo "Please install it using: sudo apt-get install sshpass (on Debian/Ubuntu)"
    exit 1
fi

# ==============================
# PREP
# ==============================
mkdir -p "$LOCAL_TMP"

# ==============================
# DUMP + COMPRESS
# ==============================
echo "Creating backup..."
# Use absolute path to mysqldump to be safe
/usr/bin/mysqldump \
  --single-transaction \
  --quick \
  --lock-tables=false \
  "$DB_NAME" | /bin/gzip > "$LOCAL_NEW"

# Validate backup size (ensure it's not empty)
if [ ! -s "$LOCAL_NEW" ]; then
  echo "Backup failed: empty file"
  exit 1
fi

# Export password for sshpass to use
export SSHPASS="$REMOTE_PASSWORD"

# ==============================
# ROTATE REMOTE BACKUPS
# ==============================
echo "Checking remote connection and directory..."

# Helper function to run remote commands via SSH with password
run_remote() {
    /usr/bin/sshpass -e /usr/bin/ssh -p "$REMOTE_PORT" -o BatchMode=no -o StrictHostKeyChecking=no "$REMOTE_USER@$REMOTE_HOST" "$1"
}

# Try to create directory if it doesn't exist
# We capture output to check for errors
if ! run_remote "mkdir -p '$REMOTE_DIR'"; then
    echo "Warning: Failed to create remote directory '$REMOTE_DIR'. It might already exist or SSH commands are restricted."
    echo "Continuing with upload attempt..."
fi

echo "Rotating remote backups..."
# Rename 'latest' to 'previous' if it exists
# We allow failure here (|| true) in case file doesn't exist yet
run_remote "cd '$REMOTE_DIR' && [ -f latest_backup.sql.gz ] && mv latest_backup.sql.gz previous_backup.sql.gz || true"

# ==============================
# UPLOAD NEW AS LATEST
# ==============================
echo "Uploading new backup to $REMOTE_DIR/latest_backup.sql.gz..."

if /usr/bin/sshpass -e /usr/bin/scp -P "$REMOTE_PORT" \
  -o BatchMode=no -o StrictHostKeyChecking=no \
  "$LOCAL_NEW" \
  "$REMOTE_USER@$REMOTE_HOST:'$REMOTE_DIR/latest_backup.sql.gz'"; then
    echo "Upload successful!"
else
    echo "Upload failed!"
    exit 1
fi

# ==============================
# CLEANUP LOCAL
# ==============================
rm -f "$LOCAL_NEW"
# Unset the password variable for security
unset SSHPASS

echo "Backup completed successfully."
