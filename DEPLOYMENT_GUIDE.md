# 🚀 Complete Deployment Guide - Ubuntu Server 22.04

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Quick Start](#quick-start)
3. [Deployment Process](#deployment-process)
4. [Post-Deployment Configuration](#post-deployment-configuration)
5. [Verification](#verification)
6. [Troubleshooting](#troubleshooting)
7. [Maintenance](#maintenance)
8. [Security Hardening](#security-hardening)

---

## Prerequisites

### System Requirements

- **OS**: Ubuntu Server 22.04 LTS
- **RAM**: Minimum 2GB (4GB recommended)
- **Storage**: 20GB minimum (50GB+ for production)
- **Network**: Public IP or domain name
- **Access**: SSH access with sudo privileges

### Required Software

- Git (for cloning repository)
- OpenSSL (for SSL certificates and random passwords)
- curl/wget (for downloading packages)

### Pre-Deployment Checklist

- [ ] Ubuntu 22.04 LTS fresh installation
- [ ] SSH access configured
- [ ] sudo privileges available
- [ ] Git repository URL available
- [ ] Domain name (optional but recommended)
- [ ] Email account for SSL certificates (Let's Encrypt)

---

## Quick Start

### 1. Clone the repository on your local machine

```bash
git clone <your-repo-url> association-app
cd association-app
```

### 2. Make deployment script executable

```bash
chmod +x deploy.sh
chmod +x configure-env.sh
chmod +x backup.sh
```

### 3. Copy scripts to your server

```bash
scp -r deploy.sh configure-env.sh backup.sh root@your-server:/root/
scp -r . root@your-server:/var/www/  # or clone directly on server
```

### 4. Connect to server and run deployment

```bash
ssh root@your-server
cd /root
./deploy.sh
```

---

## Deployment Process

### What the `deploy.sh` Script Does

The main deployment script automates all steps:

```
1. System Update & Base Dependencies
   ├─ apt update/upgrade
   ├─ Install curl, wget, git, unzip
   └─ Install essential development tools

2. Apache 2.4 Installation
   ├─ Install Apache web server
   ├─ Enable required modules (rewrite, ssl, headers, http2)
   └─ Configure security settings

3. PHP 8.4 Installation
   ├─ Add PHP repository
   ├─ Install PHP and extensions (mysqli, curl, gd, etc.)
   ├─ Configure php.ini for production
   ├─ Enable OPcache for performance
   └─ Enable PHP-FPM

4. MariaDB 10.6 Installation
   ├─ Add MariaDB repository
   ├─ Install MariaDB server
   ├─ Secure installation (password, remove test dbs)
   └─ Create application database and user

5. Clone Application
   ├─ Clone from Git repository
   ├─ Checkout specified branch
   └─ Backup if directory already exists

6. Setup Permissions
   ├─ Create required directories (logs, storage, uploads)
   ├─ Set proper ownership (www-data)
   └─ Set correct permissions (755/775)

7. Database Initialization
   ├─ Import database schema from init.sql
   ├─ Create tables and relationships
   └─ Set proper collation (utf8mb4)

8. Apache VirtualHost Configuration
   ├─ Create VirtualHost configuration
   ├─ Enable URL rewriting
   ├─ Add security headers
   ├─ Enable compression and caching
   └─ Test and restart Apache

9. SSL Configuration (Optional)
   ├─ Install Certbot
   ├─ Obtain Let's Encrypt certificate
   ├─ Create HTTPS VirtualHost
   ├─ Setup HTTP to HTTPS redirect
   └─ Configure automatic renewal

10. Cron Jobs Setup
    ├─ Log cleanup (daily)
    ├─ Database backup (daily)
    ├─ SSL renewal (daily)
    └─ Temp file cleanup (weekly)

11. Verification & Testing
    ├─ Check PHP version and modules
    ├─ Verify Apache modules
    ├─ Test database connection
    ├─ Verify file permissions
    └─ Display summary report

12. Summary & Next Steps
    └─ Display credentials and next actions
```

### Running the Deployment

```bash
# Basic deployment (HTTP only)
sudo ./deploy.sh

# With environment variables for customization
sudo APP_DOMAIN=myapp.com GIT_REPO=https://github.com/user/repo.git ./deploy.sh

# With SSL enabled
sudo ENABLE_SSL=true SSL_EMAIL=admin@example.com ./deploy.sh
```

### Environment Variables

You can customize the deployment by setting variables:

```bash
# Application settings
APP_NAME="association-app"              # Application name
APP_DOMAIN="association.local"          # Domain name
APP_USER="www-data"                     # Web server user
APP_GROUP="www-data"                    # Web server group

# Git settings
GIT_REPO="https://github.com/..."       # Repository URL
GIT_BRANCH="main"                       # Git branch to clone

# Database settings
DB_NAME="association_db"                # Database name
DB_USER="assoc_user"                    # Database user
DB_PASS="auto-generated"                # Database password (auto-generated if not set)

# PHP and Apache
PHP_VERSION="8.4"                       # PHP version
APACHE_PORT="80"                        # HTTP port
APACHE_HTTPS_PORT="443"                 # HTTPS port

# SSL (Let's Encrypt)
ENABLE_SSL="false"                      # Enable SSL
SSL_EMAIL="admin@example.com"           # Email for SSL certificate
```

---

## Post-Deployment Configuration

### Step 1: Configure Environment Variables

```bash
sudo ./configure-env.sh
```

This script will:
- Create `.env` file with database credentials
- Setup environment-specific configurations
- Verify directory permissions
- Test database connectivity

### Step 2: Update Application Configuration

Edit the database configuration:

```bash
sudo nano /var/www/association-app/app/config/database.php
```

Ensure these values match your setup:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'association_db');
define('DB_USER', 'assoc_user');
define('DB_PASS', 'your-secure-password');
```

### Step 3: Setup Initial Admin User

Connect to the database and add an admin user:

```bash
mysql -u assoc_user -p association_db
```

```sql
INSERT INTO users (email, password_hash, role, is_active) VALUES (
    'admin@example.com',
    '$2y$10$...', -- bcrypt hash of password
    'admin',
    1
);
```

Or use the application's admin interface if available.

### Step 4: Configure Email (Optional)

Edit the email configuration:

```bash
sudo nano /var/www/association-app/.env
```

Update SMTP settings:

```
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=app-specific-password
```

### Step 5: Setup DNS

Point your domain to the server IP:

```
association.com    A    192.168.1.100
www.association.com CNAME association.com
```

---

## Verification

### Check System Status

```bash
# Apache status
sudo systemctl status apache2

# PHP-FPM status
sudo systemctl status php8.4-fpm

# MariaDB status
sudo systemctl status mariadb

# All services
sudo systemctl status apache2 && sudo systemctl status php8.4-fpm && sudo systemctl status mariadb
```

### Test Database Connection

```bash
mysql -h localhost -u assoc_user -p association_db
```

### Test Web Application

```bash
# From server
curl http://localhost/

# From your computer
curl http://your-domain.com/

# Check specific endpoint
curl -I http://your-domain.com/
```

### Check Logs

```bash
# Apache error log
sudo tail -f /var/log/apache2/error.log

# Apache access log
sudo tail -f /var/log/apache2/access.log

# Application logs
sudo tail -f /var/www/association-app/logs/*.log

# PHP-FPM log
sudo tail -f /var/log/php8.4-fpm.log

# Deployment log
sudo cat /var/log/association-app-deploy.log
```

### Verify File Permissions

```bash
# Check ownership
ls -la /var/www/association-app/ | head -20

# Check specific directories
ls -la /var/www/association-app/logs/
ls -la /var/www/association-app/storage/
ls -la /var/www/association-app/public/uploads/
```

---

## Troubleshooting

### Apache Shows 403 Forbidden

**Problem**: Getting 403 error when accessing the site.

**Solutions**:

```bash
# Check file permissions
sudo chown -R www-data:www-data /var/www/association-app
sudo chmod -R 755 /var/www/association-app

# Check Apache configuration
sudo apache2ctl configtest

# Check logs
sudo tail -f /var/log/apache2/error.log

# Ensure index.php exists
ls -la /var/www/association-app/public/index.php
```

### Database Connection Error

**Problem**: "Can't connect to MySQL server"

**Solutions**:

```bash
# Check if MariaDB is running
sudo systemctl status mariadb

# Start if not running
sudo systemctl start mariadb

# Verify user credentials
mysql -u assoc_user -p association_db -e "SELECT 1;"

# Check if user has correct permissions
mysql -u root -p -e "SHOW GRANTS FOR 'assoc_user'@'localhost';"

# Reset user password if needed
mysql -u root -p -e "ALTER USER 'assoc_user'@'localhost' IDENTIFIED BY 'newpassword';"
```

### PHP Module Not Loaded

**Problem**: "Fatal error: Call to undefined function..."

**Solutions**:

```bash
# Check loaded modules
php -m | grep -i pdo

# Check installed extensions
php -m

# Install missing extension (example: PDO MySQL)
sudo apt install php8.4-mysql

# Restart Apache
sudo systemctl restart apache2
```

### Permission Denied on Upload

**Problem**: Files can't be uploaded to `/public/uploads`

**Solutions**:

```bash
# Fix permissions
sudo chmod 775 /var/www/association-app/public/uploads
sudo chown www-data:www-data /var/www/association-app/public/uploads

# Check if directory is writable
touch /var/www/association-app/public/uploads/test.txt
rm /var/www/association-app/public/uploads/test.txt
```

### SSL Certificate Issues

**Problem**: SSL certificate not working or "untrusted certificate"

**Solutions**:

```bash
# Check certificate
sudo certbot certificates

# Renew certificate
sudo certbot renew --dry-run

# Check certificate expiry
openssl s_client -connect association.com:443 -servername association.com | grep -A2 "validity"

# Force renewal
sudo certbot renew --force-renewal
```

### High Memory Usage

**Problem**: Server consuming too much memory

**Solutions**:

```bash
# Check memory usage
free -h
top

# Check PHP settings
grep memory_limit /etc/php/8.4/apache2/php.ini

# Adjust if needed
sudo sed -i 's/memory_limit = 256M/memory_limit = 512M/' /etc/php/8.4/apache2/php.ini

# Restart services
sudo systemctl restart apache2
```

---

## Maintenance

### Regular Backups

```bash
# Manual backup
sudo ./backup.sh

# View backup directory
ls -lah /var/backups/association-app/

# Restore database backup
gunzip -c /var/backups/association-app/db_backup_*.sql.gz | mysql -u assoc_user -p association_db

# Restore files backup
sudo tar -xzf /var/backups/association-app/files_backup_*.tar.gz -C /var/www/
```

### Monitor Disk Space

```bash
# Check disk usage
df -h

# Check application size
du -sh /var/www/association-app

# Clean old logs
sudo find /var/www/association-app/logs -name "*.log" -mtime +30 -delete

# Clean cache
sudo rm -rf /var/www/association-app/storage/cache/*
```

### Update System

```bash
# Check for updates
sudo apt update
sudo apt list --upgradable

# Install updates
sudo apt upgrade -y

# Update PHP packages
sudo apt install --only-upgrade php8.4*

# Restart services
sudo systemctl restart apache2 php8.4-fpm mariadb
```

### Monitor Logs

```bash
# Real-time error monitoring
sudo tail -f /var/log/apache2/error.log

# Last 100 errors
sudo tail -100 /var/log/apache2/error.log

# Search for specific errors
sudo grep "error" /var/log/apache2/error.log | tail -20

# Check for 404s
sudo grep "404" /var/log/apache2/access.log | wc -l
```

---

## Security Hardening

### Firewall Setup

```bash
# Install UFW (Uncomplicated Firewall)
sudo apt install ufw

# Enable firewall
sudo ufw enable

# Allow SSH
sudo ufw allow 22/tcp

# Allow HTTP
sudo ufw allow 80/tcp

# Allow HTTPS
sudo ufw allow 443/tcp

# Check firewall status
sudo ufw status
```

### SSL/TLS Certificate

```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache

# Obtain certificate
sudo certbot certonly --apache -d your-domain.com -d www.your-domain.com

# Automatic renewal (check cron jobs)
sudo certbot renew --dry-run
```

### Secure SSH

```bash
# Edit SSH configuration
sudo nano /etc/ssh/sshd_config

# Recommended settings:
# Port 2222 (change from default 22)
# PermitRootLogin no
# PasswordAuthentication no
# PubkeyAuthentication yes

# Restart SSH
sudo systemctl restart ssh
```

### Hide PHP Version

```bash
# Edit PHP configuration
sudo nano /etc/php/8.4/apache2/php.ini

# Set:
expose_php = Off

# Restart Apache
sudo systemctl restart apache2
```

### Setup Fail2Ban (Optional)

```bash
# Install Fail2Ban
sudo apt install fail2ban

# Start and enable
sudo systemctl start fail2ban
sudo systemctl enable fail2ban

# Create Apache jail config
sudo nano /etc/fail2ban/jail.d/apache.conf
```

---

## Quick Reference Commands

```bash
# Restart all services
sudo systemctl restart apache2 php8.4-fpm mariadb

# Start services
sudo systemctl start apache2 php8.4-fpm mariadb

# Stop services
sudo systemctl stop apache2 php8.4-fpm mariadb

# Check Apache syntax
sudo apache2ctl configtest

# Reload Apache without stopping
sudo systemctl reload apache2

# View Apache modules
apache2ctl -M

# Check PHP version
php -v

# Check installed PHP modules
php -m

# Check MariaDB version
mysql --version

# Connect to database
mysql -u root -p

# Export database
mysqldump -u user -p database_name > backup.sql

# Import database
mysql -u user -p database_name < backup.sql

# Check running processes
ps aux | grep -E "apache|php|mysql"

# Monitor real-time processes
htop

# Check open ports
sudo netstat -tulpn | grep LISTEN

# Check DNS resolution
nslookup your-domain.com
dig your-domain.com
```

---

## Support & Further Help

- **Apache Documentation**: https://httpd.apache.org/
- **PHP Documentation**: https://www.php.net/
- **MariaDB Documentation**: https://mariadb.com/docs/
- **Let's Encrypt**: https://letsencrypt.org/
- **Ubuntu Documentation**: https://help.ubuntu.com/

---

**Last Updated**: May 11, 2026  
**Version**: 1.0.0
