<?php
if (!defined('APP_ROOT')) define('APP_ROOT', dirname(dirname(dirname(__FILE__))));

require_once APP_ROOT . '/app/models/BaseModel.php';
require_once APP_ROOT . '/app/config/constants.php';

class MatchModel extends BaseModel {
    protected $table = 'matches';

    public function getUpcomingMatches($limit = 10) {
        $sql = "SELECT * FROM {$this->table}
                WHERE match_date >= NOW() AND status != ?
                ORDER BY match_date ASC LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([MATCH_STATUS_CANCELLED, $limit]);
        return $stmt->fetchAll();
    }

    public function getMatchesByTeam($team_id, $limit = null) {
        $sql = "SELECT * FROM {$this->table}
                WHERE team_id = ? AND status != ?
                ORDER BY match_date DESC";
        if ($limit) {
            $sql .= " LIMIT ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$team_id, MATCH_STATUS_CANCELLED, $limit]);
        } else {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$team_id, MATCH_STATUS_CANCELLED]);
        }
        return $stmt->fetchAll();
    }

    public function getMatchesByMonth($year, $month) {
        $start_date = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
        $end_date = date('Y-m-t', strtotime($start_date));
        $sql = "SELECT * FROM {$this->table}
                WHERE DATE(match_date) BETWEEN ? AND ?
                ORDER BY match_date ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$start_date, $end_date]);
        return $stmt->fetchAll();
    }

    public function getMatchWithTeam($id) {
        $sql = "SELECT m.*, t.name as team_name FROM {$this->table} m
                LEFT JOIN teams t ON m.team_id = t.id
                WHERE m.id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateScore($id, $score_team, $score_opponent, $status = MATCH_STATUS_COMPLETED) {
        $sql = "UPDATE {$this->table} SET score_team = ?, score_opponent = ?, status = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$score_team, $score_opponent, $status, $id]);
    }
}
