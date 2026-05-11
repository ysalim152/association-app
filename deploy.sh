#!/bin/bash

################################################################################
# Deployment Script - Association Management Application
# Ubuntu Server 22.04 LTS
# Gestion Associations Sportives v1.0.0
################################################################################

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

################################################################################
# Configuration Variables
################################################################################

# Deployment settings
APP_NAME="association-app"
APP_DOMAIN="${APP_DOMAIN:-association.local}"
APP_USER="${APP_USER:-www-data}"
APP_GROUP="${APP_GROUP:-www-data}"
APP_PATH="/var/www/${APP_NAME}"
GIT_REPO="${GIT_REPO:-https://github.com/yourusername/association-app.git}"
GIT_BRANCH="${GIT_BRANCH:-main}"

# Database settings
DB_NAME="association_db"
DB_USER="assoc_user"
DB_PASS="Aylissam@26"  # Generate random password
DB_HOST="localhost"

# PHP and Apache settings
PHP_VERSION="8.4"
APACHE_PORT="${APACHE_PORT:-80}"
APACHE_HTTPS_PORT="${APACHE_HTTPS_PORT:-443}"
ENABLE_SSL="${ENABLE_SSL:-false}"
SSL_EMAIL="${SSL_EMAIL:-admin@example.com}"

# Logging
LOG_FILE="/var/log/${APP_NAME}-deploy.log"

################################################################################
# Helper Functions
################################################################################

log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

log_success() {
    echo -e "${GREEN}✓ $1${NC}" | tee -a "$LOG_FILE"
}

log_warning() {
    echo -e "${YELLOW}⚠ $1${NC}" | tee -a "$LOG_FILE"
}

log_error() {
    echo -e "${RED}✗ $1${NC}" | tee -a "$LOG_FILE"
}

check_root() {
    if [[ $EUID -ne 0 ]]; then
        log_error "This script must be run as root"
        exit 1
    fi
    log_success "Running as root"
}

check_ubuntu() {
    if [[ ! -f /etc/os-release ]]; then
        log_error "Cannot determine OS"
        exit 1
    fi
    
    . /etc/os-release
    if [[ "$VERSION_ID" != "22.04" ]]; then
        log_warning "This script is optimized for Ubuntu 22.04, found: $VERSION_ID"
    fi
    log_success "Ubuntu $VERSION_ID detected"
}

################################################################################
# Step 1: System Update and Base Dependencies
################################################################################

step_system_update() {
    log "========================================"
    log "Step 1: System Update and Dependencies"
    log "========================================"
    
    apt update
    apt upgrade -y
    apt install -y curl wget git unzip zip software-properties-common \
        apt-transport-https ca-certificates gnupg lsb-release
    
    log_success "System updated and base dependencies installed"
}

################################################################################
# Step 2: Install Apache 2.4
################################################################################

step_install_apache() {
    log "========================================"
    log "Step 2: Apache 2.4 Installation"
    log "========================================"
    
    apt install -y apache2 apache2-utils
    
    # Enable required modules
    a2enmod rewrite
    a2enmod ssl
    a2enmod headers
    a2enmod http2
    a2enmod proxy
    a2enmod proxy_fcgi
    
    # Adjust Apache settings for better security and performance
    sed -i 's/ServerTokens OS/ServerTokens Prod/' /etc/apache2/conf-available/security.conf
    sed -i 's/ServerSignature On/ServerSignature Off/' /etc/apache2/conf-available/security.conf
    
    systemctl restart apache2
    systemctl enable apache2
    
    log_success "Apache 2.4 installed and configured"
}

################################################################################
# Step 3: Install PHP 8.4
################################################################################

