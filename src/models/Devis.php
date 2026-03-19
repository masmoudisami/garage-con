<?php
class Devis {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($filters = []) {
        $where = [];
        $params = [];
        $sql = "SELECT d.*, c.name as client_name, c.car_model FROM devis d JOIN clients c ON d.client_id = c.id";

        if (!empty($filters['search'])) {
            $where[] = "(c.name LIKE ? OR c.car_model LIKE ?)";
            $params[] = "%" . $filters['search'] . "%";
            $params[] = "%" . $filters['search'] . "%";
        }
        if (!empty($filters['status'])) {
            $where[] = "d.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['date_start'])) {
            $where[] = "d.devis_date >= ?";
            $params[] = $filters['date_start'];
        }
        if (!empty($filters['date_end'])) {
            $where[] = "d.devis_date <= ?";
            $params[] = $filters['date_end'];
        }

        if (count($where) > 0) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY d.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM devis WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getLines($devisId) {
        $stmt = $this->db->prepare("SELECT dl.*, rt.name as type_name FROM devis_lines dl JOIN repair_types rt ON dl.repair_type_id = rt.id WHERE dl.devis_id = ?");
        $stmt->execute([$devisId]);
        return $stmt->fetchAll();
    }

    public function create($data) {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("
                INSERT INTO devis (client_id, devis_date, validity_date, mileage, comment, droit_timbre, tax_rate, total_ht, total_tva, total_ttc, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['client_id'], $data['devis_date'], $data['validity_date'], $data['mileage'], $data['comment'],
                $data['droit_timbre'], $data['tax_rate'],
                $data['total_ht'], $data['total_tva'], $data['total_ttc'], $data['status']
            ]);
            $devisId = $this->db->lastInsertId();

            $stmtLine = $this->db->prepare("INSERT INTO devis_lines (devis_id, repair_type_id, quantity, price_unit, total_line) VALUES (?, ?, ?, ?, ?)");
            foreach ($data['lines'] as $line) {
                $stmtLine->execute([$devisId, $line['repair_type_id'], $line['quantity'], $line['price_unit'], $line['total_line']]);
            }
            $this->db->commit();
            return $devisId;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function update($id, $data) {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("
                UPDATE devis SET client_id=?, devis_date=?, validity_date=?, mileage=?, comment=?, droit_timbre=?, tax_rate=?, total_ht=?, total_tva=?, total_ttc=?, status=? WHERE id=?
            ");
            $stmt->execute([
                $data['client_id'], $data['devis_date'], $data['validity_date'], $data['mileage'], $data['comment'],
                $data['droit_timbre'], $data['tax_rate'],
                $data['total_ht'], $data['total_tva'], $data['total_ttc'], $data['status'], $id
            ]);

            $stmtDel = $this->db->prepare("DELETE FROM devis_lines WHERE devis_id = ?");
            $stmtDel->execute([$id]);

            $stmtLine = $this->db->prepare("INSERT INTO devis_lines (devis_id, repair_type_id, quantity, price_unit, total_line) VALUES (?, ?, ?, ?, ?)");
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

    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE devis SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function convertToInvoice($devisId) {
        $devis = $this->getById($devisId);
        if (!$devis) return false;
        
        $lines = $this->getLines($devisId);
        
        // Créer la facture
        $invoiceData = [
            'client_id' => $devis['client_id'],
            'invoice_date' => date('Y-m-d'),
            'mileage' => $devis['mileage'],
            'comment' => $devis['comment'] . "\n(Convertis depuis devis N°" . $devis['id'] . ")",
            'droit_timbre' => $devis['droit_timbre'],
            'tax_rate' => $devis['tax_rate'],
            'total_ht' => $devis['total_ht'],
            'total_tva' => $devis['total_tva'],
            'total_ttc' => $devis['total_ttc'],
            'lines' => []
        ];
        
        foreach ($lines as $line) {
            $invoiceData['lines'][] = [
                'repair_type_id' => $line['repair_type_id'],
                'quantity' => $line['quantity'],
                'price_unit' => $line['price_unit'],
                'total_line' => $line['total_line']
            ];
        }
        
        $invoiceModel = new Invoice();
        $invoiceId = $invoiceModel->create($invoiceData);
        
        if ($invoiceId) {
            $this->updateStatus($devisId, 'accepted');
            $stmt = $this->db->prepare("UPDATE devis SET converted_to_invoice_id = ? WHERE id = ?");
            $stmt->execute([$invoiceId, $devisId]);
            return $invoiceId;
        }
        
        return false;
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM devis WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getStats() {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired
            FROM devis
        ");
        return $stmt->fetch();
    }
}