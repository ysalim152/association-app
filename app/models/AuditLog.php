<?php
if (!defined('APP_ROOT')) define('APP_ROOT', dirname(dirname(dirname(__FILE__))));

require_once APP_ROOT . '/app/models/BaseModel.php';
require_once APP_ROOT . '/app/config/constants.php';

class AuditLog extends BaseModel {
    protected $table = 'audit_logs';

    public function log($user_id, $action, $entity_type, $entity_id, $old_values = null, $new_values = null) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        return $this->create([
            'user_id' => $user_id,
            'action' => $action,
            'entity_type' => $entity_type,
            'entity_id' => $entity_id,
            'old_values' => $old_values ? json_encode($old_values) : null,
            'new_values' => $new_values ? json_encode($new_values) : null,
            'ip_address' => $ip,
        ]);
    }

    public function getLogs($limit = 100, $offset = 0, $filters = []) {
        $sql = "SELECT al.*, u.email as user_email FROM {$this->table} al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE 1=1";

        $params = [];
        if (!empty($filters['user_id'])) {
            $sql .= " AND al.user_id = ?";
            $params[] = $filters['user_id'];
        }
        if (!empty($filters['action'])) {
            $sql .= " AND al.action = ?";
            $params[] = $filters['action'];
        }
        if (!empty($filters['entity_type'])) {
            $sql .= " AND al.entity_type = ?";
            $params[] = $filters['entity_type'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(al.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(al.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        $sql .= " ORDER BY al.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getLogsCount($filters = []) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE 1=1";

        $params = [];
        if (!empty($filters['user_id'])) {
            $sql .= " AND user_id = ?";
            $params[] = $filters['user_id'];
        }
        if (!empty($filters['action'])) {
            $sql .= " AND action = ?";
            $params[] = $filters['action'];
        }
        if (!empty($filters['entity_type'])) {
            $sql .= " AND entity_type = ?";
            $params[] = $filters['entity_type'];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    public function getEntityHistory($entity_type, $entity_id) {
        $sql = "SELECT al.*, u.email FROM {$this->table} al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.entity_type = ? AND al.entity_id = ?
                ORDER BY al.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$entity_type, $entity_id]);
        return $stmt->fetchAll();
    }
}