step_install_php() {
    log "========================================"
    log "Step 3: PHP ${PHP_VERSION} Installation"
    log "========================================"
    
    # Add PHP repository
    add-apt-repository ppa:ondrej/php -y
    apt update
    
    # Install PHP and extensions
    apt install -y \
        php${PHP_VERSION} \
        php${PHP_VERSION}-cli \
        php${PHP_VERSION}-fpm \
        php${PHP_VERSION}-mbstring \
        php${PHP_VERSION}-pdo \
        php${PHP_VERSION}-mysql \
        php${PHP_VERSION}-curl \
        php${PHP_VERSION}-gd \
        php${PHP_VERSION}-json \
        php${PHP_VERSION}-zip \
        php${PHP_VERSION}-xml \
        php${PHP_VERSION}-intl \
        php${PHP_VERSION}-opcache \
        php${PHP_VERSION}-bcmath
    
    # Configure PHP for production
    php_ini="/etc/php/${PHP_VERSION}/apache2/php.ini"
    
    sed -i 's/display_errors = On/display_errors = Off/' "$php_ini"
    sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 100M/' "$php_ini"
    sed -i 's/post_max_size = 8M/post_max_size = 100M/' "$php_ini"
    sed -i 's/max_execution_time = 30/max_execution_time = 300/' "$php_ini"
    sed -i 's/memory_limit = 128M/memory_limit = 256M/' "$php_ini"
    
    # Enable OPcache for better performance
    cat > /etc/php/${PHP_VERSION}/apache2/conf.d/99-opcache.ini << 'EOF'
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=100000
opcache.revalidate_freq=60
opcache.save_comments=1
EOF
    
    a2enmod php${PHP_VERSION}
    systemctl restart apache2
    
    log_success "PHP ${PHP_VERSION} installed and configured"
}

################################################################################
# Step 4: Install MariaDB 10.6
################################################################################

step_install_mariadb() {
    log "========================================"
    log "Step 4: MariaDB 10.6 Installation"
    log "========================================"
    
    # Add MariaDB repository
    curl -LsS https://r.mariadb.com/downloads/mariadb_repo_setup | bash -s -- --mariadb-version=10.6
    apt update
    
    # Install MariaDB
    apt install -y mariadb-server mariadb-client
    
    # Start and enable MariaDB
    systemctl start mariadb
    systemctl enable mariadb
    
    log_success "MariaDB 10.6 installed"
    
    # Secure MariaDB installation
    log "Securing MariaDB..."
    
    # Set root password
    mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED BY '${DB_PASS}'; FLUSH PRIVILEGES;"
    
    # Create application database and user
    mysql -u root -p"${DB_PASS}" << EOF
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
EOF
    
    log_success "MariaDB configured and database created"
}

################################################################################
# Step 5: Clone Application Repository
################################################################################

step_clone_app() {
    log "========================================"
    log "Step 5: Clone Application Repository"
    log "========================================"
    
    if [ -d "$APP_PATH" ]; then
        log_warning "Application path already exists, backing up..."
        mv "$APP_PATH" "${APP_PATH}.backup.$(date +%s)"
    fi
    
    git clone -b "$GIT_BRANCH" "$GIT_REPO" "$APP_PATH"
    
    log_success "Application cloned to $APP_PATH"
}

################################################################################
# Step 6: Setup Application Directories and Permissions
################################################################################

step_setup_permissions() {
    log "========================================"
    log "Step 6: Setup Application Directories"
    log "========================================"
    
    # Create required directories
    mkdir -p "$APP_PATH/logs"
    mkdir -p "$APP_PATH/storage/exports"
    mkdir -p "$APP_PATH/public/uploads"
    mkdir -p "$APP_PATH/storage/cache"
    mkdir -p "$APP_PATH/storage/sessions"
    
    # Set ownership
    chown -R "$APP_USER:$APP_GROUP" "$APP_PATH"
    
    # Set permissions
    chmod -R 755 "$APP_PATH"
    chmod -R 775 "$APP_PATH/logs"
    chmod -R 775 "$APP_PATH/storage"
    chmod -R 775 "$APP_PATH/public/uploads"
    
    # Restrict config files
    chmod 640 "$APP_PATH/app/config/config.php"
    chmod 640 "$APP_PATH/app/config/database.php"
    
    log_success "Application directories and permissions configured"
}

################################################################################
# Step 7: Initialize Database
################################################################################

