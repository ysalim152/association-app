<?php
// Contrôleur des équipes
if (!defined('APP_ROOT')) define('APP_ROOT', dirname(dirname(dirname(__FILE__))));

require_once APP_ROOT . '/app/models/Team.php';
require_once APP_ROOT . '/app/models/Member.php';
require_once APP_ROOT . '/app/helpers/SecurityHelper.php';
require_once APP_ROOT . '/app/middleware/AuthMiddleware.php';
require_once APP_ROOT . '/app/config/constants.php';

class TeamController {
    public static function list() {
        AuthMiddleware::checkPermission('teams.view');
        $team_model = new Team();
        $teams = $team_model->all();
        include APP_ROOT . '/views/teams/list.php';
    }

    public static function create() {
        AuthMiddleware::checkPermission('teams.manage');
        include APP_ROOT . '/views/teams/create.php';
    }

    public static function store() {
        AuthMiddleware::checkPermission('teams.manage');

        $data = SecurityHelper::sanitize($_POST);
        $csrf_token = $_POST['csrf_token'] ?? '';

        if (!SecurityHelper::verifyCSRFToken($csrf_token)) {
            $_SESSION['error'] = 'Token invalide';
            header('Location: /teams/create');
            exit;
        }

        $team_model = new Team();
        $team_model->create([
            'name' => $data['name'],
            'sport_type' => $data['sport_type'],
        ]);

        $_SESSION['success'] = 'Équipe créée';
        header('Location: /teams');
        exit;
    }

    public static function members($id) {
        AuthMiddleware::checkPermission('teams.view');

        $team_model = new Team();
        $team = $team_model->find($id);

        if (!$team) {
            $_SESSION['error'] = 'Équipe non trouvée';
            header('Location: /teams');
            exit;
        }

        $members = $team_model->getTeamMembers($id);
        $all_members = $GLOBALS['pdo']->query("SELECT id, first_name, last_name FROM members WHERE is_deleted = 0")->fetchAll();

        include APP_ROOT . '/views/teams/assign-members.php';
    }

    public static function addMember() {
        AuthMiddleware::checkPermission('teams.manage');

        $team_id = (int)($_POST['team_id'] ?? 0);
        $member_id = (int)($_POST['member_id'] ?? 0);

        $team_model = new Team();
        if ($team_model->addMember($team_id, $member_id)) {
            $_SESSION['success'] = 'Membre ajouté';
        }

        header('Location: /teams/' . $team_id . '/members');
        exit;
    }
}
