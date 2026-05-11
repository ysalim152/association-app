#!/bin/bash

################################################################################
# Verification Script - Check Deployment Status
# Comprehensive system and application health check
################################################################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

check_count=0
success_count=0

log() { echo -e "${BLUE}[INFO]${NC} $1"; }
pass() { ((success_count++)); echo -e "${GREEN}[PASS]${NC} $1"; }
fail() { ((check_count++)); echo -e "${RED}[FAIL]${NC} $1"; }
warn() { echo -e "${YELLOW}[WARN]${NC} $1"; }

echo ""
echo "╔════════════════════════════════════════════════════════════════════╗"
echo "║           DEPLOYMENT VERIFICATION & HEALTH CHECK                   ║"
echo "║              Association Management Application                    ║"
echo "╚════════════════════════════════════════════════════════════════════╝"
echo ""

# ============================================================================
# 1. System Requirements
# ============================================================================

echo "▶ System Requirements"
echo "────────────────────────────────────────────────────────────"

# Check OS
if grep -q "22.04" /etc/os-release; then
    pass "Ubuntu 22.04 LTS detected"
else
    warn "Not running Ubuntu 22.04 (deployment may have issues)"
fi

# Check RAM
ram_gb=$(free -g | awk 'NR==2 {print $2}')
if [ "$ram_gb" -ge 2 ]; then
    pass "Available RAM: ${ram_gb}GB (minimum 2GB met)"
else
    fail "Available RAM: ${ram_gb}GB (minimum 2GB required)"
fi

# Check disk space
disk_gb=$(df /var/www | awk 'NR==2 {print $4/1024/1024}')
if [ "${disk_gb%.*}" -ge 5 ]; then
    pass "Available disk space: ${disk_gb}GB"
else
    fail "Low disk space: ${disk_gb}GB (minimum 5GB recommended)"
fi

# Check internet connectivity
if ping -c 1 8.8.8.8 &> /dev/null; then
    pass "Internet connectivity: OK"
else
    warn "No internet connectivity (backups may fail)"
fi

echo ""

# ============================================================================
# 2. Apache Web Server
# ============================================================================

echo "▶ Apache Web Server"
echo "────────────────────────────────────────────────────────────"

# Check if Apache is installed
if command -v apache2 &> /dev/null; then
    pass "Apache installed"
else
    fail "Apache not installed"
fi

# Check if Apache is running
if systemctl is-active --quiet apache2; then
    pass "Apache service running"
else
    fail "Apache service not running"
fi

# Check Apache version
apache_version=$(apache2 -v 2>/dev/null | grep "Apache" | awk '{print $3}')
if [[ $apache_version > "2.4" ]]; then
    pass "Apache version: $apache_version (2.4+)"
else
    warn "Apache version: $apache_version (2.4+ recommended)"
fi

# Check required modules
for module in rewrite ssl headers http2 proxy proxy_fcgi; do
    if apache2ctl -M 2>/dev/null | grep -q "$module"; then
        pass "Apache module mod_$module enabled"
    else
        fail "Apache module mod_$module NOT enabled"
    fi
done

# Check Apache configuration
if apache2ctl configtest 2>&1 | grep -q "Syntax OK"; then
    pass "Apache configuration: Valid"
else
    fail "Apache configuration: INVALID"
    apache2ctl configtest
fi

# Check VirtualHost
if [ -f "/etc/apache2/sites-enabled/association-app.conf" ]; then
    pass "VirtualHost configuration found"
else
    warn "VirtualHost configuration not found"
fi

echo ""

# ============================================================================
# 3. PHP Runtime
# ============================================================================

echo "▶ PHP Runtime"
echo "────────────────────────────────────────────────────────────"

# Check PHP installation
if command -v php &> /dev/null; then
    pass "PHP installed"
else
    fail "PHP not installed"
fi

# Check PHP version
php_version=$(php -v 2>/dev/null | grep "PHP" | awk '{print $2}' | cut -d. -f1,2)
if [[ $php_version == "8.4" ]]; then
    pass "PHP version: $php_version (8.4 required)"
else
    warn "PHP version: $php_version (8.4 recommended)"
fi

# Check required extensions
required_extensions=("pdo" "mysqli" "mbstring" "curl" "gd" "json" "zip" "xml" "intl")
for ext in "${required_extensions[@]}"; do
    if php -m 2>/dev/null | grep -iq "$ext"; then
        pass "PHP extension: $ext"
    else
        fail "PHP extension missing: $ext"
    fi
done

# Check PHP-FPM
if systemctl is-active --quiet php8.4-fpm; then
    pass "PHP-FPM service running"
else
    warn "PHP-FPM service not running"
fi