step_init_database() {
    log "========================================"
    log "Step 7: Initialize Database"
    log "========================================"
    
    # Import database schema
    mysql -u "$DB_USER" -p"${DB_PASS}" "$DB_NAME" < "$APP_PATH/database/init.sql"
    
    log_success "Database schema imported"
}

################################################################################
# Step 8: Configure Apache VirtualHost
################################################################################

step_configure_apache() {
    log "========================================"
    log "Step 8: Configure Apache VirtualHost"
    log "========================================"
    
    # Create VirtualHost configuration
    cat > "/etc/apache2/sites-available/${APP_NAME}.conf" << EOF
<VirtualHost *:${APACHE_PORT}>
    ServerName ${APP_DOMAIN}
    ServerAlias www.${APP_DOMAIN}
    ServerAdmin admin@${APP_DOMAIN}
    
    DocumentRoot ${APP_PATH}/public
    
    # Logging
    ErrorLog ${LOG_FILE/.log/-apache-error.log}
    CustomLog ${LOG_FILE/.log/-apache-access.log} combined
    
    # Directory permissions
    <Directory ${APP_PATH}/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        
        # Rewrite rules
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule ^(.*)$ index.php?url=\$1 [QSA,L]
        </IfModule>
    </Directory>
    
    # Deny access to sensitive directories
    <Directory ${APP_PATH}/app>
        Require all denied
    </Directory>
    
    <Directory ${APP_PATH}/database>
        Require all denied
    </Directory>
    
    <Directory ${APP_PATH}/storage>
        Require all denied
    </Directory>
    
    # PHP Configuration
    <FilesMatch \\.php\$>
        SetHandler "proxy:unix:/run/php/php${PHP_VERSION}-fpm.sock|fcgi://localhost"
    </FilesMatch>
    
    # Security Headers
    <IfModule mod_headers.c>
        Header always set X-Frame-Options "SAMEORIGIN"
        Header always set X-Content-Type-Options "nosniff"
        Header always set X-XSS-Protection "1; mode=block"
        Header always set Referrer-Policy "strict-origin-when-cross-origin"
        Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"
    </IfModule>
    
    # Compression
    <IfModule mod_deflate.c>
        AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
    </IfModule>
    
    # Cache control
    <IfModule mod_expires.c>
        ExpiresActive On
        ExpiresByType image/jpg "access plus 1 month"
        ExpiresByType image/jpeg "access plus 1 month"
        ExpiresByType image/gif "access plus 1 month"
        ExpiresByType image/png "access plus 1 month"
        ExpiresByType text/css "access plus 1 week"
        ExpiresByType application/javascript "access plus 1 week"
    </IfModule>
</VirtualHost>
EOF
    
    # Enable the VirtualHost
    a2ensite "${APP_NAME}.conf"
    
    # Disable default site if exists
    a2dissite 000-default.conf 2>/dev/null || true
    
    # Test Apache configuration
    if ! apache2ctl configtest; then
        log_error "Apache configuration test failed"
        exit 1
    fi
    
    systemctl restart apache2
    log_success "Apache VirtualHost configured"
}

################################################################################
# Step 9: Enable SSL with Let's Encrypt (Optional)
################################################################################

