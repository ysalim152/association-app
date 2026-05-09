<?php
// Configuration globale de l'application
define('APP_NAME', 'Gestion Associations Sportives');
define('APP_VERSION', '1.0.0');
define('APP_ROOT', dirname(dirname(dirname(__FILE__))));
define('PUBLIC_ROOT', APP_ROOT . '/public');
define('STORAGE_ROOT', APP_ROOT . '/storage');
define('LOGS_ROOT', APP_ROOT . '/logs');

// Configuration session
define('SESSION_TIMEOUT', 3600); // 1 heure
define('SESSION_NAME', 'ASSOC_SESSION');

// Configuration email
define('MAIL_FROM', 'noreply@association.local');
define('MAIL_FROM_NAME', 'Gestion Associations Sportives');

// Configuration paiements
define('PAYMENT_YEAR', date('Y'));
define('DEFAULT_MEMBERSHIP_AMOUNT', 50.00);

// Environnement
define('ENV', getenv('APP_ENV') ?: 'development');
define('DEBUG', ENV === 'development');

// Couleurs branding
define('BRAND_PRIMARY_BLUE', '#0D3B66');
define('BRAND_WHITE', '#FFFFFF');
define('BRAND_BLACK', '#111111');

// Messages
define('MSGS', [
    'auth_required' => 'Authentification requise',
    'permission_denied' => 'Accès refusé - permissions insuffisantes',
    'record_not_found' => 'Enregistrement non trouvé',
    'operation_success' => 'Opération réalisée avec succès',
    'operation_failed' => 'L\'opération a échoué',
    'invalid_input' => 'Données invalides',
]);

// Gestion des erreurs
if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
}

// Démarrage session sécurisé
session_name(SESSION_NAME);
session_set_cookie_params([
    'lifetime' => SESSION_TIMEOUT,
    'path' => '/',
    'httponly' => true,
    'secure' => !DEBUG,
    'samesite' => 'Strict'
]);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusion des fichiers critiques
require_once APP_ROOT . '/app/config/constants.php';
require_once APP_ROOT . '/app/config/database.php';
