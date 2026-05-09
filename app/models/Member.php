<?php
if (!defined('APP_ROOT')) define('APP_ROOT', dirname(dirname(dirname(__FILE__))));

require_once APP_ROOT . '/app/models/BaseModel.php';
require_once APP_ROOT . '/app/config/constants.php';

class Member extends BaseModel {
    protected $table = 'members';

    public function findWithUser($id) {
        $sql = "SELECT m.*, u.email, u.role FROM {$this->table} m
                LEFT JOIN users u ON m.user_id = u.id
                WHERE m.id = ? AND m.is_deleted = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function allWithUsers($limit = null, $offset = 0) {
        $sql = "SELECT m.*, u.email FROM {$this->table} m
                LEFT JOIN users u ON m.user_id = u.id
                WHERE m.is_deleted = 0
                ORDER BY m.last_name, m.first_name";
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$limit, $offset]);
        } else {
            $stmt = $this->pdo->query($sql);
        }
        return $stmt->fetchAll();
    }

    public function searchMembers($query) {
        return $this->search($query, ['first_name', 'last_name', 'email', 'license_number']);
    }

    public function getMembersByTeam($team_id) {
        $sql = "SELECT m.* FROM {$this->table} m
                INNER JOIN team_members tm ON m.id = tm.member_id
                WHERE tm.team_id = ? AND m.is_deleted = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$team_id]);
        return $stmt->fetchAll();
    }

    public function getActiveMembersCount() {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}
                WHERE is_deleted = 0 AND user_id IS NOT NULL";
        $result = $this->pdo->query($sql)->fetch();
        return $result['count'] ?? 0;
    }

    public function getMembersPaidThisYear() {
        $sql = "SELECT COUNT(DISTINCT m.id) as count FROM {$this->table} m
                INNER JOIN payments p ON m.id = p.member_id
                WHERE m.is_deleted = 0 AND p.year = ? AND p.status = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([date('Y'), PAYMENT_STATUS_PAID]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
}
