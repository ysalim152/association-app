<?php
// Contrôleur d'authentification
if (!defined('APP_ROOT')) define('APP_ROOT', dirname(dirname(dirname(__FILE__))));

require_once APP_ROOT . '/app/models/User.php';
require_once APP_ROOT . '/app/helpers/SecurityHelper.php';
require_once APP_ROOT . '/app/helpers/ValidationHelper.php';
require_once APP_ROOT . '/app/middleware/LoggingMiddleware.php';

class AuthController {
    public static function showLogin() {
        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }
        include APP_ROOT . '/views/auth/login.php';
    }

    public static function login() {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $csrf_token = $_POST['csrf_token'] ?? '';

        if (!SecurityHelper::verifyCSRFToken($csrf_token)) {
            $_SESSION['error'] = 'Token de sécurité invalide';
            header('Location: /login');
            exit;
        }

        $errors = ValidationHelper::validate([
            'email' => $email,
            'password' => $password,
        ], [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: /login');
            exit;
        }

        $user_model = new User();
        if (!$user_model->verifyPassword($email, $password)) {
            $_SESSION['error'] = 'Email ou mot de passe incorrect';
            LoggingMiddleware::logError('Failed login attempt', ['email' => $email]);
            header('Location: /login');
            exit;
        }

        $user = $user_model->findByEmail($email);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['last_activity'] = time();

        LoggingMiddleware::logAccess($user['id'], '/login', 'POST');

        header('Location: /dashboard');
        exit;
    }

    public static function logout() {
        LoggingMiddleware::logAccess($_SESSION['user_id'] ?? null, '/logout', 'POST');
        session_destroy();
        header('Location: /login?logged_out=1');
        exit;
    }

    public static function showRegister() {
        // Seul admin peut créer de nouveaux comptes en production
        // À adapter selon votre politique d'inscription
        if (!AuthMiddleware::isAdmin()) {
            header('Location: /dashboard');
            exit;
        }
        include APP_ROOT . '/views/auth/register.php';
    }

    public static function register() {
        AuthMiddleware::checkRole(ROLE_ADMIN);

        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $password_confirm = trim($_POST['password_confirm'] ?? '');
        $role = trim($_POST['role'] ?? ROLE_MEMBER);
        $csrf_token = $_POST['csrf_token'] ?? '';

        if (!SecurityHelper::verifyCSRFToken($csrf_token)) {
            $_SESSION['error'] = 'Token de sécurité invalide';
            header('Location: /register');
            exit;
        }

        $errors = [];
        if (!ValidationHelper::validate(['email' => $email], ['email' => 'required|email'])) {
            $errors['email'] = 'Email invalide';
        }
        if (!ValidationHelper::isStrongPassword($password)) {
            $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre';
        }
        if ($password !== $password_confirm) {
            $errors['password_confirm'] = 'Les mots de passe ne correspondent pas';
        }
        if (!in_array($role, array_keys(ROLES))) {
            $errors['role'] = 'Rôle invalide';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: /register');
            exit;
        }

        $user_model = new User();
        if ($user_model->findByEmail($email)) {
            $_SESSION['error'] = 'Cet email est déjà utilisé';
            header('Location: /register');
            exit;
        }

        if ($user_model->createUser($email, $password, $role)) {
            LoggingMiddleware::logAction($_SESSION['user_id'], 'create', 'user', $user_model->find($user_model->pdo->lastInsertId())['id']);
            $_SESSION['success'] = 'Compte créé avec succès';
            header('Location: /users');
        } else {
            $_SESSION['error'] = 'Erreur lors de la création du compte';
            header('Location: /register');
        }
        exit;
    }
}
