<?php
require_once '../config.php';
require_once '../models/Database.php';
require_once '../models/Invoice.php';
require_once '../models/Client.php';

$invoiceModel = new Invoice();
$invoices = $invoiceModel->getAll();

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Récapitulatif</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <h1>Récapitulatif des Entretien</h1>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Client</th>
                <th>Modèle</th>
                <th>KM</th>
                <th>Total TTC</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($invoices as $inv): ?>
            <tr>
                <td><?php echo $inv['invoice_date']; ?></td>
                <td><?php echo htmlspecialchars($inv['client_name']); ?></td>
                <td><?php echo htmlspecialchars($inv['car_model'] ?? '-'); ?></td>
                <td><?php echo $inv['mileage']; ?></td>
                <td><?php echo number_format($inv['total_ttc'], 3, ',', ' '); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="no-print">
        <button onclick="window.print()">Imprimer PDF</button>
    </div>
</body>
</html>