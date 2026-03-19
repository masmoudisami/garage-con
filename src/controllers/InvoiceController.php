<?php
class InvoiceController {
    private $model;
    private $clientModel;
    private $repairModel;

    public function __construct() {
        $this->model = new Invoice();
        $this->clientModel = new Client();
        $this->repairModel = new RepairType();
    }

    public function index() {
        $filters = [
            'search' => $_GET['search'] ?? '',
            'date_start' => $_GET['date_start'] ?? '',
            'date_end' => $_GET['date_end'] ?? '',
            'type' => $_GET['type'] ?? ''
        ];
        $invoices = $this->model->getAll($filters);
        $clients = $this->clientModel->getAll();
        $types = $this->repairModel->getAll();
        include 'views/dashboard.php';
    }

    public function clientHistory() {
        $clientId = $_GET['client_id'] ?? null;
        $searchModel = $_GET['search_model'] ?? '';
        
        if (!$clientId) {
            if (!empty($searchModel)) {
                $clients = $this->clientModel->searchByModel($searchModel);
            } else {
                $clients = $this->clientModel->getAll();
            }
            include 'views/client_history_select.php';
            return;
        }
        
        $client = $this->clientModel->getById($clientId);
        if (!$client) {
            header('Location: index.php?route=invoices');
            exit;
        }
        
        $invoices = $this->model->getByClient($clientId);
        $stats = $this->model->getClientStats($clientId);
        $types = $this->repairModel->getAll();
        include 'views/client_history.php';
    }

    public function create() {
        $clientId = $_GET['client_id'] ?? null;
        $selectedClient = null;
        
        if ($clientId) {
            $selectedClient = $this->clientModel->getById($clientId);
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->prepareData($_POST);
            if ($this->model->create($data)) {
                header('Location: index.php?route=invoices');
                exit;
            }
        }
        $clients = $this->clientModel->getAll();
        $types = $this->repairModel->getAll();
        $invoice = null;
        $lines = [];
        $clientForInvoice = null;
        include 'views/invoice_form.php';
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

    public function edit($id) {
        $invoice = $this->model->getById($id);
        if (!$invoice) {
            header('Location: index.php?route=invoices');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->prepareData($_POST);
            if ($this->model->update($id, $data)) {
                header('Location: index.php?route=invoices');
                exit;
            }
        }
        $clients = $this->clientModel->getAll();
        $types = $this->repairModel->getAll();
        $lines = $this->model->getLines($id);
        $clientForInvoice = $this->clientModel->getById($invoice['client_id']);
        $selectedClient = null;
        include 'views/invoice_form.php';
    }

    public function delete($id) {
        $this->model->delete($id);
        header('Location: index.php?route=invoices');
        exit;
    }

    public function print($id) {
        $invoice = $this->model->getById($id);
        $lines = $this->model->getLines($id);
        $client = (new Client())->getById($invoice['client_id']);
        include 'views/invoice_print.php';
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
        
        $droit_timbre = floatval($post['droit_timbre']);
        $tax_rate = floatval($post['tax_rate']);
        
        $ht = $total_ht_lines;
        $tva = $ht * ($tax_rate / 100);
        $ttc = $ht + $tva + $droit_timbre;

        return [
            'client_id' => $post['client_id'],
            'invoice_date' => $post['invoice_date'],
            'mileage' => intval($post['mileage']),
            'comment' => $post['comment'],
            'droit_timbre' => $droit_timbre,
            'tax_rate' => $tax_rate,
            'total_ht' => $ht,
            'total_tva' => $tva,
            'total_ttc' => $ttc,
            'lines' => $lines
        ];
    }
}