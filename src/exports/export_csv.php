<?php
require_once '../config.php';
require_once '../models/Database.php';
require_once '../models/Invoice.php';

$invoiceModel = new Invoice();

// Récupération des filtres depuis l'URL
$filters = [
    'search' => $_GET['search'] ?? '',
    'date_start' => $_GET['date_start'] ?? '',
    'date_end' => $_GET['date_end'] ?? '',
    'type' => $_GET['type'] ?? ''
];

$invoices = $invoiceModel->getAll($filters);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=factures_' . date('Y-m-d_H-i-s') . '.csv');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// En-tête avec informations de filtre
fputcsv($output, ['EXPORT DES FACTURES', date('d/m/Y à H:i')]);
if (!empty($filters['search'])) {
    fputcsv($output, ['Filtre Recherche', $filters['search']]);
}
if (!empty($filters['date_start'])) {
    fputcsv($output, ['Date Début', $filters['date_start']]);
}
if (!empty($filters['date_end'])) {
    fputcsv($output, ['Date Fin', $filters['date_end']]);
}
if (!empty($filters['type'])) {
    $repairModel = new RepairType();
    $type = $repairModel->getById($filters['type']);
    if ($type) {
        fputcsv($output, ['Type Réparation', $type['name']]);
    }
}
fputcsv($output, []);

// En-têtes des colonnes
fputcsv($output, [
    'N° Facture',
    'Date',
    'Client',
    'Modèle Voiture',
    'Kilométrage',
    'Total HT',
    'Total TVA',
    'Droit Timbre',
    'Total TTC'
]);

// Données des factures
foreach ($invoices as $inv) {
    fputcsv($output, [
        str_pad($inv['id'], 6, '0', STR_PAD_LEFT),
        $inv['invoice_date'],
        $inv['client_name'],
        $inv['car_model'] ?? 'N/A',
        $inv['mileage'],
        number_format($inv['total_ht'], 3, ',', ' '),
        number_format($inv['total_tva'], 3, ',', ' '),
        number_format($inv['droit_timbre'], 3, ',', ' '),
        number_format($inv['total_ttc'], 3, ',', ' ')
    ]);
}

// Totaux
fputcsv($output, []);
$totalHT = array_sum(array_column($invoices, 'total_ht'));
$totalTVA = array_sum(array_column($invoices, 'total_tva'));
$totalTTC = array_sum(array_column($invoices, 'total_ttc'));

fputcsv($output, ['RÉCAPITULATIF', '', '', '', '', '', '', '', '']);
fputcsv($output, ['Nombre de factures', count($invoices), '', '', '', '', '', '', '']);
fputcsv($output, ['Total HT', number_format($totalHT, 3, ',', ' '), '', '', '', '', '', '']);
fputcsv($output, ['Total TVA', number_format($totalTVA, 3, ',', ' '), '', '', '', '', '', '']);
fputcsv($output, ['Total TTC', number_format($totalTTC, 3, ',', ' '), '', '', '', '', '', '']);

fclose($output);