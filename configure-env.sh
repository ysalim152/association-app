#!/bin/bash

################################################################################
# Environment Configuration Script
# Post-Deployment Configuration Helper
################################################################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

log_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

log_error() {
    echo -e "${RED}✗ $1${NC}"
}

APP_PATH="/var/www/association-app"

# Check if .env file exists or create it
if [ ! -f "$APP_PATH/.env" ]; then
    log "Creating .env file..."
    
    # Get database credentials from deploy.sh output or user input
    read -p "Database Host (default: localhost): " DB_HOST
    DB_HOST=${DB_HOST:-localhost}
    
    read -p "Database Name (default: association_db): " DB_NAME
    DB_NAME=${DB_NAME:-association_db}
    
    read -p "Database User (default: assoc_user): " DB_USER
    DB_USER=${DB_USER:-assoc_user}
    
    read -sp "Database Password: " DB_PASS
    echo ""
    
    read -p "App Environment (development/production, default: production): " APP_ENV
    APP_ENV=${APP_ENV:-production}
    
    read -p "App URL (default: http://association.local): " APP_URL
    APP_URL=${APP_URL:-http://association.local}
    
    read -p "SMTP Host (for emails, default: localhost): " SMTP_HOST
    SMTP_HOST=${SMTP_HOST:-localhost}
    
    read -p "SMTP Port (default: 587): " SMTP_PORT
    SMTP_PORT=${SMTP_PORT:-587}
    
    read -p "SMTP Username: " SMTP_USER
    read -sp "SMTP Password: " SMTP_PASS
    echo ""
    
    # Create .env file
    cat > "$APP_PATH/.env" << EOF
# Application Environment
APP_ENV=${APP_ENV}
APP_URL=${APP_URL}
APP_DEBUG=$([ "$APP_ENV" = "development" ] && echo "true" || echo "false")

# Database Configuration
DB_HOST=${DB_HOST}
DB_PORT=3306
DB_NAME=${DB_NAME}
DB_USER=${DB_USER}
DB_PASS=${DB_PASS}

# Mail Configuration
MAIL_HOST=${SMTP_HOST}
MAIL_PORT=${SMTP_PORT}
MAIL_USERNAME=${SMTP_USER}
MAIL_PASSWORD=${SMTP_PASS}
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@association.local
MAIL_FROM_NAME=Association App

# Session Configuration
SESSION_DRIVER=files
SESSION_LIFETIME=3600

# Logging
LOG_CHANNEL=single
LOG_LEVEL=debug

# Security
APP_KEY=$(openssl rand -base64 32)
CSRF_PROTECTION=true

# Timezone
APP_TIMEZONE=Europe/Paris
EOF
    
    chmod 640 "$APP_PATH/.env"
    chown www-data:www-data "$APP_PATH/.env"
    
    log_success ".env file created and configured"
else
    log_success ".env file already exists"
fi

# Update database.php if needed
log "Updating database configuration..."

DB_CONFIG="$APP_PATH/app/config/database.php"

if [ -f "$DB_CONFIG" ]; then
    # This is a manual step that requires database credentials
    log "Database configuration file found: $DB_CONFIG"
    log_success "Update the database credentials in $DB_CONFIG if needed"
else
    log_error "Database config file not found: $DB_CONFIG"
fi

# Create necessary directories with proper permissions
log "Verifying directory permissions..."

dirs=(
    "$APP_PATH/logs"
    "$APP_PATH/storage"
    "$APP_PATH/storage/exports"
    "$APP_PATH/public/uploads"
    "$APP_PATH/storage/cache"
    "$APP_PATH/storage/sessions"
)

for dir in "${dirs[@]}"; do
    if [ ! -d "$dir" ]; then
        mkdir -p "$dir"
        log "Created directory: $dir"
    fi
    chown www-data:www-data "$dir"
    chmod 775 "$dir"
    log_success "Directory configured: $dir"
done

# Create backup directory
BACKUP_DIR="/var/backups/association-app"
if [ ! -d "$BACKUP_DIR" ]; then
    mkdir -p "$BACKUP_DIR"
    chown www-data:www-data "$BACKUP_DIR"
    chmod 775 "$BACKUP_DIR"
    log_success "Backup directory created: $BACKUP_DIR"
fi

# Test database connection
log "Testing database connection..."

read -p "Database User: " TEST_DB_USER
read -sp "Database Password: " TEST_DB_PASS
echo ""
read -p "Database Name: " TEST_DB_NAME

if mysql -h localhost -u "$TEST_DB_USER" -p"$TEST_DB_PASS" "$TEST_DB_NAME" -e "SELECT 1;" > /dev/null 2>&1; then
    log_success "Database connection successful!"
else
    log_error "Database connection failed. Check credentials."
    exit 1
fi

log_success "Configuration complete!"
log "Next steps:"
log "1. Verify .env file: cat $APP_PATH/.env"
log "2. Check file permissions: ls -la $APP_PATH/"
log "3. Test application: curl http://localhost/index.php"
log "4. Check Apache error log: tail -f /var/log/apache2/error.log"
