<?php
// Middleware d'authentification
class AuthMiddleware {
    public static function check() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    }

    public static function checkRole($required_role) {
        self::check();
        if ($_SESSION['user_role'] !== $required_role) {
            if (!in_array($_SESSION['user_role'], [$required_role])) {
                http_response_code(403);
                die('Accès refusé');
            }
        }
    }

    public static function checkPermission($permission) {
        self::check();
        $permissions = ROLE_PERMISSIONS[$_SESSION['user_role']] ?? [];
        if (!isset($permissions[$permission]) || !$permissions[$permission]) {
            http_response_code(403);
            die('Accès refusé');
        }
    }

    public static function checkSessionTimeout() {
        if (isset($_SESSION['last_activity'])) {
            $timeout = time() - $_SESSION['last_activity'];
            if ($timeout > SESSION_TIMEOUT) {
                session_destroy();
                header('Location: /login?expired=1');
                exit;
            }
        }
        $_SESSION['last_activity'] = time();
    }

    public static function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === ROLE_ADMIN;
    }

    public static function isSecretary() {
        return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], [ROLE_ADMIN, ROLE_SECRETARY]);
    }
}
