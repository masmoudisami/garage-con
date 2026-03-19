<?php
class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? AND active = 1");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? AND active = 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($username, $password, $email, $full_name, $role = 'user') {
        // Vérifier si l'utilisateur existe déjà
        $existing = $this->getByUsername($username);
        if ($existing) {
            return ['success' => false, 'error' => 'username_exists'];
        }

        // Hacher le mot de passe
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("
            INSERT INTO users (username, password, email, full_name, role, active) 
            VALUES (?, ?, ?, ?, ?, 1)
        ");
        
        if ($stmt->execute([$username, $passwordHash, $email, $full_name, $role])) {
            return ['success' => true, 'user_id' => $this->db->lastInsertId()];
        }
        
        return ['success' => false, 'error' => 'database_error'];
    }

    public function updatePassword($id, $newPassword) {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$passwordHash, $id]);
    }

    public function updateLastLogin($id) {
        $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function verifyPassword($password, $hash) {
        // C'est LA fonction à utiliser pour vérifier un mot de passe
        // password_hash() génère un hash différent à chaque fois (à cause du sel)
        // Mais password_verify() sait comparer correctement
        return password_verify($password, $hash);
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT id, username, email, full_name, role, active, created_at, last_login FROM users ORDER BY username ASC");
        return $stmt->fetchAll();
    }

    public function toggleActive($id) {
        $stmt = $this->db->prepare("UPDATE users SET active = NOT active WHERE id = ?");
        return $stmt->execute([$id]);
    }
}