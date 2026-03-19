<?php
require_once '../config.php';
require_once '../models/Database.php';
require_once '../models/Invoice.php';
require_once '../models/Client.php';

$clientId = $_GET['client_id'] ?? null;

if (!$clientId) {
    die('Client ID requis');
}

$invoiceModel = new Invoice();
$clientModel = new Client();

$client = $clientModel->getById($clientId);
$invoices = $invoiceModel->getByClient($clientId);

if (!$client) {
    die('Client non trouvé');
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=historique_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $client['name']) . '_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// En-tête du fichier
fputcsv($output, ['CLIENT', $client['name']]);
fputcsv($output, ['Modèle Voiture', $client['car_model'] ?? 'N/A']);
fputcsv($output, ['Téléphone', $client['phone'] ?? 'N/A']);
fputcsv($output, ['Adresse', $client['address'] ?? 'N/A']);
fputcsv($output, []);

// En-têtes des factures
fputcsv($output, [
    'N° Facture',
    'Date',
    'Kilométrage',
    'Type Réparation',
    'Quantité',
    'Prix Unit.',
    'Total Ligne',
    'Commentaire',
    'Total HT',
    'Total TVA',
    'Droit Timbre',
    'Total TTC'
]);

// Données des factures
foreach ($invoices as $inv) {
    $lines = $invoiceModel->getLines($inv['id']);
    
    if (empty($lines)) {
        // Facture sans lignes
        fputcsv($output, [
            str_pad($inv['id'], 6, '0', STR_PAD_LEFT),
            $inv['invoice_date'],
            $inv['mileage'],
            'Aucune réparation',
            '',
            '',
            '',
            $inv['comment'] ?? '',
            number_format($inv['total_ht'], 3, ',', ' '),
            number_format($inv['total_tva'], 3, ',', ' '),
            number_format($inv['droit_timbre'], 3, ',', ' '),
            number_format($inv['total_ttc'], 3, ',', ' ')
        ]);
    } else {
        $firstLine = true;
        foreach ($lines as $line) {
            fputcsv($output, [
                str_pad($inv['id'], 6, '0', STR_PAD_LEFT),
                $inv['invoice_date'],
                $inv['mileage'],
                $line['type_name'],
                $line['quantity'],
                number_format($line['price_unit'], 3, ',', ' '),
                number_format($line['total_line'], 3, ',', ' '),
                $firstLine ? ($inv['comment'] ?? '') : '',
                $firstLine ? number_format($inv['total_ht'], 3, ',', ' ') : '',
                $firstLine ? number_format($inv['total_tva'], 3, ',', ' ') : '',
                $firstLine ? number_format($inv['droit_timbre'], 3, ',', ' ') : '',
                $firstLine ? number_format($inv['total_ttc'], 3, ',', ' ') : ''
            ]);
            $firstLine = false;
        }
    }
}

// Totaux
$stats = $invoiceModel->getClientStats($clientId);
fputcsv($output, []);
fputcsv($output, ['RÉCAPITULATIF', '', '', '', '', '', '', '', '', '', '', '']);
fputcsv($output, ['Nombre de factures', $stats['total_invoices'] ?? 0, '', '', '', '', '', '', '', '', '', '']);
fputcsv($output, ['Total Dépensé', number_format($stats['total_spent'] ?? 0, 3, ',', ' '), '', '', '', '', '', '', '', '', '', '']);
fputcsv($output, ['Première visite', $stats['first_visit'] ?? 'N/A', '', '', '', '', '', '', '', '', '', '']);
fputcsv($output, ['Dernière visite', $stats['last_visit'] ?? 'N/A', '', '', '', '', '', '', '', '', '', '']);

fclose($output);