# Check PHP configuration
php_memory=$(php -i | grep "memory_limit" | head -1 | awk '{print $NF}')
pass "PHP memory_limit: $php_memory"

php_upload=$(php -i | grep "upload_max_filesize" | head -1 | awk '{print $NF}')
pass "PHP upload_max_filesize: $php_upload"

echo ""

# ============================================================================
# 4. MariaDB Database
# ============================================================================

echo "▶ MariaDB Database"
echo "────────────────────────────────────────────────────────────"

# Check if MariaDB is installed
if command -v mysql &> /dev/null; then
    pass "MariaDB installed"
else
    fail "MariaDB not installed"
fi

# Check if MariaDB is running
if systemctl is-active --quiet mariadb; then
    pass "MariaDB service running"
else
    fail "MariaDB service not running"
fi

# Check MariaDB version
mysql_version=$(mysql --version 2>/dev/null | awk '{print $5}' | cut -d- -f1)
if [[ $mysql_version == "10.6"* ]]; then
    pass "MariaDB version: $mysql_version (10.6+)"
else
    warn "MariaDB version: $mysql_version (10.6+ recommended)"
fi

# Try to connect to database
if mysql -u assoc_user -ptest -e "SELECT 1;" 2>/dev/null || \
   mysql -u root -e "SELECT 1;" 2>/dev/null; then
    pass "MariaDB connection: OK"
else
    fail "MariaDB connection: FAILED (check credentials)"
fi

# Check if database exists
if mysql -u root -e "USE association_db;" 2>/dev/null || \
   mysql -u assoc_user -e "USE association_db;" 2>/dev/null; then
    pass "Application database: EXISTS"
else
    warn "Application database: NOT FOUND"
fi

echo ""

# ============================================================================
# 5. Application Files
# ============================================================================

echo "▶ Application Files"
echo "────────────────────────────────────────────────────────────"

APP_PATH="/var/www/association-app"

# Check if app directory exists
if [ -d "$APP_PATH" ]; then
    pass "Application directory: EXISTS"
else
    fail "Application directory: NOT FOUND at $APP_PATH"
fi

# Check main files
for file in "public/index.php" "app/config/config.php" "database/init.sql"; do
    if [ -f "$APP_PATH/$file" ]; then
        pass "File found: $file"
    else
        fail "File missing: $file"
    fi
done

# Check required directories
for dir in "logs" "storage" "public/uploads" "storage/cache" "storage/exports"; do
    if [ -d "$APP_PATH/$dir" ]; then
        pass "Directory exists: $dir"
    else
        warn "Directory missing: $dir"
    fi
done

# Check ownership
owner=$(ls -ld "$APP_PATH" | awk '{print $3}')
group=$(ls -ld "$APP_PATH" | awk '{print $4}')
if [ "$owner" = "www-data" ] && [ "$group" = "www-data" ]; then
    pass "Application ownership: www-data:www-data"
else
    warn "Application ownership: $owner:$group (should be www-data:www-data)"
fi

# Check permissions
if [ -w "$APP_PATH/logs" ]; then
    pass "Logs directory: WRITABLE"
else
    fail "Logs directory: NOT WRITABLE"
fi

if [ -w "$APP_PATH/storage" ]; then
    pass "Storage directory: WRITABLE"
else
    fail "Storage directory: NOT WRITABLE"
fi

if [ -w "$APP_PATH/public/uploads" ]; then
    pass "Uploads directory: WRITABLE"
else
    fail "Uploads directory: NOT WRITABLE"
fi

echo ""

# ============================================================================
# 6. Web Accessibility
# ============================================================================

echo "▶ Web Accessibility"
echo "────────────────────────────────────────────────────────────"

# Check if port 80 is open
if netstat -tln 2>/dev/null | grep -q ":80 " || ss -tln | grep -q ":80 "; then
    pass "HTTP port 80: LISTENING"
else
    warn "HTTP port 80: NOT LISTENING"
fi

# Check if port 443 is open
if netstat -tln 2>/dev/null | grep -q ":443 " || ss -tln | grep -q ":443 "; then
    pass "HTTPS port 443: LISTENING"
else
    warn "HTTPS port 443: NOT LISTENING"
fi

# Test local connectivity
if curl -s http://localhost/index.php > /dev/null 2>&1; then
    pass "Local web access: OK"
else
    warn "Local web access: FAILED"
fi

# Check SSL certificate (if applicable)
if [ -f "/etc/letsencrypt/live/association.local/fullchain.pem" ]; then
    expiry=$(openssl x509 -enddate -noout -in /etc/letsencrypt/live/association.local/fullchain.pem | cut -d= -f2)
    pass "SSL certificate found, expires: $expiry"
