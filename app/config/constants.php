<?php
// Constantes métier

// Rôles utilisateurs
define('ROLE_ADMIN', 'admin');
define('ROLE_SECRETARY', 'secrétaire');
define('ROLE_MEMBER', 'membre');

const ROLES = [
    ROLE_ADMIN => 'Administrateur',
    ROLE_SECRETARY => 'Secrétaire',
    ROLE_MEMBER => 'Membre',
];

// Statuts des paiements
define('PAYMENT_STATUS_PAID', 'payé');
define('PAYMENT_STATUS_PENDING', 'en attente');
define('PAYMENT_STATUS_LATE', 'retard');

const PAYMENT_STATUSES = [
    PAYMENT_STATUS_PAID => 'Payé',
    PAYMENT_STATUS_PENDING => 'En attente',
    PAYMENT_STATUS_LATE => 'En retard',
];

// Statuts des matchs
define('MATCH_STATUS_SCHEDULED', 'programmé');
define('MATCH_STATUS_IN_PROGRESS', 'en cours');
define('MATCH_STATUS_COMPLETED', 'terminé');
define('MATCH_STATUS_CANCELLED', 'annulé');

const MATCH_STATUSES = [
    MATCH_STATUS_SCHEDULED => 'Programmé',
    MATCH_STATUS_IN_PROGRESS => 'En cours',
    MATCH_STATUS_COMPLETED => 'Terminé',
    MATCH_STATUS_CANCELLED => 'Annulé',
];

// Types de matchs
const MATCH_TYPES = [
    'friendly' => 'Amical',
    'league' => 'Championnat',
    'cup' => 'Coupe',
    'tournament' => 'Tournoi',
];

// Sports
const SPORTS = [
    'football' => 'Football',
    'basketball' => 'Basketball',
    'volleyball' => 'Volleyball',
    'handball' => 'Handball',
    'tennis' => 'Tennis',
    'badminton' => 'Badminton',
    'other' => 'Autre',
];

// Actions audit
const AUDIT_ACTIONS = [
    'create' => 'Création',
    'update' => 'Modification',
    'delete' => 'Suppression',
    'login' => 'Connexion',
    'logout' => 'Déconnexion',
    'export' => 'Export',
    'payment' => 'Paiement',
];

// Permissions par rôle
const ROLE_PERMISSIONS = [
    ROLE_ADMIN => [
        'dashboard' => true,
        'members.view' => true,
        'members.create' => true,
        'members.edit' => true,
        'members.delete' => true,
        'members.export' => true,
        'teams.manage' => true,
        'matches.manage' => true,
        'payments.manage' => true,
        'admin.settings' => true,
        'admin.logs' => true,
        'admin.users' => true,
        'api.access' => true,
    ],
    ROLE_SECRETARY => [
        'dashboard' => true,
        'members.view' => true,
        'members.create' => true,
        'members.edit' => true,
        'members.export' => true,
        'teams.view' => true,
        'matches.view' => true,
        'payments.manage' => true,
        'api.access' => false,
    ],
    ROLE_MEMBER => [
        'dashboard' => true,
        'members.view' => true,
        'members.edit' => false,
        'teams.view' => true,
        'matches.view' => true,
        'payments.view' => true,
        'api.access' => false,
    ],
];

// Formats d'export
const EXPORT_FORMATS = [
    'csv' => 'CSV',
    'xlsx' => 'Excel',
];

// Pagination
define('ITEMS_PER_PAGE', 20);

// Limites API
define('API_RATE_LIMIT', 100); // requêtes par heure
define('API_RATE_WINDOW', 3600); // 1 heure en secondes

// Extensions fichiers autorisées
const ALLOWED_EXTENSIONS = [
    'jpg', 'jpeg', 'png', 'gif', 'pdf'
];

// MIME types autorisés
const ALLOWED_MIMES = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'application/pdf' => 'pdf',
];

// Tailles fichiers max
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
