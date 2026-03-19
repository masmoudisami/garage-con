<?php
class RepairType {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM repair_types ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM repair_types WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($name, $price) {
        $stmt = $this->db->prepare("INSERT INTO repair_types (name, default_price) VALUES (?, ?)");
        return $stmt->execute([$name, $price]);
    }

    public function update($id, $name, $price) {
        $stmt = $this->db->prepare("UPDATE repair_types SET name=?, default_price=? WHERE id=?");
        return $stmt->execute([$name, $price, $id]);
    }

    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM repair_types WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                // Contrainte de clé étrangère violée
                return ['success' => false, 'error' => 'foreign_key'];
            }
            throw $e;
        }
    }

    public function isInUse($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM invoice_lines WHERE repair_type_id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    public function getUsageCount($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM invoice_lines WHERE repair_type_id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result['count'];
    }

    public function getUsedInInvoices($id) {
        $stmt = $this->db->prepare("
            SELECT DISTINCT i.id, i.invoice_date, c.name as client_name 
            FROM invoice_lines il 
            JOIN invoices i ON il.invoice_id = i.id 
            JOIN clients c ON i.client_id = c.id 
            WHERE il.repair_type_id = ? 
            ORDER BY i.invoice_date DESC
            LIMIT 5
        ");
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }
}