elif certbot certificates 2>/dev/null | grep -q "association"; then
    pass "SSL certificate: ACTIVE"
else
    warn "SSL certificate: NOT CONFIGURED"
fi

echo ""

# ============================================================================
# 7. Security Configuration
# ============================================================================

echo "▶ Security Configuration"
echo "────────────────────────────────────────────────────────────"

# Check firewall
if systemctl is-active --quiet ufw; then
    pass "Firewall (UFW): ENABLED"
    ufw_status=$(sudo ufw status | grep "22" || echo "SSH rule not found")
    pass "Firewall rules: Configured"
else
    warn "Firewall (UFW): NOT ENABLED"
fi

# Check if SSH is restricted
if sshd -T 2>/dev/null | grep -q "permitrootlogin no"; then
    pass "SSH root login: DISABLED"
else
    warn "SSH root login: NOT DISABLED (consider disabling)"
fi

# Check Apache security headers
if grep -q "X-Frame-Options" /etc/apache2/sites-enabled/association-app.conf 2>/dev/null; then
    pass "Security headers: CONFIGURED"
else
    warn "Security headers: NOT CONFIGURED"
fi

# Check PHP display_errors
php_display=$(php -i | grep "display_errors" | grep -i off)
if [ ! -z "$php_display" ]; then
    pass "PHP display_errors: OFF (production mode)"
else
    warn "PHP display_errors: ON (not production-safe)"
fi

echo ""

# ============================================================================
# 8. Backups
# ============================================================================

echo "▶ Backup System"
echo "────────────────────────────────────────────────────────────"

BACKUP_DIR="/var/backups/association-app"

# Check if backup directory exists
if [ -d "$BACKUP_DIR" ]; then
    pass "Backup directory: EXISTS"
else
    warn "Backup directory: NOT FOUND"
fi

# Check recent backups
db_backup_count=$(find "$BACKUP_DIR" -name "db_backup_*" -type f 2>/dev/null | wc -l)
if [ "$db_backup_count" -gt 0 ]; then
    pass "Database backups: $db_backup_count found"
    latest_backup=$(ls -t "$BACKUP_DIR"/db_backup_* 2>/dev/null | head -1)
    backup_age=$(( ($(date +%s) - $(stat -f%m "$latest_backup" 2>/dev/null || stat -c%Y "$latest_backup")) / 3600 ))
    if [ "$backup_age" -lt 25 ]; then
        pass "Latest backup: ${backup_age}h ago (recent)"
    else
        warn "Latest backup: ${backup_age}h ago (consider running backup)"
    fi
else
    warn "Database backups: NONE FOUND"
fi

# Check cron jobs
if [ -f "/etc/cron.d/association-app" ]; then
    pass "Cron jobs: CONFIGURED"
else
    warn "Cron jobs: NOT CONFIGURED"
fi

echo ""

# ============================================================================
# 9. Performance
# ============================================================================

echo "▶ Performance Metrics"
echo "────────────────────────────────────────────────────────────"

# CPU load
load=$(uptime | awk -F'load average:' '{print $2}' | awk '{print $1}')
pass "CPU load average: $load"

# Memory usage
mem_percent=$(free | awk 'NR==2 {print int($3/$2 * 100.0)}')
pass "Memory usage: ${mem_percent}%"

# Disk usage
disk_percent=$(df / | awk 'NR==2 {print int($3/$2 * 100.0)}')
if [ "$disk_percent" -lt 80 ]; then
    pass "Disk usage: ${disk_percent}% (healthy)"
else
    warn "Disk usage: ${disk_percent}% (consider cleanup)"
fi

# Check application size
app_size=$(du -sh "$APP_PATH" | awk '{print $1}')
pass "Application size: $app_size"

echo ""

# ============================================================================
# 10. Summary
# ============================================================================

echo "╔════════════════════════════════════════════════════════════════════╗"
echo "║                        HEALTH CHECK SUMMARY                        ║"
echo "╚════════════════════════════════════════════════════════════════════╝"
echo ""

if [ $check_count -eq 0 ]; then
    echo -e "${GREEN}✓ All checks passed!${NC}"
    echo ""
    echo "Your application is ready for production use."
else
    echo -e "${RED}⚠ $check_count checks failed${NC}"
    echo ""
    echo "Please review the failures above and run:"
    echo "  - For deployment issues: See DEPLOYMENT_GUIDE.md"
    echo "  - For quick help: ./quick-deploy.sh help"
fi

echo ""
echo "═══════════════════════════════════════════════════════════════════════"
echo "Timestamp: $(date)"
echo "═══════════════════════════════════════════════════════════════════════"
echo ""

# Exit with error code if there are failures
[ $check_count -eq 0 ] && exit 0 || exit 1