step_enable_ssl() {
    if [ "$ENABLE_SSL" != "true" ]; then
        log_warning "SSL not enabled (set ENABLE_SSL=true to enable)"
        return
    fi
    
    log "========================================"
    log "Step 9: Enable SSL with Let's Encrypt"
    log "========================================"
    
    # Install Certbot
    apt install -y certbot python3-certbot-apache
    
    # Obtain certificate
    certbot certonly --apache \
        -d "$APP_DOMAIN" \
        -d "www.$APP_DOMAIN" \
        --non-interactive \
        --agree-tos \
        -m "$SSL_EMAIL"
    
    # Update VirtualHost to include HTTPS
    cat > "/etc/apache2/sites-available/${APP_NAME}-ssl.conf" << EOF
<VirtualHost *:${APACHE_HTTPS_PORT}>
    ServerName ${APP_DOMAIN}
    ServerAlias www.${APP_DOMAIN}
    ServerAdmin admin@${APP_DOMAIN}
    
    DocumentRoot ${APP_PATH}/public
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/${APP_DOMAIN}/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/${APP_DOMAIN}/privkey.pem
    SSLProtocol -all +TLSv1.2 +TLSv1.3
    SSLCipherSuite HIGH:!aNULL:!MD5
    SSLCompression off
    
    # HSTS Header
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    
    # Logging
    ErrorLog ${LOG_FILE/.log/-apache-ssl-error.log}
    CustomLog ${LOG_FILE/.log/-apache-ssl-access.log} combined
    
    # Include same configuration as HTTP site
    <Directory ${APP_PATH}/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule ^(.*)$ index.php?url=\$1 [QSA,L]
        </IfModule>
    </Directory>
    
    <Directory ${APP_PATH}/app>
        Require all denied
    </Directory>
    
    <Directory ${APP_PATH}/database>
        Require all denied
    </Directory>
    
    <Directory ${APP_PATH}/storage>
        Require all denied
    </Directory>
    
    <FilesMatch \\.php\$>
        SetHandler "proxy:unix:/run/php/php${PHP_VERSION}-fpm.sock|fcgi://localhost"
    </FilesMatch>
    
    <IfModule mod_headers.c>
        Header always set X-Frame-Options "SAMEORIGIN"
        Header always set X-Content-Type-Options "nosniff"
        Header always set X-XSS-Protection "1; mode=block"
        Header always set Referrer-Policy "strict-origin-when-cross-origin"
    </IfModule>
    
    <IfModule mod_deflate.c>
        AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
    </IfModule>
    
    <IfModule mod_expires.c>
        ExpiresActive On
        ExpiresByType image/jpg "access plus 1 month"
        ExpiresByType image/jpeg "access plus 1 month"
        ExpiresByType image/gif "access plus 1 month"
        ExpiresByType image/png "access plus 1 month"
        ExpiresByType text/css "access plus 1 week"
        ExpiresByType application/javascript "access plus 1 week"
    </IfModule>
</VirtualHost>
EOF
    
    a2ensite "${APP_NAME}-ssl.conf"
    a2enmod ssl
    
    # Redirect HTTP to HTTPS
    cat > "/etc/apache2/sites-available/${APP_NAME}-redirect.conf" << EOF
<VirtualHost *:${APACHE_PORT}>
    ServerName ${APP_DOMAIN}
    ServerAlias www.${APP_DOMAIN}
    
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</VirtualHost>
EOF
    
    a2ensite "${APP_NAME}-redirect.conf"
    systemctl restart apache2
    
    log_success "SSL configured with Let's Encrypt"
}

################################################################################
# Step 10: Setup Cron Jobs
################################################################################

step_setup_cron() {
    log "========================================"
    log "Step 10: Setup Cron Jobs"
    log "========================================"
    
    # Create cron job file
    cat > "/etc/cron.d/${APP_NAME}" << 'EOF'
# Association Management Application - Cron Jobs
# Run maintenance tasks

# Clean up old logs (daily at 2 AM)
0 2 * * * root find /var/www/association-app/logs -type f -name "*.log" -mtime +30 -delete

# Backup database (daily at 3 AM)
0 3 * * * root /usr/bin/mysqldump -u assoc_user -p${DB_PASS} association_db | gzip > /var/backups/association_db_$(date +\%Y\%m\%d).sql.gz

# Renew SSL certificate (Let's Encrypt - daily at 4 AM)
0 4 * * * root certbot renew --quiet

# Cleanup old temporary files (weekly on Sunday)
0 5 * * 0 root find /var/www/association-app/storage -type f -atime +7 -delete
EOF
    
    chmod 644 "/etc/cron.d/${APP_NAME}"
    
    log_success "Cron jobs configured"
}

################################################################################
# Step 11: Verification and Testing
################################################################################

