<?php
// Contrôleur des matchs
if (!defined('APP_ROOT')) define('APP_ROOT', dirname(dirname(dirname(__FILE__))));

require_once APP_ROOT . '/app/models/MatchModel.php';
require_once APP_ROOT . '/app/models/Team.php';
require_once APP_ROOT . '/app/helpers/SecurityHelper.php';
require_once APP_ROOT . '/app/middleware/AuthMiddleware.php';
require_once APP_ROOT . '/app/config/constants.php';

class MatchController {
    public static function calendar() {
        AuthMiddleware::checkPermission('matches.view');

        $year = (int)($_GET['year'] ?? date('Y'));
        $month = (int)($_GET['month'] ?? date('m'));

        $match_model = new MatchModel();
        $matches = $match_model->getMatchesByMonth($year, $month);

        include APP_ROOT . '/views/matches/calendar.php';
    }

    public static function create() {
        AuthMiddleware::checkPermission('matches.manage');

        $team_model = new Team();
        $teams = $team_model->all();

        include APP_ROOT . '/views/matches/create.php';
    }

    public static function store() {
        AuthMiddleware::checkPermission('matches.manage');

        $data = SecurityHelper::sanitize($_POST);
        $csrf_token = $_POST['csrf_token'] ?? '';

        if (!SecurityHelper::verifyCSRFToken($csrf_token)) {
            $_SESSION['error'] = 'Token invalide';
            header('Location: /matches/create');
            exit;
        }

        $match_model = new MatchModel();
        $match_model->create([
            'team_id' => (int)$data['team_id'],
            'opponent_name' => $data['opponent_name'],
            'match_date' => $data['match_date'],
            'location' => $data['location'],
            'match_type' => $data['match_type'],
            'status' => MATCH_STATUS_SCHEDULED,
        ]);

        $_SESSION['success'] = 'Match créé';
        header('Location: /matches/calendar');
        exit;
    }

    public static function updateScore($id) {
        AuthMiddleware::checkPermission('matches.manage');

        $data = SecurityHelper::sanitize($_POST);
        $csrf_token = $_POST['csrf_token'] ?? '';

        if (!SecurityHelper::verifyCSRFToken($csrf_token)) {
            $_SESSION['error'] = 'Token invalide';
            header('Location: /matches/calendar');
            exit;
        }

        $match_model = new MatchModel();
        $match_model->updateScore(
            $id,
            (int)$data['score_team'],
            (int)$data['score_opponent'],
            $data['status'] ?? MATCH_STATUS_COMPLETED
        );

        $_SESSION['success'] = 'Score mis à jour';
        header('Location: /matches/calendar');
        exit;
    }
}
