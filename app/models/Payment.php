<?php
if (!defined('APP_ROOT')) define('APP_ROOT', dirname(dirname(dirname(__FILE__))));

require_once APP_ROOT . '/app/models/BaseModel.php';
require_once APP_ROOT . '/app/config/constants.php';

class Payment extends BaseModel {
    protected $table = 'payments';

    public function getPaymentsByMember($member_id) {
        $sql = "SELECT * FROM {$this->table} WHERE member_id = ? ORDER BY year DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$member_id]);
        return $stmt->fetchAll();
    }

    public function getCurrentYearPayment($member_id, $year = null) {
        $year = $year ?? date('Y');
        $sql = "SELECT * FROM {$this->table} WHERE member_id = ? AND year = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$member_id, $year]);
        return $stmt->fetch();
    }

    public function getTotalRevenue($year = null) {
        $year = $year ?? date('Y');
        $sql = "SELECT SUM(amount) as total FROM {$this->table}
                WHERE year = ? AND status = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$year, PAYMENT_STATUS_PAID]);
        $result = $stmt->fetch();
        return floatval($result['total'] ?? 0);
    }

    public function getPaymentStats($year = null) {
        $year = $year ?? date('Y');
        $sql = "SELECT
                    status,
                    COUNT(*) as count,
                    SUM(amount) as total
                FROM {$this->table}
                WHERE year = ?
                GROUP BY status";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$year]);
        return $stmt->fetchAll();
    }

    public function getLatePayers($days = 30, $year = null) {
        $year = $year ?? date('Y');
        $due_date = date('Y-m-d', strtotime("-$days days"));
        $sql = "SELECT p.*, m.first_name, m.last_name, u.email FROM {$this->table} p
                INNER JOIN members m ON p.member_id = m.id
                INNER JOIN users u ON m.user_id = u.id
                WHERE p.year = ? AND p.status != ? AND p.due_date <= ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$year, PAYMENT_STATUS_PAID, $due_date]);
        return $stmt->fetchAll();
    }

    public function markAsPaid($id) {
        return $this->update($id, [
            'status' => PAYMENT_STATUS_PAID,
            'payment_date' => date('Y-m-d H:i:s'),
        ]);
    }

    public function bulkUpdateStatus($payment_ids, $status) {
        if (empty($payment_ids)) return false;
        $placeholders = implode(',', array_fill(0, count($payment_ids), '?'));
        $sql = "UPDATE {$this->table} SET status = ? WHERE id IN ($placeholders)";
        $values = array_merge([$status], $payment_ids);
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }
}
