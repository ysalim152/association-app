<?php
if (!defined('APP_ROOT')) define('APP_ROOT', dirname(dirname(dirname(__FILE__))));

require_once APP_ROOT . '/app/models/BaseModel.php';
require_once APP_ROOT . '/app/config/constants.php';

class User extends BaseModel {
    protected $table = 'users';

    public function findByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = ? AND is_deleted = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function findActiveUsers() {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 AND is_deleted = 0 ORDER BY email";
        $result = $this->pdo->query($sql);
        return $result->fetchAll();
    }

    public function verifyPassword($email, $password) {
        $user = $this->findByEmail($email);
        if (!$user) return false;
        return password_verify($password, $user['password_hash']);
    }

    public function createUser($email, $password, $role = ROLE_MEMBER) {
        return $this->create([
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
            'is_active' => 1,
        ]);
    }

    public function getUsersByRole($role) {
        $sql = "SELECT * FROM {$this->table} WHERE role = ? AND is_deleted = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    }
}
