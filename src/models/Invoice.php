<?php
class Invoice {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($filters = []) {
        $where = [];
        $params = [];
        $sql = "SELECT i.*, c.name as client_name, c.car_model FROM invoices i JOIN clients c ON i.client_id = c.id";

        if (!empty($filters['search'])) {
            $where[] = "(c.name LIKE ? OR c.car_model LIKE ?)";
            $params[] = "%" . $filters['search'] . "%";
            $params[] = "%" . $filters['search'] . "%";
        }
        if (!empty($filters['date_start'])) {
            $where[] = "i.invoice_date >= ?";
            $params[] = $filters['date_start'];
        }
        if (!empty($filters['date_end'])) {
            $where[] = "i.invoice_date <= ?";
            $params[] = $filters['date_end'];
        }
        if (!empty($filters['type'])) {
            $where[] = "EXISTS (SELECT 1 FROM invoice_lines il WHERE il.invoice_id = i.id AND il.repair_type_id = ?)";
            $params[] = $filters['type'];
        }

        if (count($where) > 0) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY i.invoice_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getByClient($clientId) {
        $stmt = $this->db->prepare("
            SELECT i.*, c.name as client_name, c.car_model, c.phone, c.address 
            FROM invoices i 
            JOIN clients c ON i.client_id = c.id 
            WHERE i.client_id = ? 
            ORDER BY i.invoice_date DESC
        ");
        $stmt->execute([$clientId]);
        return $stmt->fetchAll();
    }

    public function getClientStats($clientId) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_invoices,
                SUM(total_ttc) as total_spent,
                MIN(invoice_date) as first_visit,
                MAX(invoice_date) as last_visit
            FROM invoices 
            WHERE client_id = ?
        ");
        $stmt->execute([$clientId]);
        return $stmt->fetch();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM invoices WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getLines($invoiceId) {
        $stmt = $this->db->prepare("SELECT il.*, rt.name as type_name FROM invoice_lines il JOIN repair_types rt ON il.repair_type_id = rt.id WHERE il.invoice_id = ?");
        $stmt->execute([$invoiceId]);
        return $stmt->fetchAll();
    }

    public function getLinesByInvoiceIds($invoiceIds) {
        if (empty($invoiceIds)) return [];
        $placeholders = implode(',', array_fill(0, count($invoiceIds), '?'));
        $stmt = $this->db->prepare("SELECT il.*, rt.name as type_name FROM invoice_lines il JOIN repair_types rt ON il.repair_type_id = rt.id WHERE il.invoice_id IN ($placeholders)");
        $stmt->execute($invoiceIds);
        return $stmt->fetchAll();
    }

    public function create($data) {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("INSERT INTO invoices (client_id, invoice_date, mileage, comment, droit_timbre, tax_rate, total_ht, total_tva, total_ttc) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['client_id'], $data['invoice_date'], $data['mileage'], $data['comment'],
                $data['droit_timbre'], $data['tax_rate'],
                $data['total_ht'], $data['total_tva'], $data['total_ttc']
            ]);
            $invoiceId = $this->db->lastInsertId();

            $stmtLine = $this->db->prepare("INSERT INTO invoice_lines (invoice_id, repair_type_id, quantity, price_unit, total_line) VALUES (?, ?, ?, ?, ?)");
            foreach ($data['lines'] as $line) {
                $stmtLine->execute([$invoiceId, $line['repair_type_id'], $line['quantity'], $line['price_unit'], $line['total_line']]);
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function update($id, $data) {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("UPDATE invoices SET client_id=?, invoice_date=?, mileage=?, comment=?, droit_timbre=?, tax_rate=?, total_ht=?, total_tva=?, total_ttc=? WHERE id=?");
            $stmt->execute([
                $data['client_id'], $data['invoice_date'], $data['mileage'], $data['comment'],
                $data['droit_timbre'], $data['tax_rate'],
                $data['total_ht'], $data['total_tva'], $data['total_ttc'], $id
            ]);

            $stmtDel = $this->db->prepare("DELETE FROM invoice_lines WHERE invoice_id = ?");
            $stmtDel->execute([$id]);

            $stmtLine = $this->db->prepare("INSERT INTO invoice_lines (invoice_id, repair_type_id, quantity, price_unit, total_line) VALUES (?, ?, ?, ?, ?)");
            foreach ($data['lines'] as $line) {
                $stmtLine->execute([$id, $line['repair_type_id'], $line['quantity'], $line['price_unit'], $line['total_line']]);
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM invoices WHERE id = ?");
        return $stmt->execute([$id]);
    }
}