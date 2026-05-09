# Gestion Associations Sportives

Application web complète pour la gestion des associations sportives, construite avec PHP 8.4, MariaDB 10.6 et Bootstrap 5.

## 🎯 Fonctionnalités

- ✅ **Authentification multi-rôles** : Admin, Secrétaire, Membre
- ✅ **Gestion des membres** : Création, modification, suppression, export CSV
- ✅ **Gestion des équipes** : Affectation des membres, statistiques
- ✅ **Calendrier de matchs** : Programmation, scores, historique
- ✅ **Gestion des paiements** : Adhésions, rappels, suivi par année
- ✅ **Tableau de bord** : KPI, graphiques, statistiques
- ✅ **Système d'audit** : Traçabilité complète des actions
- ✅ **Notifications email** : Rappels, alertes
- ✅ **API REST** : Endpoints pour intégration externe
- ✅ **Design responsive** : Mobile-first, couleurs imposées

## 🎨 Charte Graphique

- **Couleur primaire** : Bleu `#0D3B66`
- **Couleur secondaire** : Blanc `#FFFFFF`
- **Couleur tertiaire** : Noir `#111111`
- **Framework CSS** : Bootstrap 5 + CSS personnalisé

## 📋 Prérequis

- **Serveur Web** : Apache 2.4+ avec mod_rewrite
- **PHP** : 8.4.21+
- **Base de données** : MariaDB 10.6.23+
- **OS** : Ubuntu 22.04 LTS (recommandé)

## 🚀 Installation Rapide

Voir le fichier `INSTALL.md` pour les instructions détaillées.

### Résumé

```bash
# 1. Copier les fichiers
cp -r association-app /var/www/

# 2. Créer la base de données
mysql -u root < /var/www/association-app/database/init.sql

# 3. Configurer Apache (VirtualHost)
# Voir INSTALL.md section "Configuration Apache"

# 4. Configuration PHP
cp /var/www/association-app/.env.example /var/www/association-app/.env

# 5. Permissions
chmod -R 775 /var/www/association-app/public/uploads
chmod -R 775 /var/www/association-app/logs
chmod -R 775 /var/www/association-app/storage

# 6. Tester
# Accéder à http://votre-domaine.local/login
```

## 🔐 Identifiants de Test

| Rôle | Email | Mot de passe |
|------|-------|-------------|
| Admin | admin@association.local | password |
| Secrétaire | secretaire@association.local | password |
| Membre | membre@association.local | password |

## 📁 Structure des Fichiers

```
association-app/
├── public/               # Racine web (Apache docroot)
│   ├── index.php        # Point d'entrée
│   ├── css/             # Feuilles de style
│   ├── js/              # Scripts JavaScript
│   └── uploads/         # Fichiers uploadés
│
├── app/                 # Code source
│   ├── config/          # Configuration
│   ├── controllers/     # Logique métier
│   ├── models/          # Modèles de données
│   ├── helpers/         # Fonctions utilitaires
│   ├── middleware/      # Middlewares
│   └── utils/           # Classes utilitaires
│
├── views/               # Templates PHP
│   ├── layouts/         # Layouts principaux
│   ├── auth/            # Pages authentification
│   ├── dashboard/       # Tableau de bord
│   ├── members/         # Gestion membres
│   ├── teams/           # Gestion équipes
│   ├── matches/         # Gestion matchs
│   ├── payments/        # Gestion paiements
│   ├── admin/           # Pages admin
│   └── api/             # Réponses API
│
├── database/            # Scripts SQL
├── logs/                # Fichiers journaux
├── storage/             # Fichiers générés
└── README.md            # Ce fichier
```

## 🔒 Sécurité

- ✅ Hachage des mots de passe avec `password_hash()`
- ✅ Requêtes préparées (PDO) contre les injections SQL
- ✅ Validation côté serveur de toutes les entrées
- ✅ Protection CSRF avec tokens
- ✅ Échappement XSS avec `htmlspecialchars()`
- ✅ Sessions sécurisées avec timeout
- ✅ Headers de sécurité HTTP

## 📊 Base de Données

### Tables principales

- `users` - Utilisateurs et authentification
- `members` - Adhérents
- `teams` - Équipes
- `team_members` - Relation membres-équipes
- `matches` - Matchs et compétitions
- `payments` - Adhésions et paiements
- `audit_logs` - Traçabilité des actions
- `email_queue` - Queue d'envoi d'emails

## 🔌 API REST

### Endpoints disponibles

```
GET    /api/members            # Liste membres
POST   /api/members            # Créer membre
GET    /api/members/{id}       # Détail membre
PUT    /api/members/{id}       # Modifier membre
DELETE /api/members/{id}       # Supprimer membre

GET    /api/teams              # Liste équipes
GET    /api/teams/{id}/members # Membres équipe

GET    /api/matches            # Liste matchs
GET    /api/matches/upcoming   # Prochains matchs

GET    /api/payments/member/{id} # Paiements membre

GET    /api/stats/dashboard    # Stats tableau de bord
```

## 📧 Configuration Email

Éditer `app/helpers/EmailHelper.php` pour configurer:

- **Option 1** : Utiliser la fonction `mail()` native PHP (par défaut)
- **Option 2** : Intégrer PHPMailer pour SMTP
- **Option 3** : Configuration Sendmail

## 🎯 Performances

- Pagination des listes (20 items par page par défaut)
- Index BD sur les colonnes fréquemment interrogées
- Cache des assets frontend (Bootstrap, Chart.js CDN)
- Logs d'audit optimisés avec archivage

## 📞 Support

Pour les bugs ou améliorations, consultez les logs :

```bash
# Logs applicatifs
tail -f /var/www/association-app/logs/error.log
tail -f /var/www/association-app/logs/access.log
tail -f /var/www/association-app/logs/audit.log
```

## 📄 Licence

Propriétaire - Tous droits réservés

## 📝 Changelog

### Version 1.0.0 (2025-05-09)
- Première release complète
- Toutes les fonctionnalités de base implémentées
- API REST et système d'audit actifs
