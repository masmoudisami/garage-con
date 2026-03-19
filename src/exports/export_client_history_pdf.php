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

$stats = $invoiceModel->getClientStats($clientId);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique Réparations - <?php echo htmlspecialchars($client['name']); ?></title>
    <style>
        @page {
            margin: 15mm 15mm;
            size: A4;
            
            @top-right {
                content: "Page " counter(page) " / " counter(pages);
                font-size: 10px;
                color: #777;
            }
            
            @bottom-left {
                content: "";
            }
            
            @bottom-center {
                content: "";
            }
            
            @bottom-right {
                content: "";
            }
        }
        
        @page :first {
            @top-right {
                content: "";
            }
        }
        
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        body { 
            font-family: 'Times New Roman', serif; 
            padding: 0; 
            margin: 0; 
            color: #000;
            font-size: 11px;
            line-height: 1.4;
        }
        
        .container { 
            max-width: 800px; 
            margin: 0 auto; 
            padding: 20px;
        }
        
        .header { 
            text-align: center;
            margin-bottom: 30px; 
            border-bottom: 3px solid #2c3e50; 
            padding-bottom: 20px; 
        }
        
        .garage-name { 
            font-size: 18px; 
            font-weight: bold; 
            color: #2c3e50; 
            margin: 5px 0; 
        }
        
        .garage-details { 
            font-size: 10px; 
            color: #555; 
        }
        
        .document-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            margin: 20px 0;
            text-transform: uppercase;
        }
        
        .client-section { 
            margin-bottom: 25px; 
            background: #f9f9f9; 
            padding: 15px; 
            border-radius: 5px; 
        }
        
        .client-title { 
            font-weight: bold; 
            margin-bottom: 10px; 
            color: #2c3e50; 
            font-size: 12px;
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px; 
        }
        
        th, td { 
            border: 1px solid #2c3e50; 
            padding: 8px; 
            text-align: left; 
            font-size: 10px;
        }
        
        th { 
            background: #2c3e50; 
            color: #fff; 
            font-size: 10px;
        }
        
        .date-col { width: 12%; }
        .km-col { width: 10%; }
        .repair-col { width: 58%; }
        .comment-col { width: 20%; }
        
        .footer { 
            margin-top: 40px; 
            text-align: center; 
            font-size: 9px; 
            color: #777; 
            border-top: 1px solid #ddd; 
            padding-top: 15px; 
        }
        
        .no-print { 
            display: none;
        }
        
        .summary-box {
            background: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            text-align: center;
        }
        
        .summary-item {
            background: #3498db;
            color: #fff;
            padding: 10px;
            border-radius: 4px;
        }
        
        .summary-item h3 {
            margin: 0;
            font-size: 18px;
        }
        
        .summary-item p {
            margin: 5px 0 0;
            font-size: 9px;
            opacity: 0.9;
        }
        
        @media print { 
            .no-print { 
                display: none !important; 
            }
            
            body {
                padding: 0;
                margin: 0;
            }
            
            .container {
                border: none;
                box-shadow: none;
                margin: 0;
                padding: 0;
            }
            
            @page {
                margin: 15mm 15mm;
            }
        }
        
        @media screen {
            .container {
                border: 1px solid #ddd;
                padding: 30px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }
            
            .no-print {
                display: block;
                margin-top: 20px;
                text-align: center;
                padding: 20px;
                background: #ecf0f1;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="garage-name"><?php echo GARAGE_NAME; ?></div>
            <div class="garage-details">
                <?php echo GARAGE_ADDRESS; ?> - Tél: <?php echo GARAGE_PHONE; ?><br>
                <?php if(defined('GARAGE_EMAIL')): ?>Email: <?php echo GARAGE_EMAIL; ?><?php endif; ?>
            </div>
        </div>
        
        <div class="document-title">
            📋 HISTORIQUE DES RÉPARATIONS
        </div>

        <div class="client-section">
            <div class="client-title">INFORMATIONS CLIENT</div>
            <strong><?php echo htmlspecialchars($client['name']); ?></strong><br>
            <?php if(!empty($client['car_model'])): ?>Modèle: <?php echo htmlspecialchars($client['car_model']); ?><br><?php endif; ?>
            <?php if(!empty($client['phone'])): ?>Téléphone: <?php echo htmlspecialchars($client['phone']); ?><br><?php endif; ?>
            <?php if(!empty($client['address'])): ?>Adresse: <?php echo htmlspecialchars($client['address']); ?><?php endif; ?>
        </div>
        
        <div class="summary-box">
            <div class="summary-grid">
                <div class="summary-item">
                    <h3><?php echo $stats['total_invoices'] ?? 0; ?></h3>
                    <p>Interventions</p>
                </div>
                <div class="summary-item">
                    <h3><?php echo $stats['first_visit'] ? date('d/m/Y', strtotime($stats['first_visit'])) : '-'; ?></h3>
                    <p>Première Visite</p>
                </div>
                <div class="summary-item">
                    <h3><?php echo $stats['last_visit'] ? date('d/m/Y', strtotime($stats['last_visit'])) : '-'; ?></h3>
                    <p>Dernière Visite</p>
                </div>
                <div class="summary-item">
                    <h3><?php 
                        $totalKm = 0;
                        foreach($invoices as $inv) {
                            if($inv['mileage'] > $totalKm) $totalKm = $inv['mileage'];
                        }
                        echo number_format($totalKm, 0, ',', ' ');
                    ?></h3>
                    <p>KM Actuel</p>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="date-col">Date</th>
                    <th class="km-col">Kilométrage</th>
                    <th class="repair-col">Réparations Effectuées</th>
                    <th class="comment-col">Commentaire</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if(empty($invoices)): 
                ?>
                <tr>
                    <td colspan="4" style="text-align: center; padding: 30px;">
                        Aucune réparation enregistrée pour ce client
                    </td>
                </tr>
                <?php else: 
                    foreach ($invoices as $inv): 
                        $lines = $invoiceModel->getLines($inv['id']);
                        $repairList = [];
                        foreach($lines as $line) {
                            $repairList[] = $line['type_name'] . ' (x' . $line['quantity'] . ')';
                        }
                ?>
                <tr>
                    <td><?php echo date('d/m/Y', strtotime($inv['invoice_date'])); ?></td>
                    <td style="text-align: center;"><?php echo number_format($inv['mileage'], 0, ',', ' '); ?></td>
                    <td>
                        <?php if(!empty($repairList)): ?>
                            <ul style="margin: 5px 0; padding-left: 15px;">
                                <?php foreach($repairList as $repair): ?>
                                    <li><?php echo htmlspecialchars($repair); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td><?php echo !empty($inv['comment']) ? htmlspecialchars($inv['comment']) : '-'; ?></td>
                </tr>
                <?php 
                    endforeach; 
                endif; 
                ?>
            </tbody>
        </table>

        <div class="footer">
            <?php echo GARAGE_NAME; ?> - Document généré le <?php echo date('d/m/Y à H:i'); ?><br>
            Ce document atteste des interventions effectuées sur le véhicule - Les montants ne sont pas indiqués
        </div>
    </div>

    <div class="no-print">
        <button onclick="window.print()" class="btn" style="padding: 10px 20px; background: #3498db; color: #fff; border: none; cursor: pointer; border-radius: 4px;">🖨️ Imprimer / PDF</button>
        <a href="../index.php?route=client_history&client_id=<?php echo $client['id']; ?>" class="btn" style="padding: 10px 20px; background: #e74c3c; color: #fff; text-decoration: none; border-radius: 4px; margin-left: 10px;">↩️ Retour à l'Historique</a>
        
        <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 5px; text-align: left; max-width: 600px; margin-left: auto; margin-right: auto;">
            <strong>⚠️ Important - Pour supprimer l'URL en bas du PDF :</strong>
            <ol style="margin: 10px 0; padding-left: 20px;">
                <li>Cliquez sur "🖨️ Imprimer / PDF"</li>
                <li>Dans la fenêtre d'impression, cliquez sur <strong>"Plus de paramètres"</strong></li>
                <li><strong>DÉCOCHEZ</strong> l'option <strong>"En-têtes et pieds de page"</strong></li>
                <li>Cliquez sur "Enregistrer" ou "Imprimer"</li>
            </ol>
        </div>
    </div>
    
    <script>
        if (window.location.search.includes('print=1')) {
            window.onload = function() {
                window.print();
            };
        }
    </script>
</body>
</html>