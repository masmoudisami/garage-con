<?php
class ClientController {
    private $model;

    public function __construct() {
        $this->model = new Client();
    }

    public function create() {
        $clients = $this->model->getAll();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->create($_POST['name'], $_POST['car_model'], $_POST['phone'], $_POST['address']);
            header('Location: index.php?route=clients_create');
            exit;
        }
        $client = null;
        $error = null;
        include 'views/client_form.php';
    }

    public function edit($id) {
        $client = $this->model->getById($id);
        $clients = $this->model->getAll();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->update($id, $_POST['name'], $_POST['car_model'], $_POST['phone'], $_POST['address']);
            header('Location: index.php?route=clients_create');
            exit;
        }
        $error = null;
        include 'views/client_form.php';
    }

    public function delete($id) {
        $result = $this->model->delete($id);
        
        if (is_array($result) && $result['success'] === false && $result['error'] === 'foreign_key') {
            // Client a des factures - afficher message d'erreur
            $invoiceCount = $this->model->getInvoiceCount($id);
            $invoices = $this->model->getInvoices($id);
            $totalSpent = $this->model->getTotalSpent($id);
            $client = $this->model->getById($id);
            $clients = $this->model->getAll();
            $error = [
                'type' => 'foreign_key',
                'message' => 'Impossible de supprimer ce client',
                'invoiceCount' => $invoiceCount,
                'invoices' => $invoices,
                'totalSpent' => $totalSpent,
                'clientName' => $client['name']
            ];
            include 'views/client_form.php';
            return;
        }
        
        header('Location: index.php?route=clients_create');
        exit;
    }
}