#!/bin/bash
# Quick Reference Card - Can be printed or displayed
# DEPLOYMENT COMMANDS CHEAT SHEET
# Association Management Application

cat << 'EOF'

╔══════════════════════════════════════════════════════════════════════════════╗
║                  DEPLOYMENT QUICK REFERENCE CARD                            ║
║                   Association Management Application                         ║
║                      Ubuntu Server 22.04 LTS                                 ║
║                                                                              ║
║                    Print this page for quick reference!                      ║
╚══════════════════════════════════════════════════════════════════════════════╝


┌─ PREPARATION ─────────────────────────────────────────────────────────────┐
│                                                                             │
│ Step 1: Make scripts executable                                           │
│ $ chmod +x *.sh                                                           │
│                                                                             │
│ Step 2: Upload to server                                                  │
│ $ scp -r *.sh root@your-server:/root/                                    │
│ $ scp -r . root@your-server:/var/www/  (or clone git repo)               │
│                                                                             │
│ Step 3: Connect to server                                                 │
│ $ ssh root@your-server                                                    │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘


┌─ DEPLOYMENT ──────────────────────────────────────────────────────────────┐
│                                                                             │
│ OPTION 1: HTTP Only (Development/Staging)                                │
│ $ sudo ./deploy.sh                                                        │
│                                                                             │
│ OPTION 2: HTTPS with Let's Encrypt (Production)                          │
│ $ sudo ENABLE_SSL=true SSL_EMAIL=admin@example.com ./deploy.sh          │
│                                                                             │
│ OPTION 3: Quick Deploy with Custom Domain                                │
│ $ sudo ./quick-deploy.sh deploy-ssl                                       │
│   (You'll be prompted for domain and email)                              │
│                                                                             │
│ OPTION 4: Quick Deploy HTTP Only                                         │
│ $ sudo ./quick-deploy.sh deploy-http                                      │
│                                                                             │
│ Deployment takes: ~15-25 minutes (depending on internet speed)            │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘


┌─ POST-DEPLOYMENT CONFIGURATION ───────────────────────────────────────────┐
│                                                                             │
│ Configure environment and database:                                       │
│ $ sudo ./quick-deploy.sh config                                           │
│                                                                             │
│ OR manually:                                                              │
│ $ sudo ./configure-env.sh                                                 │
│                                                                             │
│ Update database credentials:                                              │
│ $ sudo nano /var/www/association-app/app/config/database.php             │
│                                                                             │
│ Verify installation:                                                      │
│ $ ./verify-deployment.sh                                                  │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘


┌─ SERVICE MANAGEMENT ──────────────────────────────────────────────────────┐
│                                                                             │
│ Check Status:                                                             │
│ $ ./quick-deploy.sh status                                                │
│ $ systemctl status apache2                                                │
│ $ systemctl status php8.4-fpm                                             │
│ $ systemctl status mariadb                                                │
│                                                                             │
│ Restart Services:                                                         │
│ $ sudo ./quick-deploy.sh restart           # All services                 │
│ $ sudo ./quick-deploy.sh restart-apache    # Apache only                 │
│ $ sudo ./quick-deploy.sh restart-php       # PHP only                    │
│ $ sudo ./quick-deploy.sh restart-mysql     # MariaDB only                │
│                                                                             │
│ Stop/Start Individual Services:                                           │
│ $ sudo systemctl stop apache2              # Stop Apache                  │
│ $ sudo systemctl start apache2             # Start Apache                 │
│ $ sudo systemctl restart apache2           # Restart Apache              │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘


┌─ MONITORING & LOGS ───────────────────────────────────────────────────────┐
│                                                                             │
│ View All Logs (Real-Time):                                               │
│ $ ./quick-deploy.sh logs                   # All logs                    │
│                                                                             │
│ Apache Logs:                                                              │
│ $ ./quick-deploy.sh logs-apache            # Error log                   │
│ $ tail -50 /var/log/apache2/error.log      # Last 50 lines             │
│ $ tail -f /var/log/apache2/access.log      # Real-time access          │
│                                                                             │
│ Application Logs:                                                         │
│ $ ./quick-deploy.sh logs-app               # App logs                    │
│ $ tail -f /var/www/association-app/logs/*  # Real-time                  │
│                                                                             │
│ Deployment Log:                                                           │
│ $ cat /var/log/association-app-deploy.log  # Full deployment log        │
│                                                                             │
│ MySQL Logs:                                                               │
│ $ sudo tail -100 /var/log/mysql/error.log                               │
│                                                                             │
│ System Information:                                                       │
│ $ ./quick-deploy.sh info                   # Server info                 │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘


┌─ BACKUP & RESTORE ────────────────────────────────────────────────────────┐
│                                                                             │
│ Create Manual Backup:                                                     │
│ $ sudo ./backup.sh                                                        │
│                                                                             │
│ List Backups:                                                             │
│ $ ls -lah /var/backups/association-app/                                  │
│                                                                             │
│ Restore Database from Backup:                                             │
│ $ sudo ./quick-deploy.sh restore                                          │
│                                                                             │
│ Restore Files from Backup:                                                │
│ $ sudo tar -xzf /var/backups/association-app/files_backup_*.tar.gz \    │
│   -C /var/www/                                                            │
│                                                                             │
│ Automatic Backups Configured:                                             │
│ ✓ Database: Daily at 3 AM                                                │
│ ✓ Files: Daily at 3 AM                                                   │
│ ✓ Retention: 30 days                                                     │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘


┌─ SECURITY & FIREWALL ─────────────────────────────────────────────────────┐
│                                                                             │
│ Show Security Checklist:                                                  │
│ $ ./quick-deploy.sh security                                              │
│                                                                             │
│ Setup/Enable Firewall:                                                    │
│ $ sudo ./quick-deploy.sh firewall                                         │
│                                                                             │
│ Check SSL Certificate Status:                                             │
│ $ ./quick-deploy.sh ssl-status                                            │
│                                                                             │
│ Renew SSL Certificate Manually:                                           │
│ $ sudo certbot renew                                                      │
│                                                                             │
│ Check Firewall Status:                                                    │
│ $ sudo ufw status                                                         │
│                                                                             │
│ Allow/Deny Ports:                                                         │
│ $ sudo ufw allow 22/tcp       # Allow SSH                                │
│ $ sudo ufw allow 80/tcp       # Allow HTTP                               │
│ $ sudo ufw allow 443/tcp      # Allow HTTPS                              │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘


┌─ MAINTENANCE & UPDATES ───────────────────────────────────────────────────┐
│                                                                             │
│ Update System:                                                            │
│ $ sudo ./quick-deploy.sh update                                           │
│ $ sudo apt update && sudo apt upgrade -y                                 │
│                                                                             │
│ Clean Old Files & Cache:                                                  │
│ $ sudo ./quick-deploy.sh clean                                            │
│                                                                             │
│ Check Disk Usage:                                                         │
│ $ df -h                        # Filesystem usage                        │
│ $ du -sh /var/www/association-app  # App size                           │
│ $ du -sh /var/backups/         # Backups size                           │
│                                                                             │
│ Monitor System Resources:                                                 │
│ $ htop                         # Interactive monitoring                   │
│ $ free -h                      # Memory usage                             │
│ $ vmstat 1                     # Virtual memory stats                    │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘


┌─ TROUBLESHOOTING ─────────────────────────────────────────────────────────┐
│                                                                             │
│ Run Complete Health Check:                                               │
│ $ ./verify-deployment.sh                                                  │
│                                                                             │
│ 403 Forbidden Error:                                                      │
│ $ sudo chown -R www-data:www-data /var/www/association-app               │
│ $ sudo chmod -R 755 /var/www/association-app                             │
│                                                                             │
│ Database Connection Error:                                                │
│ $ mysql -u assoc_user -p association_db -e "SELECT 1;"                  │
│                                                                             │
│ Apache Configuration Issues:                                              │
│ $ sudo apache2ctl configtest                                              │
│                                                                             │
│ Check Apache Error Log:                                                   │
│ $ tail -100 /var/log/apache2/error.log                                   │
│                                                                             │
│ File Upload Permission Error:                                             │
│ $ sudo chmod 775 /var/www/association-app/public/uploads                │
│ $ sudo chown www-data:www-data /var/www/association-app/public/uploads  │
│                                                                             │
│ For detailed troubleshooting:                                             │
│ → See DEPLOYMENT_GUIDE.md (Troubleshooting section)                      │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘


┌─ VERIFICATION ────────────────────────────────────────────────────────────┐
│                                                                             │
│ Quick Verification:                                                       │
│ $ ./verify-deployment.sh                                                  │
│                                                                             │
│ Check PHP Version:                                                        │
│ $ php -v                                                                   │
│                                                                             │
│ Check Apache Version:                                                     │
│ $ apache2 -v                                                               │
│                                                                             │
│ Check MariaDB Version:                                                    │
│ $ mysql --version                                                          │
│                                                                             │
│ Test Local Web Access:                                                    │
│ $ curl http://localhost/                                                  │
│                                                                             │
│ Test Remote Web Access:                                                   │
│ $ curl http://your-domain.com/                                            │
│                                                                             │
│ Check Running Services:                                                   │
│ $ ps aux | grep -E "apache|php|mysql"                                   │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘


┌─ IMPORTANT LOCATIONS ─────────────────────────────────────────────────────┐
│                                                                             │
│ Application Root:         /var/www/association-app                       │
│ Web Root (Public):        /var/www/association-app/public                │
│ Application Config:       /var/www/association-app/app/config/           │
│ Database Config:          /var/www/association-app/app/config/database.php│
│ Environment Config:       /var/www/association-app/.env                  │
│                                                                             │
│ Apache Config:            /etc/apache2/                                   │
│ VirtualHost Config:       /etc/apache2/sites-enabled/association-app.conf│
│ PHP Config:               /etc/php/8.4/apache2/php.ini                   │
│ PHP-FPM Config:           /etc/php/8.4/fpm/php.ini                       │
│                                                                             │
│ Logs:                     /var/log/apache2/                              │
│ App Logs:                 /var/www/association-app/logs/                │
│ Deployment Log:           /var/log/association-app-deploy.log            │
│ MySQL Log:                /var/log/mysql/error.log                       │
│                                                                             │
│ Backups:                  /var/backups/association-app/                  │
│ SSL Certificates:         /etc/letsencrypt/live/your-domain.com/        │
│ Cron Jobs:                /etc/cron.d/association-app                    │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘


┌─ HELP & DOCUMENTATION ────────────────────────────────────────────────────┐
│                                                                             │
│ View Help for Quick Deploy:                                               │
│ $ ./quick-deploy.sh help                                                  │
│                                                                             │
│ View Help for All Commands:                                               │
│ $ ./quick-deploy.sh                    (shows help by default)            │
│                                                                             │
│ Read Full Deployment Guide:                                               │
│ $ less DEPLOYMENT_GUIDE.md             (650+ lines)                      │
│                                                                             │
│ Read Quick Start Guide:                                                   │
│ $ less README_DEPLOYMENT.md            (400+ lines)                      │
│                                                                             │
│ View Deployment Summary:                                                  │
│ $ less DEPLOYMENT_SUMMARY.md           (Overview of all scripts)         │
│                                                                             │
│ View Installation Instructions:                                            │
│ $ less INSTALL.md                      (Original guide)                  │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘


╔══════════════════════════════════════════════════════════════════════════════╗
║                           GETTING STARTED                                    ║
║                                                                              ║
║ 1. Prepare scripts:           chmod +x *.sh                                  ║
║ 2. Deploy:                    sudo ./deploy.sh                               ║
║ 3. Post-deployment config:    sudo ./quick-deploy.sh config                 ║
║ 4. Verify installation:       ./verify-deployment.sh                         ║
║ 5. Check status:              ./quick-deploy.sh status                       ║
║                                                                              ║
║ For help:                     ./quick-deploy.sh help                         ║
║ For detailed guide:           less DEPLOYMENT_GUIDE.md                       ║
║                                                                              ║
╚══════════════════════════════════════════════════════════════════════════════╝

EOF
