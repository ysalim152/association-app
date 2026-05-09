<?php
// Middleware de logging
class LoggingMiddleware {
    public static function logAction($user_id, $action, $entity_type, $entity_id, $data = []) {
        global $pdo;
        $audit = new AuditLog();
        $audit->log($user_id, $action, $entity_type, $entity_id, null, $data);
    }

    public static function logError($message, $context = []) {
        $log_file = LOGS_ROOT . '/error.log';
        $timestamp = date('Y-m-d H:i:s');
        $context_str = json_encode($context);
        $log_entry = "[$timestamp] $message | Context: $context_str\n";
        error_log($log_entry, 3, $log_file);
    }

    public static function logAccess($user_id, $route, $method) {
        $log_file = LOGS_ROOT . '/access.log';
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $log_entry = "[$timestamp] User:$user_id IP:$ip $method $route\n";
        error_log($log_entry, 3, $log_file);
    }
}
