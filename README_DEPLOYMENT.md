# 🚀 Deployment Scripts - Association Management App

Complete, production-ready deployment solution for Ubuntu Server 22.04 LTS.

## 📦 What's Included

This deployment package contains everything needed to deploy your Association Management application:

### Core Scripts

1. **`deploy.sh`** - Main deployment script
   - Fully automated deployment process
   - Installs and configures all components
   - Production-ready with security hardening
   - ~500 lines of professional bash code

2. **`configure-env.sh`** - Post-deployment configuration
   - Interactive environment setup
   - Database connection testing
   - File permission verification
   - .env file generation

3. **`backup.sh`** - Automated backup tool
   - Daily database backups
   - File backups with exclusions
   - Automatic old backup cleanup
   - Restore instructions

4. **`quick-deploy.sh`** - Quick reference and helper
   - Simplified deployment commands
   - Service management shortcuts
   - Log viewing and monitoring
   - Security checklist

### Documentation

1. **`DEPLOYMENT_GUIDE.md`** - Comprehensive deployment guide
   - Step-by-step instructions
   - Troubleshooting section
   - Maintenance procedures
   - Security hardening guide

2. **`README_DEPLOYMENT.md`** (this file)
   - Quick overview
   - Getting started guide
   - Command reference

---

## 🎯 Quick Start

### Prerequisites

```bash
# Ubuntu 22.04 LTS server with:
- SSH access
- sudo privileges
- git installed
- Internet connectivity
```

### Three-Step Deployment

#### Step 1: Prepare Scripts

```bash
# Clone your repository or upload files
git clone <your-repo-url> association-app
cd association-app

# Make scripts executable
chmod +x deploy.sh configure-env.sh backup.sh quick-deploy.sh
```

#### Step 2: Run Deployment

```bash
# Full deployment with SSL (recommended)
sudo ./deploy.sh

# Or use quick deployment
sudo ./quick-deploy.sh deploy-ssl

# For HTTP only
sudo ./deploy.sh deploy-http
```

#### Step 3: Configure Application

```bash
# Post-deployment setup
sudo ./quick-deploy.sh config

# Or manually
sudo ./configure-env.sh
```

---

## 📋 What Gets Installed

### System Level
- [ ] Ubuntu 22.04 LTS updates
- [ ] Essential development tools
- [ ] Git, curl, wget, unzip

### Web Server
- [ ] Apache 2.4.67+
- [ ] Apache modules (rewrite, ssl, headers, http2, proxy, proxy_fcgi)
- [ ] Security configuration
- [ ] URL rewriting (mod_rewrite)

### PHP Runtime
- [ ] PHP 8.4.21+
- [ ] PHP-FPM (FastCGI)
- [ ] Essential extensions:
  - pdo, pdo-mysql
  - mbstring, curl, gd
  - json, zip, xml, intl
  - opcache, bcmath
- [ ] OPcache optimization
- [ ] Production php.ini settings

### Database
- [ ] MariaDB 10.6.23+
- [ ] Database creation
- [ ] Secure installation
- [ ] User with limited privileges
- [ ] Schema import

### Application
- [ ] Git repository cloning
- [ ] Directory structure
- [ ] File permissions
- [ ] Storage directories
- [ ] Log directories

### Security
- [ ] HTTPS with Let's Encrypt (optional)
- [ ] Security headers
- [ ] Firewall rules
- [ ] File permissions
- [ ] Database hardening

### Maintenance
- [ ] Daily backups (database + files)
- [ ] Log rotation
- [ ] SSL renewal automation
- [ ] Cron job setup

---

## 🛠️ Commands Reference

### Main Deployment

```bash
# Full automated deployment
sudo ./deploy.sh

# With custom domain
sudo APP_DOMAIN=myapp.com ./deploy.sh

# With custom database password
sudo DB_PASS=MySecurePass123 ./deploy.sh

# With SSL enabled
sudo ENABLE_SSL=true SSL_EMAIL=admin@example.com ./deploy.sh

# With custom git repository
sudo GIT_REPO=https://github.com/user/repo.git ./deploy.sh
```

