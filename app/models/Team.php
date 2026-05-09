<?php
if (!defined('APP_ROOT')) define('APP_ROOT', dirname(dirname(dirname(__FILE__))));

require_once APP_ROOT . '/app/models/BaseModel.php';
require_once APP_ROOT . '/app/config/constants.php';

class Team extends BaseModel {
    protected $table = 'teams';

    public function getMembersCount($team_id) {
        $sql = "SELECT COUNT(*) as count FROM team_members WHERE team_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$team_id]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    public function addMember($team_id, $member_id, $position = 'Joueur') {
        $sql = "INSERT INTO team_members (team_id, member_id, position, joined_date) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$team_id, $member_id, $position, date('Y-m-d')]);
    }

    public function removeMember($team_id, $member_id) {
        $sql = "DELETE FROM team_members WHERE team_id = ? AND member_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$team_id, $member_id]);
    }

    public function getTeamMembers($team_id) {
        $sql = "SELECT m.*, tm.position, tm.joined_date FROM members m
                INNER JOIN team_members tm ON m.id = tm.member_id
                WHERE tm.team_id = ? AND m.is_deleted = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$team_id]);
        return $stmt->fetchAll();
    }

    public function getNextMatch($team_id) {
        $sql = "SELECT * FROM matches
                WHERE team_id = ? AND match_date >= NOW() AND status != ?
                ORDER BY match_date ASC LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$team_id, MATCH_STATUS_CANCELLED]);
        return $stmt->fetch();
    }

    public function getStats($team_id) {
        $sql = "SELECT
                    COUNT(*) as total_matches,
                    SUM(CASE WHEN status = ? AND score_team > score_opponent THEN 1 ELSE 0 END) as wins,
                    SUM(CASE WHEN status = ? AND score_team = score_opponent THEN 1 ELSE 0 END) as draws,
                    SUM(CASE WHEN status = ? AND score_team < score_opponent THEN 1 ELSE 0 END) as losses
                FROM matches WHERE team_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([MATCH_STATUS_COMPLETED, MATCH_STATUS_COMPLETED, MATCH_STATUS_COMPLETED, $team_id]);
        return $stmt->fetch();
    }
}
