<?php
class Client {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM clients ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public function searchByModel($search = '') {
        $stmt = $this->db->prepare("
            SELECT * FROM clients 
            WHERE car_model LIKE ? 
            OR name LIKE ? 
            OR phone LIKE ?
            OR matricule_fiscal LIKE ?
            ORDER BY car_model ASC, name ASC
        ");
        $searchTerm = "%" . $search . "%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM clients WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($name, $car_model, $matricule_fiscal, $phone, $address) {
        $stmt = $this->db->prepare("
            INSERT INTO clients (name, car_model, matricule_fiscal, phone, address) 
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$name, $car_model, $matricule_fiscal, $phone, $address]);
    }

    public function update($id, $name, $car_model, $matricule_fiscal, $phone, $address) {
        $stmt = $this->db->prepare("
            UPDATE clients 
            SET name=?, car_model=?, matricule_fiscal=?, phone=?, address=? 
            WHERE id=?
        ");
        return $stmt->execute([$name, $car_model, $matricule_fiscal, $phone, $address, $id]);
    }

    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM clients WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                return ['success' => false, 'error' => 'foreign_key'];
            }
            throw $e;
        }
    }

    public function getInvoiceCount($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM invoices WHERE client_id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result['count'];
    }

    public function getInvoices($id) {
        $stmt = $this->db->prepare("
            SELECT id, invoice_date, total_ttc, mileage 
            FROM invoices 
            WHERE client_id = ? 
            ORDER BY invoice_date DESC 
            LIMIT 5
        ");
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    public function getTotalSpent($id) {
        $stmt = $this->db->prepare("SELECT SUM(total_ttc) as total FROM invoices WHERE client_id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
}