### Quick Deploy Helper

```bash
# Show all available commands
./quick-deploy.sh help

# Deploy with HTTPS
sudo ./quick-deploy.sh deploy-ssl

# Deploy with HTTP
sudo ./quick-deploy.sh deploy-http

# Configure after deployment
sudo ./quick-deploy.sh config

# Verify installation
./quick-deploy.sh verify

# Check service status
./quick-deploy.sh status

# View logs
./quick-deploy.sh logs
sudo ./quick-deploy.sh logs-apache
sudo ./quick-deploy.sh logs-app

# Service management
sudo ./quick-deploy.sh restart
sudo ./quick-deploy.sh restart-apache
sudo ./quick-deploy.sh restart-php
sudo ./quick-deploy.sh restart-mysql

# Backup and restore
sudo ./quick-deploy.sh backup
sudo ./quick-deploy.sh restore

# Maintenance
sudo ./quick-deploy.sh update
sudo ./quick-deploy.sh clean

# Security
./quick-deploy.sh security
sudo ./quick-deploy.sh firewall
./quick-deploy.sh ssl-status

# Information
./quick-deploy.sh info
```

---

## 📊 Deployment Process Overview

```
┌─ System Setup ──────────────────────────────────────┐
│ 1. Update system & install base packages            │
│ 2. Install Apache 2.4 with required modules        │
│ 3. Install PHP 8.4 with extensions                 │
│ 4. Install MariaDB 10.6                            │
└─────────────────────────────────────────────────────┘
            ↓
┌─ Application Setup ─────────────────────────────────┐
│ 5. Clone application from Git                      │
│ 6. Setup directories and permissions               │
│ 7. Initialize database schema                      │
│ 8. Configure Apache VirtualHost                    │
└─────────────────────────────────────────────────────┘
            ↓
┌─ Security & Automation ─────────────────────────────┐
│ 9. Setup SSL (optional - Let's Encrypt)            │
│ 10. Configure cron jobs for backups & maintenance  │
│ 11. Verify installation                            │
│ 12. Display credentials & next steps               │
└─────────────────────────────────────────────────────┘
```

---

## 🔒 Security Features

The deployment script includes comprehensive security:

### Server Security
- ✅ Automatic firewall configuration
- ✅ SSH hardening recommendations
- ✅ Fail2Ban setup (optional)
- ✅ Security headers (X-Frame-Options, CSP, etc.)

### Web Application
- ✅ HTTPS with Let's Encrypt certificates
- ✅ Automatic SSL renewal
- ✅ HTTP to HTTPS redirect
- ✅ Cookie security settings

### Database
- ✅ Root password protection
- ✅ Anonymous users removed
- ✅ Remote root login disabled
- ✅ Application user with limited privileges
- ✅ UTF-8MB4 encoding for full Unicode support

### File Permissions
- ✅ Application owned by www-data
- ✅ Logs writable (775)
- ✅ Storage writable (775)
- ✅ Config files protected (640)
- ✅ Denied access to sensitive directories

### PHP Security
- ✅ display_errors = Off
- ✅ expose_php = Off
- ✅ Increased timeouts and file uploads
- ✅ OPcache enabled for performance

---

## 📝 Post-Deployment Checklist

After deployment completes:

- [ ] DNS configured (pointing domain to server IP)
- [ ] Environment variables configured (.env file)
- [ ] Database credentials verified
- [ ] Email configuration tested
- [ ] SSL certificate working (if HTTPS enabled)
- [ ] Application accessible from browser
- [ ] Admin user created
- [ ] File uploads working
- [ ] Backups verified
- [ ] Firewall enabled and configured
- [ ] Monitoring configured

---

## 🆘 Troubleshooting

### Common Issues

**Application shows 403 Forbidden**
```bash
sudo chown -R www-data:www-data /var/www/association-app
sudo chmod -R 755 /var/www/association-app
```

**Database connection error**
```bash
mysql -u assoc_user -p association_db -e "SELECT 1;"
```

