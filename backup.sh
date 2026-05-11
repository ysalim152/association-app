#!/bin/bash

################################################################################
# Backup Script - Association Management Application
# Scheduled backups of database and application files
################################################################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configuration
APP_PATH="/var/www/association-app"
BACKUP_DIR="/var/backups/association-app"
DB_NAME="association_db"
DB_USER="assoc_user"
DB_HOST="localhost"
RETENTION_DAYS=30

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

log_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

log_error() {
    echo -e "${RED}✗ $1${NC}"
}

BACKUP_DATE=$(date +%Y%m%d_%H%M%S)
DB_BACKUP_FILE="$BACKUP_DIR/db_backup_${BACKUP_DATE}.sql.gz"
FILES_BACKUP_FILE="$BACKUP_DIR/files_backup_${BACKUP_DATE}.tar.gz"

log "Starting backup process..."

# Backup Database
log "Backing up database..."

if [ -z "$DB_PASSWORD" ]; then
    # Try to get password from .env file
    if [ -f "$APP_PATH/.env" ]; then
        DB_PASSWORD=$(grep "^DB_PASS=" "$APP_PATH/.env" | cut -d'=' -f2)
    fi
fi

if mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" 2>/dev/null | gzip > "$DB_BACKUP_FILE"; then
    log_success "Database backup created: $(du -h "$DB_BACKUP_FILE" | cut -f1)"
else
    log_error "Database backup failed"
    exit 1
fi

# Backup Application Files
log "Backing up application files..."

if tar -czf "$FILES_BACKUP_FILE" \
    --exclude='logs' \
    --exclude='storage/cache' \
    --exclude='public/uploads/temp' \
    --exclude='.git' \
    --exclude='node_modules' \
    -C "$APP_PATH/.." "$(basename $APP_PATH)" 2>/dev/null; then
    log_success "Files backup created: $(du -h "$FILES_BACKUP_FILE" | cut -f1)"
else
    log_error "Files backup failed"
    exit 1
fi

# Create backup manifest
MANIFEST_FILE="$BACKUP_DIR/backup_manifest_${BACKUP_DATE}.txt"
cat > "$MANIFEST_FILE" << EOF
Backup Date: $(date)
Database: $DB_NAME
Application: $APP_PATH

Files in this backup:
- $DB_BACKUP_FILE
- $FILES_BACKUP_FILE

To restore database:
  gunzip -c $DB_BACKUP_FILE | mysql -u $DB_USER -p -h $DB_HOST $DB_NAME

To restore files:
  tar -xzf $FILES_BACKUP_FILE -C /var/www/

Size: DB=$(du -h "$DB_BACKUP_FILE" | cut -f1), Files=$(du -h "$FILES_BACKUP_FILE" | cut -f1)
EOF

log_success "Backup manifest created"

# Cleanup old backups
log "Cleaning up old backups (retention: $RETENTION_DAYS days)..."

find "$BACKUP_DIR" -maxdepth 1 -name "db_backup_*" -type f -mtime +$RETENTION_DAYS -delete
find "$BACKUP_DIR" -maxdepth 1 -name "files_backup_*" -type f -mtime +$RETENTION_DAYS -delete
find "$BACKUP_DIR" -maxdepth 1 -name "backup_manifest_*" -type f -mtime +$RETENTION_DAYS -delete

log_success "Old backups cleaned up"

# Display backup summary
log "========================================"
log "Backup Summary"
log "========================================"
log "Backup Directory: $BACKUP_DIR"
log "Total backups size: $(du -sh "$BACKUP_DIR" | cut -f1)"
log "Number of backups: $(find "$BACKUP_DIR" -name "db_backup_*" -type f | wc -l)"
log "Oldest backup: $(find "$BACKUP_DIR" -name "db_backup_*" -type f -printf '%T@ %p\n' | sort -n | head -1 | awk '{print $2}')"
log "Latest backup: $(ls -t "$BACKUP_DIR"/db_backup_* 2>/dev/null | head -1)"
log "========================================"

log_success "Backup process completed"
