<?php
require_once '../config.php';
require_once '../models/Database.php';
require_once '../models/Invoice.php';
require_once '../models/Client.php';

$invoiceModel = new Invoice();
$clientModel = new Client();

$clients = $clientModel->getAll();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=tous_clients_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// En-têtes
fputcsv($output, [
    'Client',
    'Modèle Voiture',
    'Matricule Fiscal',
    'Téléphone',
    'N° Facture',
    'Date',
    'Kilométrage',
    'Type Réparation',
    'Quantité',
    'Prix Unit.',
    'Total Ligne',
    'Total HT',
    'Total TVA',
    'Droit Timbre',
    'Total TTC'
]);

// Données
foreach ($clients as $client) {
    $invoices = $invoiceModel->getByClient($client['id']);
    
    foreach ($invoices as $inv) {
        $lines = $invoiceModel->getLines($inv['id']);
        
        if (empty($lines)) {
            fputcsv($output, [
                $client['name'],
                $client['car_model'] ?? 'N/A',
                $client['matricule_fiscal'] ?? 'N/A',
                $client['phone'] ?? 'N/A',
                str_pad($inv['id'], 6, '0', STR_PAD_LEFT),
                $inv['invoice_date'],
                $inv['mileage'],
                'Aucune réparation',
                '',
                '',
                '',
                number_format($inv['total_ht'], 3, ',', ' '),
                number_format($inv['total_tva'], 3, ',', ' '),
                number_format($inv['droit_timbre'], 3, ',', ' '),
                number_format($inv['total_ttc'], 3, ',', ' ')
            ]);
        } else {
            foreach ($lines as $line) {
                fputcsv($output, [
                    $client['name'],
                    $client['car_model'] ?? 'N/A',
                    $client['matricule_fiscal'] ?? 'N/A',
                    $client['phone'] ?? 'N/A',
                    str_pad($inv['id'], 6, '0', STR_PAD_LEFT),
                    $inv['invoice_date'],
                    $inv['mileage'],
                    $line['type_name'],
                    $line['quantity'],
                    number_format($line['price_unit'], 3, ',', ' '),
                    number_format($line['total_line'], 3, ',', ' '),
                    number_format($inv['total_ht'], 3, ',', ' '),
                    number_format($inv['total_tva'], 3, ',', ' '),
                    number_format($inv['droit_timbre'], 3, ',', ' '),
                    number_format($inv['total_ttc'], 3, ',', ' ')
                ]);
            }
        }
    }
}

fclose($output);