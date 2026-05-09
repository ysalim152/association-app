<?php
// Contrôleur des membres
if (!defined('APP_ROOT')) define('APP_ROOT', dirname(dirname(dirname(__FILE__))));

require_once APP_ROOT . '/app/models/Member.php';
require_once APP_ROOT . '/app/models/User.php';
require_once APP_ROOT . '/app/helpers/ValidationHelper.php';
require_once APP_ROOT . '/app/helpers/SecurityHelper.php';
require_once APP_ROOT . '/app/helpers/ExportHelper.php';
require_once APP_ROOT . '/app/middleware/AuthMiddleware.php';
require_once APP_ROOT . '/app/middleware/LoggingMiddleware.php';

class MemberController {
    public static function list() {
        AuthMiddleware::checkPermission('members.view');
        AuthMiddleware::checkSessionTimeout();

        $member_model = new Member();
        $page = (int)($_GET['page'] ?? 1);
        $page = max(1, $page);
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        $search = trim($_GET['search'] ?? '');
        if (!empty($search)) {
            $members = $member_model->searchMembers($search);
            $total = count($members);
        } else {
            $members = $member_model->allWithUsers($limit, $offset);
            $total = $member_model->count();
        }

        $pages = ceil($total / $limit);

        include APP_ROOT . '/views/members/list.php';
    }

    public static function create() {
        AuthMiddleware::checkPermission('members.create');
        include APP_ROOT . '/views/members/create.php';
    }

    public static function store() {
        AuthMiddleware::checkPermission('members.create');

        $data = SecurityHelper::sanitize($_POST);
        $csrf_token = $_POST['csrf_token'] ?? '';

        if (!SecurityHelper::verifyCSRFToken($csrf_token)) {
            $_SESSION['error'] = 'Token de sécurité invalide';
            header('Location: /members/create');
            exit;
        }

        $errors = ValidationHelper::validate($data, [
            'first_name' => 'required|min:2',
            'last_name' => 'required|min:2',
            'email' => 'required|email',
            'phone' => 'required|phone',
            'dob' => 'required|date',
        ]);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $data;
            header('Location: /members/create');
            exit;
        }

        $user_model = new User();
        $member_model = new Member();

        if ($user_model->findByEmail($data['email'])) {
            $_SESSION['error'] = 'Cet email est déjà utilisé';
            header('Location: /members/create');
            exit;
        }

        $user_id = null;
        if (!empty($data['create_account'])) {
            $password = bin2hex(random_bytes(4));
            if ($user_model->createUser($data['email'], $password, ROLE_MEMBER)) {
                $user_id = $user_model->pdo->lastInsertId();
            }
        }

        $member_data = [
            'user_id' => $user_id,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'dob' => $data['dob'],
            'phone' => $data['phone'],
            'address' => $data['address'] ?? '',
            'city' => $data['city'] ?? '',
            'postal_code' => $data['postal_code'] ?? '',
            'license_number' => $data['license_number'] ?? '',
        ];

        if ($member_model->create($member_data)) {
            LoggingMiddleware::logAction($_SESSION['user_id'], 'create', 'member', $member_model->pdo->lastInsertId());
            $_SESSION['success'] = 'Membre créé avec succès';
            header('Location: /members');
        } else {
            $_SESSION['error'] = 'Erreur lors de la création';
            header('Location: /members/create');
        }
        exit;
    }

    public static function edit($id) {
        AuthMiddleware::checkPermission('members.edit');

        $member_model = new Member();
        $member = $member_model->findWithUser($id);

        if (!$member) {
            $_SESSION['error'] = 'Membre non trouvé';
            header('Location: /members');
            exit;
        }

        include APP_ROOT . '/views/members/edit.php';
    }

    public static function update($id) {
        AuthMiddleware::checkPermission('members.edit');

        $data = SecurityHelper::sanitize($_POST);
        $csrf_token = $_POST['csrf_token'] ?? '';

        if (!SecurityHelper::verifyCSRFToken($csrf_token)) {
            $_SESSION['error'] = 'Token de sécurité invalide';
            header('Location: /members/' . $id . '/edit');
            exit;
        }

        $member_model = new Member();
        $member = $member_model->find($id);

        if (!$member) {
            $_SESSION['error'] = 'Membre non trouvé';
            header('Location: /members');
            exit;
        }

        $update_data = [
            'first_name' => $data['first_name'] ?? $member['first_name'],
            'last_name' => $data['last_name'] ?? $member['last_name'],
            'phone' => $data['phone'] ?? $member['phone'],
            'address' => $data['address'] ?? $member['address'],
            'city' => $data['city'] ?? $member['city'],
            'postal_code' => $data['postal_code'] ?? $member['postal_code'],
        ];

        if ($member_model->update($id, $update_data)) {
            LoggingMiddleware::logAction($_SESSION['user_id'], 'update', 'member', $id, $member, $update_data);
            $_SESSION['success'] = 'Membre mis à jour avec succès';
            header('Location: /members/' . $id . '/edit');
        } else {
            $_SESSION['error'] = 'Erreur lors de la mise à jour';
            header('Location: /members/' . $id . '/edit');
        }
        exit;
    }

    public static function delete($id) {
        AuthMiddleware::checkPermission('members.delete');

        $member_model = new Member();
        if ($member_model->delete($id)) {
            LoggingMiddleware::logAction($_SESSION['user_id'], 'delete', 'member', $id);
            $_SESSION['success'] = 'Membre supprimé';
            header('Location: /members');
        } else {
            $_SESSION['error'] = 'Erreur lors de la suppression';
            header('Location: /members');
        }
        exit;
    }

    public static function export() {
        AuthMiddleware::checkPermission('members.export');

        $member_model = new Member();
        $members = $member_model->allWithUsers();

        $export_data = [];
        foreach ($members as $m) {
            $export_data[] = [
                'Prénom' => $m['first_name'],
                'Nom' => $m['last_name'],
                'Email' => $m['email'],
                'Téléphone' => $m['phone'],
                'Ville' => $m['city'],
                'Code Postal' => $m['postal_code'],
                'Date Adhésion' => $m['created_at'],
            ];
        }

        ExportHelper::exportToCsv($export_data, 'membres_' . date('Y-m-d') . '.csv');
    }
}
