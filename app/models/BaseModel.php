<?php
// Classe abstraite pour les modèles
if (!defined('APP_ROOT')) define('APP_ROOT', dirname(dirname(dirname(__FILE__))));

abstract class BaseModel {
    protected $table;
    protected $pdo;
    protected $id;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function all($limit = null, $offset = 0) {
        $sql = "SELECT * FROM {$this->table}";
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$limit, $offset]);
        } else {
            $stmt = $this->pdo->query($sql);
        }
        return $stmt->fetchAll();
    }

    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $columns = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array_values($data));
    }

    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        unset($data['id']);
        $setClause = implode(',', array_map(fn($k) => "$k = ?", array_keys($data)));
        $sql = "UPDATE {$this->table} SET $setClause WHERE id = ?";
        $values = array_values($data);
        $values[] = $id;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }

    public function delete($id) {
        $sql = "UPDATE {$this->table} SET is_deleted = 1 WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function count() {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE is_deleted = 0";
        $result = $this->pdo->query($sql)->fetch();
        return $result['count'] ?? 0;
    }

    public function search($query, $fields = []) {
        if (empty($fields)) return [];
        $conditions = implode(' OR ', array_map(fn($f) => "$f LIKE ?", $fields));
        $sql = "SELECT * FROM {$this->table} WHERE ($conditions) AND is_deleted = 0";
        $stmt = $this->pdo->prepare($sql);
        $search_param = "%$query%";
        $stmt->execute(array_fill(0, count($fields), $search_param));
        return $stmt->fetchAll();
    }
}