step_verify() {
    log "========================================"
    log "Step 11: Verification and Testing"
    log "========================================"
    
    # Check PHP version
    php_version=$(php -v | head -n 1)
    log "PHP Version: $php_version"
    
    # Check Apache modules
    log "Apache modules check:"
    for module in rewrite ssl headers http2; do
        if apache2ctl -M 2>/dev/null | grep -q "$module"; then
            log_success "✓ mod_$module enabled"
        fi
    done
    
    # Check MariaDB
    if mysql -u "$DB_USER" -p"${DB_PASS}" "$DB_NAME" -e "SELECT 1;" > /dev/null 2>&1; then
        log_success "✓ MariaDB connection OK"
    else
        log_error "✗ MariaDB connection failed"
    fi
    
    # Check application files
    if [ -f "$APP_PATH/public/index.php" ]; then
        log_success "✓ Application files found"
    else
        log_error "✗ Application files missing"
    fi
    
    # Check file permissions
    if [ -w "$APP_PATH/logs" ]; then
        log_success "✓ Logs directory writable"
    else
        log_error "✗ Logs directory not writable"
    fi
    
    if [ -w "$APP_PATH/storage" ]; then
        log_success "✓ Storage directory writable"
    else
        log_error "✗ Storage directory not writable"
    fi
    
    log_success "Verification complete"
}

################################################################################
# Step 12: Summary and Next Steps
################################################################################

step_summary() {
    log "========================================"
    log "Deployment Summary"
    log "========================================"
    
    echo ""
    echo -e "${GREEN}=== Installation Complete ===${NC}"
    echo ""
    echo "Application Details:"
    echo "  Application Path: $APP_PATH"
    echo "  Domain: http://$APP_DOMAIN"
    if [ "$ENABLE_SSL" = "true" ]; then
        echo "  SSL Domain: https://$APP_DOMAIN"
    fi
    echo ""
    echo "Database Details:"
    echo "  Database Name: $DB_NAME"
    echo "  Database User: $DB_USER"
    echo "  Database Host: $DB_HOST"
    echo ""
    echo "Important Next Steps:"
    echo "  1. Configure DNS to point $APP_DOMAIN to this server IP"
    echo "  2. Update /app/config/database.php with database credentials:"
    echo "     - Host: $DB_HOST"
    echo "     - Database: $DB_NAME"
    echo "     - User: $DB_USER"
    echo "     - Password: Check the credentials file (see below)"
    echo "  3. Set secure ownership: sudo chown -R $APP_USER:$APP_GROUP $APP_PATH"
    echo "  4. Update environment variables if needed"
    echo "  5. Test application at http://$APP_DOMAIN"
    if [ "$ENABLE_SSL" = "true" ]; then
        echo "  6. Enable SSL redirection"
    fi
    echo ""
    echo "Useful Commands:"
    echo "  View Apache error log: tail -f /var/log/apache2/error.log"
    echo "  View application logs: tail -f $APP_PATH/logs/*.log"
    echo "  Check Apache status: systemctl status apache2"
    echo "  Check MariaDB status: systemctl status mariadb"
    echo "  Restart services: systemctl restart apache2 && systemctl restart mariadb"
    echo ""
    echo "Security:"
    echo "  - Root MariaDB password: ${DB_PASS}"
    echo "  - App DB password: ${DB_PASS}"
    echo "  - SAVE THESE CREDENTIALS SECURELY"
    echo "  - Run: sudo ufw enable && sudo ufw allow 22,80,443/tcp"
    echo ""
    echo "Logs:"
    echo "  Deployment log: $LOG_FILE"
    echo ""
}

################################################################################
# Main Execution
################################################################################

main() {
    log "========================================"
    log "Association Management App Deployment"
    log "Ubuntu Server 22.04 LTS"
    log "========================================"
    log "Start time: $(date)"
    echo ""
    
    check_root
    check_ubuntu
    
    # Execute deployment steps
    step_system_update
    step_install_apache
    step_install_php
    step_install_mariadb
    step_clone_app
    step_setup_permissions
    step_init_database
    step_configure_apache
    step_enable_ssl
    step_setup_cron
    step_verify
    step_summary
    
    log "========================================"
    log "End time: $(date)"
    log "Deployment completed successfully!"
    log "========================================"
}

# Run main function
main "$@"
