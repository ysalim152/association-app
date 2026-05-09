<?php
// Contrôleur des paiements
if (!defined('APP_ROOT')) define('APP_ROOT', dirname(dirname(dirname(__FILE__))));

require_once APP_ROOT . '/app/models/Payment.php';
require_once APP_ROOT . '/app/models/Member.php';
require_once APP_ROOT . '/app/helpers/SecurityHelper.php';
require_once APP_ROOT . '/app/middleware/AuthMiddleware.php';
require_once APP_ROOT . '/app/middleware/LoggingMiddleware.php';
require_once APP_ROOT . '/app/config/constants.php';

class PaymentController {
    public static function list() {
        AuthMiddleware::checkPermission('payments.manage');
        AuthMiddleware::checkSessionTimeout();

        $payment_model = new Payment();
        $member_model = new Member();

        $year = (int)($_GET['year'] ?? date('Y'));
        $status = $_GET['status'] ?? '';

        $sql = "SELECT p.*, m.first_name, m.last_name FROM payments p
                INNER JOIN members m ON p.member_id = m.id
                WHERE p.year = ?";
        $params = [$year];

        if (!empty($status)) {
            $sql .= " AND p.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY m.last_name, p.created_at DESC";
        $stmt = $GLOBALS['pdo']->prepare($sql);
        $stmt->execute($params);
        $payments = $stmt->fetchAll();

        $stats = $payment_model->getPaymentStats($year);

        include APP_ROOT . '/views/payments/list.php';
    }

    public static function memberHistory($member_id) {
        AuthMiddleware::checkPermission('payments.manage');

        $payment_model = new Payment();
        $member_model = new Member();

        $member = $member_model->find($member_id);
        if (!$member) {
            $_SESSION['error'] = 'Membre non trouvé';
            header('Location: /payments');
            exit;
        }

        $payments = $payment_model->getPaymentsByMember($member_id);

        include APP_ROOT . '/views/payments/member-history.php';
    }

    public static function bulkUpdate() {
        AuthMiddleware::checkPermission('payments.manage');

        $payment_ids = $_POST['payment_ids'] ?? [];
        $new_status = $_POST['status'] ?? '';
        $csrf_token = $_POST['csrf_token'] ?? '';

        if (!SecurityHelper::verifyCSRFToken($csrf_token)) {
            $_SESSION['error'] = 'Token de sécurité invalide';
            header('Location: /payments');
            exit;
        }

        if (empty($payment_ids) || !in_array($new_status, array_keys(PAYMENT_STATUSES))) {
            $_SESSION['error'] = 'Données invalides';
            header('Location: /payments');
            exit;
        }

        $payment_model = new Payment();
        if ($payment_model->bulkUpdateStatus($payment_ids, $new_status)) {
            $_SESSION['success'] = 'Paiements mis à jour avec succès';
            LoggingMiddleware::logAction($_SESSION['user_id'], 'update', 'payment', count($payment_ids));
        } else {
            $_SESSION['error'] = 'Erreur lors de la mise à jour';
        }

        header('Location: /payments');
        exit;
    }

    public static function markPaid($id) {
        AuthMiddleware::checkPermission('payments.manage');

        $payment_model = new Payment();
        if ($payment_model->markAsPaid($id)) {
            $_SESSION['success'] = 'Paiement marqué comme payé';
            LoggingMiddleware::logAction($_SESSION['user_id'], 'payment', 'payment', $id);
        }

        header('Location: /payments');
        exit;
    }
}
