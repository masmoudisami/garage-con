<?php
class RepairTypeController {
    private $model;

    public function __construct() {
        $this->model = new RepairType();
    }

    public function create() {
        $types = $this->model->getAll();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->create($_POST['name'], $_POST['default_price']);
            header('Location: index.php?route=types_create');
            exit;
        }
        $type = null;
        $error = null;
        include 'views/repair_type_form.php';
    }

    public function edit($id) {
        $type = $this->model->getById($id);
        $types = $this->model->getAll();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->update($id, $_POST['name'], $_POST['default_price']);
            header('Location: index.php?route=types_create');
            exit;
        }
        $error = null;
        include 'views/repair_type_form.php';
    }

    public function delete($id) {
        $result = $this->model->delete($id);
        
        if (is_array($result) && $result['success'] === false && $result['error'] === 'foreign_key') {
            // Type utilisé dans des factures - afficher message d'erreur
            $usageCount = $this->model->getUsageCount($id);
            $usedInInvoices = $this->model->getUsedInInvoices($id);
            $type = $this->model->getById($id);
            $types = $this->model->getAll();
            $error = [
                'type' => 'foreign_key',
                'message' => 'Impossible de supprimer ce type de réparation',
                'usageCount' => $usageCount,
                'usedInInvoices' => $usedInInvoices,
                'typeName' => $type['name']
            ];
            include 'views/repair_type_form.php';
            return;
        }
        
        header('Location: index.php?route=types_create');
        exit;
    }
}