<?php
class DevisController {
    private $model;
    private $clientModel;
    private $repairModel;

    public function __construct() {
        $this->model = new Devis();
        $this->clientModel = new Client();
        $this->repairModel = new RepairType();
    }

    public function index() {
        $filters = [
            'search' => $_GET['search'] ?? '',
            'status' => $_GET['status'] ?? '',
            'date_start' => $_GET['date_start'] ?? '',
            'date_end' => $_GET['date_end'] ?? ''
        ];
        $devis = $this->model->getAll($filters);
        $stats = $this->model->getStats();
        include 'views/devis_list.php';
    }

    public function create() {
        $clientId = $_GET['client_id'] ?? null;
        $selectedClient = null;
        
        if ($clientId) {
            $selectedClient = $this->clientModel->getById($clientId);
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->prepareData($_POST);
            
            if (empty($data['client_id'])) {
                $error = "Veuillez sélectionner un client";
            } else {
                $devisId = $this->model->create($data);
                if ($devisId) {
                    header('Location: index.php?route=devis');
                    exit;
                } else {
                    $error = "Erreur lors de la création du devis";
                }
            }
        }
        
        $clients = $this->clientModel->getAll();
        $types = $this->repairModel->getAll();
        $devis = null;
        $lines = [];
        include 'views/devis_form.php';
    }

    public function edit($id) {
        $devis = $this->model->getById($id);
        if (!$devis) {
            header('Location: index.php?route=devis');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->prepareData($_POST);
            if ($this->model->update($id, $data)) {
                header('Location: index.php?route=devis');
                exit;
            }
        }
        $clients = $this->clientModel->getAll();
        $types = $this->repairModel->getAll();
        $lines = $this->model->getLines($id);
        $selectedClient = null;
        include 'views/devis_form.php';
    }

    public function delete($id) {
        $this->model->delete($id);
        header('Location: index.php?route=devis');
        exit;
    }

    public function print($id) {
        $devis = $this->model->getById($id);
        $lines = $this->model->getLines($id);
        $client = $this->clientModel->getById($devis['client_id']);
        include 'views/devis_print.php';
    }

    public function convert($id) {
        $invoiceId = $this->model->convertToInvoice($id);
        if ($invoiceId) {
            header('Location: index.php?route=invoices_print&id=' . $invoiceId);
        } else {
            header('Location: index.php?route=devis');
        }
        exit;
    }

    public function updateStatus($id, $status) {
        $validStatuses = ['draft', 'sent', 'accepted', 'rejected', 'expired'];
        if (in_array($status, $validStatuses)) {
            $this->model->updateStatus($id, $status);
        }
        header('Location: index.php?route=devis');
        exit;
    }

    public function searchClients() {
        header('Content-Type: application/json');
        $search = $_GET['q'] ?? '';
        if (strlen($search) < 2) {
            echo json_encode([]);
            return;
        }
        $clients = $this->clientModel->searchByModel($search);
        echo json_encode($clients);
    }

    private function prepareData($post) {
        $lines = [];
        $total_ht_lines = 0;
        
        if (isset($post['repair_type_id'])) {
            foreach ($post['repair_type_id'] as $key => $typeId) {
                if (!empty($typeId)) {
                    $qty = floatval($post['quantity'][$key]);
                    $price = floatval($post['price_unit'][$key]);
                    $total = $qty * $price;
                    $lines[] = [
                        'repair_type_id' => $typeId,
                        'quantity' => $qty,
                        'price_unit' => $price,
                        'total_line' => $total
                    ];
                    $total_ht_lines += $total;
                }
            }
        }
        
        $droit_timbre = floatval($post['droit_timbre'] ?? 0);
        $tax_rate = floatval($post['tax_rate'] ?? 19);
        
        $ht = $total_ht_lines;
        $tva = $ht * ($tax_rate / 100);
        $ttc = $ht + $tva + $droit_timbre;

        return [
            'client_id' => $post['client_id'] ?? 0,
            'devis_date' => $post['devis_date'] ?? date('Y-m-d'),
            'validity_date' => $post['validity_date'] ?? null,
            'mileage' => intval($post['mileage'] ?? 0),
            'comment' => $post['comment'] ?? '',
            'droit_timbre' => $droit_timbre,
            'tax_rate' => $tax_rate,
            'total_ht' => $ht,
            'total_tva' => $tva,
            'total_ttc' => $ttc,
            'status' => $post['status'] ?? 'draft',
            'lines' => $lines
        ];
    }
}