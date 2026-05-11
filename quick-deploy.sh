#!/bin/bash

################################################################################
# Quick Deployment Reference Script
# Ubuntu 22.04 - Single Command Deployment
################################################################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

show_help() {
    cat << EOF
╔════════════════════════════════════════════════════════════════════════════╗
║                    DEPLOYMENT QUICK REFERENCE SCRIPT                       ║
║                    Association Management Application                      ║
║                       Ubuntu Server 22.04 LTS                              ║
╚════════════════════════════════════════════════════════════════════════════╝

USAGE: ./quick-deploy.sh [OPTION]

OPTIONS:
    help                Show this help message
    
    deploy              Full automated deployment
    deploy-http         Deploy with HTTP only (no SSL)
    deploy-ssl          Deploy with HTTPS (Let's Encrypt)
    
    config              Post-deployment configuration
    verify              Verify deployment status
    
    backup              Create manual backup
    restore             Restore from backup
    
    logs                View all logs in real-time
    logs-apache         View Apache logs
    logs-app            View application logs
    logs-mysql          View MySQL logs
    
    status              Show status of all services
    restart             Restart all services
    restart-apache      Restart Apache only
    restart-php         Restart PHP only
    restart-mysql       Restart MySQL only
    
    update              Update system and packages
    clean               Clean old logs and cache
    
    security            Show security checklist
    firewall            Setup firewall
    ssl-status          Check SSL certificate status
    
    info                Show server information
    help                Show this help message

EXAMPLES:

    # Full deployment with SSL
    sudo ./quick-deploy.sh deploy-ssl

    # Deploy without SSL
    sudo ./quick-deploy.sh deploy-http

    # View logs in real-time
    sudo ./quick-deploy.sh logs

    # Check service status
    sudo ./quick-deploy.sh status

    # Perform backup
    sudo ./quick-deploy.sh backup

    # Show security checklist
    sudo ./quick-deploy.sh security

EOF
}

# Check if running as root for deployment commands
check_root() {
    if [ "$1" = "deploy" ] || [ "$1" = "deploy-http" ] || [ "$1" = "deploy-ssl" ]; then
        if [[ $EUID -ne 0 ]]; then
            echo -e "${RED}✗ This command must be run as root${NC}"
            exit 1
        fi
    fi
}

log_info() { echo -e "${BLUE}ℹ $1${NC}"; }
log_success() { echo -e "${GREEN}✓ $1${NC}"; }
log_warning() { echo -e "${YELLOW}⚠ $1${NC}"; }
log_error() { echo -e "${RED}✗ $1${NC}"; }

# Deploy functions
cmd_deploy() {
    log_info "Starting deployment with HTTP + SSL (Let's Encrypt)"
    log_warning "You will be prompted for domain and email"
    
    read -p "Enter your domain (e.g., association.com): " domain
    read -p "Enter your email (for Let's Encrypt): " email
    read -p "Enter Git repository URL: " git_repo
    
    ./deploy.sh
    
    log_success "Deployment completed! Run './quick-deploy.sh config' for post-deployment setup."
}

cmd_deploy_http() {
    log_info "Starting deployment with HTTP only (no SSL)"
    
    read -p "Enter your domain (e.g., association.local): " domain
    read -p "Enter Git repository URL: " git_repo
    
    ENABLE_SSL=false APP_DOMAIN="$domain" GIT_REPO="$git_repo" ./deploy.sh
    
    log_success "HTTP deployment completed!"
}

cmd_deploy_ssl() {
    log_info "Starting deployment with HTTPS (Let's Encrypt)"
    
    read -p "Enter your domain (e.g., association.com): " domain
    read -p "Enter your email (for Let's Encrypt): " email
    read -p "Enter Git repository URL: " git_repo
    
    ENABLE_SSL=true APP_DOMAIN="$domain" SSL_EMAIL="$email" GIT_REPO="$git_repo" ./deploy.sh
    
    log_success "HTTPS deployment completed!"
}

# Configuration
cmd_config() {
    log_info "Running post-deployment configuration..."
    sudo ./configure-env.sh
}

# Verification
cmd_verify() {
    echo ""
    echo "═══════════════════════════════════════════════════════════"
    echo "            DEPLOYMENT VERIFICATION REPORT"
    echo "═══════════════════════════════════════════════════════════"
    echo ""
    
    # Check Apache
    if systemctl is-active --quiet apache2; then
        log_success "Apache2 running"
        apache2 -v | head -n 1
    else
        log_error "Apache2 not running"
    fi
    
    echo ""
    
    # Check PHP
    if command -v php &> /dev/null; then
        log_success "PHP installed"
        php -v | head -n 1
        echo "Modules: $(php -m | tr '\n' ',' | sed 's/,/, /g' | head -c 50)..."
    else
        log_error "PHP not found"
    fi
    
    echo ""
    
    # Check MariaDB
    if systemctl is-active --quiet mariadb; then
        log_success "MariaDB running"
        mysql --version
    else
        log_error "MariaDB not running"
    fi
    
    echo ""
    
    # Check application files
    if [ -f "/var/www/association-app/public/index.php" ]; then
        log_success "Application files present"
    else
        log_error "Application files not found"
    fi
    
    echo ""
    
    # Check file permissions
    if [ -w "/var/www/association-app/logs" ]; then
        log_success "Logs directory writable"
    else
        log_error "Logs directory not writable"
    fi
    
    if [ -w "/var/www/association-app/storage" ]; then
        log_success "Storage directory writable"
    else
        log_error "Storage directory not writable"
    fi
    
    echo ""
    echo "═══════════════════════════════════════════════════════════"
    echo ""
}

# Backup
cmd_backup() {
    log_info "Creating backup..."
    sudo ./backup.sh
}

# Restore
cmd_restore() {
    echo ""
    echo "Available backups:"
    ls -lh /var/backups/association-app/db_backup_* 2>/dev/null || log_warning "No backups found"
    echo ""
    read -p "Enter backup filename to restore: " backup_file
    
    if [ -f "/var/backups/association-app/$backup_file" ]; then
        log_warning "This will restore the database. Continue? (yes/no)"
        read confirm
        if [ "$confirm" = "yes" ]; then
            gunzip -c "/var/backups/association-app/$backup_file" | mysql -u assoc_user -p association_db
            log_success "Database restored"
        fi
    else
        log_error "Backup file not found"
    fi
}

# Logs
cmd_logs() {
    log_info "Showing all logs (press Ctrl+C to exit)"
    tail -f /var/log/apache2/error.log /var/www/association-app/logs/*.log 2>/dev/null
}

cmd_logs_apache() {
    log_info "Apache error log (press Ctrl+C to exit)"
    tail -f /var/log/apache2/error.log
}

cmd_logs_app() {
    log_info "Application logs (press Ctrl+C to exit)"
    tail -f /var/www/association-app/logs/*.log
}

cmd_logs_mysql() {
    log_info "MySQL logs"
    sudo tail -100 /var/log/mysql/error.log
}

# Service management
cmd_status() {
    echo ""
    echo "Service Status:"
    echo "───────────────────────────────────────"
    
    systemctl is-active apache2 > /dev/null && log_success "Apache2" || log_error "Apache2"
    systemctl is-active php8.4-fpm > /dev/null && log_success "PHP-FPM 8.4" || log_error "PHP-FPM 8.4"
    systemctl is-active mariadb > /dev/null && log_success "MariaDB" || log_error "MariaDB"
    
    echo ""
}

cmd_restart() {
    log_info "Restarting all services..."
    sudo systemctl restart apache2 php8.4-fpm mariadb
    log_success "Services restarted"
}

cmd_restart_apache() {
    log_info "Restarting Apache..."
    sudo systemctl restart apache2
    log_success "Apache restarted"
}

cmd_restart_php() {
    log_info "Restarting PHP-FPM..."
    sudo systemctl restart php8.4-fpm
    log_success "PHP-FPM restarted"
}

cmd_restart_mysql() {
    log_info "Restarting MariaDB..."
    sudo systemctl restart mariadb
    log_success "MariaDB restarted"
}

# Maintenance
cmd_update() {
    log_info "Updating system..."
    sudo apt update
    sudo apt upgrade -y
    log_success "System updated"
}

cmd_clean() {
    log_info "Cleaning old files..."
    sudo find /var/www/association-app/logs -name "*.log" -mtime +30 -delete
    sudo rm -rf /var/www/association-app/storage/cache/*
    log_success "Cleanup complete"
}

# Security
cmd_security() {
    cat << 'EOF'

╔════════════════════════════════════════════════════════════════════════════╗
║                     SECURITY CHECKLIST                                     ║
╚════════════════════════════════════════════════════════════════════════════╝

FIREWALL:
    ✓ UFW installed and enabled
    ✓ SSH (port 22) allowed
    ✓ HTTP (port 80) allowed
    ✓ HTTPS (port 443) allowed
    ✓ Unused ports closed

SSL/TLS CERTIFICATE:
    ✓ HTTPS enabled
    ✓ Valid SSL certificate from Let's Encrypt
    ✓ Certificate auto-renewal configured
    ✓ HTTP redirects to HTTPS

PHP SECURITY:
    ✓ display_errors = Off
    ✓ expose_php = Off
    ✓ Disabled dangerous functions
    ✓ Updated PHP version

APACHE SECURITY:
    ✓ ServerTokens set to Prod
    ✓ Security headers configured
    ✓ Unnecessary modules disabled
    ✓ .htaccess enabled for URL rewriting

FILE PERMISSIONS:
    ✓ Application owned by www-data
    ✓ /logs directory (775 permissions)
    ✓ /storage directory (775 permissions)
    ✓ Config files (640 permissions)
    ✓ /public directory accessible

DATABASE SECURITY:
    ✓ MariaDB root password changed
    ✓ Anonymous users removed
    ✓ Remote root login disabled
    ✓ Dedicated app database user
    ✓ App user has limited privileges

SSH SECURITY:
    □ SSH port changed from 22
    □ Root login disabled
    □ Password authentication disabled
    □ Public key authentication only
    □ SSH banner configured

BACKUPS:
    ✓ Daily database backups
    ✓ Daily file backups
    ✓ 30-day retention policy
    ✓ Backup verification tested

MONITORING:
    □ Log monitoring setup
    □ Automated alerts configured
    □ Uptime monitoring active
    □ Performance monitoring setup

RUN: sudo ./quick-deploy.sh firewall   [To setup firewall]

EOF
}

cmd_firewall() {
    log_info "Setting up firewall with UFW..."
    
    sudo apt install -y ufw
    
    # Default policies
    sudo ufw default deny incoming
    sudo ufw default allow outgoing
    
    # Allow specific ports
    sudo ufw allow 22/tcp    # SSH
    sudo ufw allow 80/tcp    # HTTP
    sudo ufw allow 443/tcp   # HTTPS
    
    # Enable firewall
    sudo ufw enable
    
    log_success "Firewall configured"
    sudo ufw status
}

cmd_ssl_status() {
    log_info "Checking SSL certificates..."
    
    if command -v certbot &> /dev/null; then
        sudo certbot certificates
    else
        log_warning "Certbot not installed"
    fi
}

# System info
cmd_info() {
    echo ""
    echo "═══════════════════════════════════════════════════════════"
    echo "                    SERVER INFORMATION"
    echo "═══════════════════════════════════════════════════════════"
    echo ""
    
    echo "OS Information:"
    lsb_release -a | grep -E "Description|Release"
    
    echo ""
    echo "Hardware:"
    echo "  CPU Cores: $(nproc)"
    echo "  RAM: $(free -h | awk 'NR==2 {print $2}')"
    echo "  Disk: $(df -h / | awk 'NR==2 {print $2}')"
    
    echo ""
    echo "Software Versions:"
    echo "  Apache: $(apache2 -v | grep "Apache" | awk '{print $3}')"
    echo "  PHP: $(php -v | grep "PHP" | awk '{print $2}')"
    echo "  MariaDB: $(mysql --version | awk '{print $3}')"
    
    echo ""
    echo "Application Location:"
    echo "  Path: /var/www/association-app"
    echo "  Size: $(du -sh /var/www/association-app | awk '{print $1}')"
    
    echo ""
    echo "Network:"
    echo "  IP Address: $(hostname -I)"
    echo "  Hostname: $(hostname)"
    
    echo ""
    echo "═══════════════════════════════════════════════════════════"
    echo ""
}

# Main
main() {
    case "${1:-help}" in
        deploy)
            check_root "$1"
            cmd_deploy
            ;;
        deploy-http)
            check_root "$1"
            cmd_deploy_http
            ;;
        deploy-ssl)
            check_root "$1"
            cmd_deploy_ssl
            ;;
        config)
            cmd_config
            ;;
        verify)
            cmd_verify
            ;;
        backup)
            cmd_backup
            ;;
        restore)
            cmd_restore
            ;;
        logs)
            cmd_logs
            ;;
        logs-apache)
            cmd_logs_apache
            ;;
        logs-app)
            cmd_logs_app
            ;;
        logs-mysql)
            cmd_logs_mysql
            ;;
        status)
            cmd_status
            ;;
        restart)
            cmd_restart
            ;;
        restart-apache)
            cmd_restart_apache
            ;;
        restart-php)
            cmd_restart_php
            ;;
        restart-mysql)
            cmd_restart_mysql
            ;;
        update)
            cmd_update
            ;;
        clean)
            cmd_clean
            ;;
        security)
            cmd_security
            ;;
        firewall)
            check_root "$1"
            cmd_firewall
            ;;
        ssl-status)
            cmd_ssl_status
            ;;
        info)
            cmd_info
            ;;
        help|--help|-h)
            show_help
            ;;
        *)
            log_error "Unknown command: $1"
            show_help
            exit 1
            ;;
    esac
}

# Run main
main "$@"
