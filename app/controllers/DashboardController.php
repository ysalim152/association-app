<?php
// Contrôleur de tableau de bord
if (!defined('APP_ROOT')) define('APP_ROOT', dirname(dirname(dirname(__FILE__))));

require_once APP_ROOT . '/app/models/Member.php';
require_once APP_ROOT . '/app/models/Payment.php';
require_once APP_ROOT . '/app/models/MatchModel.php';
require_once APP_ROOT . '/app/models/Team.php';
require_once APP_ROOT . '/app/middleware/AuthMiddleware.php';

class DashboardController {
    public static function index() {
        AuthMiddleware::check();
        AuthMiddleware::checkSessionTimeout();

        $member_model = new Member();
        $payment_model = new Payment();
        $match_model = new MatchModel();
        $team_model = new Team();

        $stats = [
            'total_members' => $member_model->getActiveMembersCount(),
            'members_paid' => $member_model->getMembersPaidThisYear(),
            'total_revenue' => $payment_model->getTotalRevenue(),
            'upcoming_matches' => $match_model->getUpcomingMatches(5),
            'teams_count' => $team_model->count(),
        ];

        $payment_stats = $payment_model->getPaymentStats();
        $latest_members = $member_model->allWithUsers(5);

        include APP_ROOT . '/views/dashboard/index.php';
    }
}
