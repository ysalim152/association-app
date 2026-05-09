<?php
// =====================================================
// Point d'entrée principal de l'application
// =====================================================

define('APP_START', microtime(true));

// Configuration
require_once __DIR__ . '/../app/config/config.php';

// Require autoload
spl_autoload_register(function($class) {
    $dirs = [
        APP_ROOT . '/app/controllers/',
        APP_ROOT . '/app/models/',
        APP_ROOT . '/app/helpers/',
        APP_ROOT . '/app/middleware/',
        APP_ROOT . '/app/utils/',
    ];

    foreach ($dirs as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Require main utils
require_once APP_ROOT . '/app/utils/Router.php';
require_once APP_ROOT . '/app/helpers/SecurityHelper.php';
require_once APP_ROOT . '/app/middleware/AuthMiddleware.php';
require_once APP_ROOT . '/app/middleware/LoggingMiddleware.php';

// Routage simple
$method = $_SERVER['REQUEST_METHOD'];
$path = $_GET['route'] ?? '/dashboard';
$path = '/' . trim($path, '/');

// Routes publiques
if ($path === '/login' || $path === '/login/submit') {
    if ($method === 'GET') {
        AuthController::showLogin();
    } elseif ($method === 'POST') {
        AuthController::login();
    }
} elseif ($path === '/logout') {
    AuthController::logout();
}

// Routes protégées
else {
    AuthMiddleware::check();
    AuthMiddleware::checkSessionTimeout();

    // Dashboard
    if ($path === '/dashboard') {
        DashboardController::index();
    }

    // Members
    elseif ($path === '/members') {
        MemberController::list();
    } elseif ($path === '/members/create') {
        MemberController::create();
    } elseif ($path === '/members/store') {
        MemberController::store();
    } elseif (preg_match('#^/members/(\d+)/edit$#', $path, $m)) {
        MemberController::edit($m[1]);
    } elseif (preg_match('#^/members/(\d+)/update$#', $path, $m)) {
        MemberController::update($m[1]);
    } elseif (preg_match('#^/members/(\d+)/delete$#', $path, $m)) {
        MemberController::delete($m[1]);
    } elseif ($path === '/members/export') {
        MemberController::export();
    }

    // Teams
    elseif ($path === '/teams') {
        TeamController::list();
    } elseif ($path === '/teams/create') {
        TeamController::create();
    } elseif ($path === '/teams/store') {
        TeamController::store();
    } elseif (preg_match('#^/teams/(\d+)/members$#', $path, $m)) {
        TeamController::members($m[1]);
    } elseif ($path === '/teams/add-member') {
        TeamController::addMember();
    }

    // Matches
    elseif ($path === '/matches/calendar') {
        MatchController::calendar();
    } elseif ($path === '/matches/create') {
        MatchController::create();
    } elseif ($path === '/matches/store') {
        MatchController::store();
    } elseif (preg_match('#^/matches/(\d+)/score$#', $path, $m)) {
        MatchController::updateScore($m[1]);
    }

    // Payments
    elseif ($path === '/payments') {
        PaymentController::list();
    } elseif (preg_match('#^/payments/member/(\d+)$#', $path, $m)) {
        PaymentController::memberHistory($m[1]);
    } elseif ($path === '/payments/bulk-update') {
        PaymentController::bulkUpdate();
    } elseif (preg_match('#^/payments/(\d+)/paid$#', $path, $m)) {
        PaymentController::markPaid($m[1]);
    }

    // 404
    else {
        http_response_code(404);
        die('Page non trouvée');
    }
}