**Can't upload files**
```bash
sudo chmod 775 /var/www/association-app/public/uploads
sudo chown www-data:www-data /var/www/association-app/public/uploads
```

**Apache shows 500 error**
```bash
# Check logs
tail -50 /var/log/apache2/error.log

# Check Apache config
sudo apache2ctl configtest

# Restart Apache
sudo systemctl restart apache2
```

### Getting Help

1. **Check logs**: `./quick-deploy.sh logs`
2. **Verify status**: `./quick-deploy.sh status`
3. **Review guide**: See `DEPLOYMENT_GUIDE.md` troubleshooting section

---

## 📅 Maintenance Schedule

### Daily
- ✅ Automatic database backup (3 AM)
- ✅ Automatic SSL renewal (4 AM)
- ✅ Log cleanup for files > 30 days old

### Weekly
- ⏰ Check disk usage
- ⏰ Review error logs
- ⏰ Verify backup integrity

### Monthly
- ⏰ Update system packages
- ⏰ Review security logs
- ⏰ Test backup restoration

### Quarterly
- ⏰ Major version updates
- ⏰ Security audit
- ⏰ Performance optimization

---

## 📞 Support Resources

### Documentation
- **Full Guide**: See `DEPLOYMENT_GUIDE.md`
- **INSTALL.md**: Original installation instructions
- **README.md**: Application overview

### External Resources
- **Apache**: https://httpd.apache.org/
- **PHP**: https://www.php.net/
- **MariaDB**: https://mariadb.com/docs/
- **Let's Encrypt**: https://letsencrypt.org/
- **Ubuntu**: https://help.ubuntu.com/

### Commands for Common Tasks

```bash
# View deployment log
cat /var/log/association-app-deploy.log

# Check service status
systemctl status apache2 php8.4-fpm mariadb

# Restart all services
sudo systemctl restart apache2 php8.4-fpm mariadb

# Create manual backup
sudo ./backup.sh

# Update system
sudo apt update && sudo apt upgrade -y

# Check disk usage
df -h

# Monitor processes
htop
```

---

## 🔐 Security Recommendations

1. **Immediately After Deployment**
   - [ ] Change default passwords
   - [ ] Enable firewall
   - [ ] Configure SSH security
   - [ ] Test backup restoration

2. **Before Production**
   - [ ] Enable SSL/HTTPS
   - [ ] Harden PHP configuration
   - [ ] Setup monitoring and alerts
   - [ ] Configure log aggregation
   - [ ] Perform security scan

3. **Ongoing**
   - [ ] Keep system updated
   - [ ] Monitor error logs
   - [ ] Test backups regularly
   - [ ] Review access logs
   - [ ] Update dependencies

---

## 📊 Performance Tuning

The deployment includes basic optimization:

```
PHP OPcache:
- memory_consumption=256M
- max_accelerated_files=100000
- revalidate_freq=60

Apache:
- mod_deflate (gzip compression)
- Cache-Control headers
- Expires directives
- Connection keep-alive

MySQL:
- Full UTF-8MB4 support
- Proper collation settings
```

For production environments, consider:
- Redis for caching
- CDN for static assets
- Load balancing
- Advanced monitoring

---

## 📄 License & Support

This deployment script is provided as-is. For issues or improvements:

1. Check `DEPLOYMENT_GUIDE.md` troubleshooting section
2. Review application logs
3. Verify system resources
4. Test on staging environment first

---

## 🎯 Next Steps

1. **Read the Guide**: Review `DEPLOYMENT_GUIDE.md` for detailed information
2. **Run Deployment**: Execute `sudo ./deploy.sh` or use `./quick-deploy.sh`
3. **Configure Application**: Run `./quick-deploy.sh config`
4. **Verify**: Run `./quick-deploy.sh verify`
5. **Test**: Access application in browser
6. **Monitor**: Use `./quick-deploy.sh logs` to monitor

---

**Version**: 1.0.0  
**Last Updated**: May 11, 2026  
**Tested On**: Ubuntu Server 22.04 LTS
