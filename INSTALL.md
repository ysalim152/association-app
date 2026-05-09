# Guide d'Installation - Gestion Associations Sportives

Instructions complètes pour déployer l'application sur Ubuntu 22.04 LTS LAMP.

## 📋 Table des matières

1. [Prérequis](#prérequis)
2. [Installation du serveur](#installation-du-serveur)
3. [Installation de l'application](#installation-de-lapplication)
4. [Configuration Apache](#configuration-apache)
5. [Configuration PHP](#configuration-php)
6. [Configuration de la base de données](#configuration-de-la-base-de-données)
7. [Vérification et tests](#vérification-et-tests)
8. [Maintenance](#maintenance)

---

## 🔧 Prérequis

### Versions requises

- **Ubuntu** : 22.04 LTS
- **Apache** : 2.4.67+
- **PHP** : 8.4.21+
- **MariaDB** : 10.6.23+
- **OpenSSL** : Pour HTTPS (recommandé)

### Accès

- Accès SSH ou console serveur
- Permissions sudo
- Domaine ou IP publique (optionnel pour dev local)

---

## 🌐 Installation du serveur

### Étape 1 : Mise à jour du système

```bash
sudo apt update
sudo apt upgrade -y
sudo apt install -y curl wget git
```

### Étape 2 : Installation Apache 2.4

```bash
sudo apt install -y apache2
sudo a2enmod rewrite
sudo a2enmod ssl
sudo a2enmod headers
sudo systemctl restart apache2
```

### Étape 3 : Installation PHP 8.4

```bash
# Ajouter repository PHP
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Installer PHP 8.4 et extensions
sudo apt install -y php8.4 php8.4-cli php8.4-fpm php8.4-mbstring \
    php8.4-pdo php8.4-mysql php8.4-curl php8.4-gd php8.4-json \
    php8.4-zip php8.4-xml

# Vérifier la version
php -v
```

### Étape 4 : Installation MariaDB 10.6

```bash
# Ajouter repository MariaDB
curl -LsS https://r.mariadb.com/downloads/mariadb_repo_setup | sudo bash -s -- --mariadb-version=10.6

# Installer MariaDB
sudo apt install -y mariadb-server mariadb-client

# Vérifier
mysql --version

# Démarrer et activer
sudo systemctl start mariadb
sudo systemctl enable mariadb
```

### Étape 5 : Sécurisation MariaDB

```bash
# Lancer le script de sécurisation
sudo mariadb-secure-installation

# Répondre aux questions :
# Enter current password (press Enter) : [appuyer sur Entrée]
# Switch to unix_socket authentication : N
# Change root password : Y (définir mot de passe fort)
# Remove anonymous users : Y
# Disable root login remotely : Y
# Remove test database : Y
# Reload privilege tables : Y
```

---

## 📦 Installation de l'application

### Étape 1 : Téléchargement

```bash
# Option A : Git clone (si disponible)
cd /var/www
sudo git clone <repo-url> association-app

# Option B : Copie manuelle
cd /var/www
sudo cp -r /path/to/association-app .
```

### Étape 2 : Permissions

```bash
cd /var/www/association-app

# Propriétaire
sudo chown -R www-data:www-data .

# Permissions répertoires
sudo chmod 755 .
sudo chmod 755 public
sudo chmod 755 app

# Permissions dossiers writable
sudo chmod 775 public/uploads
sudo chmod 775 logs
sudo chmod 775 storage
sudo chmod 775 storage/exports

# Permissions fichiers
sudo chmod 644 public/index.php
sudo chmod 644 public/.htaccess
sudo chmod 644 app/config/*.php
```

### Étape 3 : Configuration .env

```bash
# Copier template
sudo cp .env.example .env
sudo nano .env
```

Éditer les variables :

```
APP_ENV=production
DB_HOST=localhost
DB_PORT=3306
DB_NAME=association_db
DB_USER=association_user
DB_PASSWORD=PutPasswordSecureIciWithCapitalNumbers123!
APP_DEBUG=false
```

---

## 🔒 Configuration Apache

### Étape 1 : Créer VirtualHost

```bash
sudo nano /etc/apache2/sites-available/association.conf
```

Contenu (adapter domaine et chemins) :

```apache
<VirtualHost *:80>
    ServerName association.local
    ServerAlias www.association.local
    ServerAdmin admin@association.local

    # Racine web
    DocumentRoot /var/www/association-app/public

    # Logs
    ErrorLog /var/www/association-app/logs/apache_error.log
    CustomLog /var/www/association-app/logs/apache_access.log combined

    # Directory permissions
    <Directory /var/www/association-app/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Empêcher accès à app/
    <Directory /var/www/association-app/app>
        Order Allow,Deny
        Deny from all
    </Directory>

    # Compression
    <IfModule mod_deflate.c>
        AddOutputFilterByType DEFLATE text/html text/plain text/xml
        AddOutputFilterByType DEFLATE text/css
        AddOutputFilterByType DEFLATE text/javascript application/javascript
    </IfModule>

    # Cache headers
    <IfModule mod_expires.c>
        ExpiresActive On
        ExpiresByType image/jpg "access plus 1 year"
        ExpiresByType image/png "access plus 1 year"
        ExpiresByType image/css "access plus 1 year"
        ExpiresByType text/css "access plus 1 year"
        ExpiresByType text/javascript "access plus 1 year"
    </IfModule>
</VirtualHost>
```

### Étape 2 : Activer VirtualHost

```bash
# Activer le site
sudo a2ensite association

# Tester config Apache
sudo apache2ctl configtest
# Doit afficher : Syntax OK

# Redémarrer Apache
sudo systemctl restart apache2
```

### Étape 3 : Configuration HTTPS (Let's Encrypt - optionnel)

```bash
# Installer Certbot
sudo apt install -y certbot python3-certbot-apache

# Générer certificat
sudo certbot --apache -d association.local

# Renouvellement auto (cron)
sudo certbot renew --quiet
```

### Étape 4 : Configuration /etc/hosts (local dev)

Si dev local sans DNS :

```bash
sudo nano /etc/hosts

# Ajouter ligne :
127.0.0.1  association.local www.association.local
```

---

## 🐘 Configuration PHP

### Étape 1 : Paramètres PHP

```bash
sudo nano /etc/php/8.4/apache2/php.ini
```

Recommandations :

```ini
; Sécurité
expose_php = Off
display_errors = Off
log_errors = On
error_log = /var/www/association-app/logs/php_error.log

; Performance
max_execution_time = 300
memory_limit = 256M
post_max_size = 50M
upload_max_filesize = 50M

; Sessions
session.save_path = /var/lib/php/sessions
session.name = ASSOC_SESSION
session.gc_maxlifetime = 3600

; Timezone
date.timezone = "Europe/Paris"

; PDO
extension=pdo_mysql
```

### Étape 2 : Redémarrer PHP-FPM

```bash
sudo systemctl restart php8.4-fpm
sudo systemctl restart apache2
```

---

## 💾 Configuration de la base de données

### Étape 1 : Créer utilisateur MariaDB

```bash
# Se connecter à MariaDB
sudo mysql -u root -p
# Entrer mot de passe défini précédemment

# Dans le prompt MariaDB :
CREATE USER 'association_user'@'localhost' IDENTIFIED BY 'PutPasswordSecureIciWithCapitalNumbers123!';

CREATE DATABASE association_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON association_db.* TO 'association_user'@'localhost';

FLUSH PRIVILEGES;

EXIT;
```

### Étape 2 : Importer schéma initial

```bash
# Importer le script SQL
mysql -u association_user -p association_db < /var/www/association-app/database/init.sql

# Vérifier les tables
mysql -u association_user -p association_db
# Dans le prompt :
SHOW TABLES;
# Doit afficher 8 tables
EXIT;
```

### Étape 3 : Vérifier les données

```bash
mysql -u association_user -p association_db -e "SELECT * FROM users;"
# Doit afficher 3 utilisateurs de test
```

---

## ✅ Vérification et tests

### Étape 1 : Vérifier services

```bash
# Apache
sudo systemctl status apache2

# PHP
sudo systemctl status php8.4-fpm

# MariaDB
sudo systemctl status mariadb
```

### Étape 2 : Vérifier permissions

```bash
cd /var/www/association-app
ls -la
# Vérifier que www-data est propriétaire

# Vérifier fichiers critiques
ls -la app/config/
# database.php et constants.php doivent être lisibles
```

### Étape 3 : Tester l'application

Ouvrir navigateur :

```
http://association.local/login
# Ou : http://127.0.0.1/association-app/public/login (si local)
```

### Étape 4 : Se connecter

Utiliser identifiants de test :

```
Email : admin@association.local
Mot de passe : password
```

### Étape 5 : Vérifier les logs

```bash
# Logs erreurs Apache
tail -f /var/www/association-app/logs/apache_error.log

# Logs PHP
tail -f /var/www/association-app/logs/php_error.log

# Logs application
tail -f /var/www/association-app/logs/error.log
```

---

## 🔧 Maintenance

### Sauvegardes

```bash
# Sauvegarde BD
mysqldump -u association_user -p association_db > /backup/association_$(date +%Y%m%d).sql

# Sauvegarde fichiers
tar -czf /backup/association_$(date +%Y%m%d).tar.gz /var/www/association-app
```

### Nettoyage logs

```bash
# Archiver logs mensuellement
cd /var/www/association-app/logs
gzip *.log
mkdir -p archives
mv *.gz archives/
```

### Mise à jour

```bash
# Backup avant update
sudo cp -r /var/www/association-app /backup/association-backup-$(date +%Y%m%d)

# Télécharger nouvelle version
# Fusionner fichiers ...

# Redémarrer
sudo systemctl restart apache2
```

### Monitoring

```bash
# Vérifier espace disque
df -h

# Vérifier mémoire
free -m

# Connexions actives
netstat -an | grep ESTABLISHED | wc -l
```

---

## 🆘 Troubleshooting

### Erreur : "Permission denied"

```bash
sudo chown -R www-data:www-data /var/www/association-app
sudo chmod -R 755 /var/www/association-app
sudo chmod 775 /var/www/association-app/logs
```

### Erreur : "Could not connect to database"

```bash
# Vérifier MariaDB
sudo systemctl restart mariadb

# Vérifier connexion
mysql -u association_user -pPASSWORD association_db

# Vérifier fichier config
cat /var/www/association-app/app/config/database.php
```

### Erreur : ".htaccess not working"

```bash
# Vérifier mod_rewrite
sudo a2enmod rewrite

# Vérifier AllowOverride
sudo grep -r "AllowOverride" /etc/apache2/sites-available/

# Redémarrer
sudo systemctl restart apache2
```

### Page blanche

```bash
# Vérifier logs
tail -50 /var/www/association-app/logs/php_error.log

# Activer débug temporairement
# Éditer .env : APP_DEBUG=true

# Revérifier après fix
# APP_DEBUG=false
```

---

## 📞 Support

Vérifier les logs d'erreur dans `/var/www/association-app/logs/`

Contactez l'administrateur système en cas de problème.

---

**Dernière mise à jour** : 2025-05-09
